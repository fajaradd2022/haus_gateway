<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WahaWebhookController extends Controller
{
    /**
     * POST /webhook/waha/messages
     * Handles: message.any events from WAHA
     */
    public function handleMessage(Request $request): Response
    {
        $payload = $request->all();

        Log::channel('daily')->info('WAHA webhook message.any received', ['payload' => $payload]);

        if (($payload['event'] ?? '') !== 'message.any') {
            return response('', 200);
        }

        $msg = $payload['payload'] ?? null;
        if (! $msg) {
            return response('', 200);
        }

        // Skip outbound echo (agent's own sent messages)
        if ($msg['fromMe'] ?? false) {
            return response('', 200);
        }

        // Extract phone from "628xxx@c.us"
        $from  = $msg['from'] ?? '';
        $phone = preg_replace('/@c\.us$/', '', $from);

        if (blank($phone)) {
            return response('', 200);
        }

        $body       = $msg['body'] ?? '';
        $hasMedia   = (bool) ($msg['hasMedia'] ?? false);
        $mediaUrl   = $msg['media']['url'] ?? null;
        $wahaId     = $msg['id'] ?? null;
        $notifyName = $msg['_data']['notifyName'] ?? null;

        // Find or create Customer by phone
        $customer = Customer::firstOrCreate(
            ['phone_number' => $phone],
            ['name' => $notifyName ?: "WA {$phone}"]
        );

        // Guard: blocked customer — ignore silently
        if ($customer->is_blocked) {
            Log::info('WAHA: message from blocked customer ignored', ['phone' => $phone]);
            return response('', 200);
        }

        // Find latest active WhatsApp ticket for this customer
        $ticket = Ticket::query()
            ->where('customer_id', $customer->id)
            ->where('channel', 'whatsapp')
            ->whereIn('status', ['open', 'pending', 'on_progress'])
            ->whereNull('archived_at')
            ->latest('last_message_at')
            ->first();

        if (! $ticket) {
            // New ticket with no subject — agent will name it
            $ticket = Ticket::query()->create([
                'customer_id'     => $customer->id,
                'subject'         => null,
                'status'          => 'pending',
                'priority'        => 'medium',
                'channel'         => 'whatsapp',
                'last_message_at' => now(),
                'sla_deadline'    => now()->addHours(2),
            ]);
        }

        // Detect media type from URL extension
        $mediaType = null;
        if ($hasMedia && $mediaUrl) {
            $ext       = strtolower(pathinfo((string) parse_url($mediaUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            $mediaType = match (true) {
                in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) => 'image',
                in_array($ext, ['mp4', 'mov', 'avi'])                 => 'video',
                in_array($ext, ['mp3', 'ogg', 'aac', 'opus'])         => 'audio',
                in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx']) => 'document',
                default => 'file',
            };
        }

        Message::query()->create([
            'ticket_id'        => $ticket->id,
            'sender_type'      => 'customer',
            'content'          => blank($body) ? null : $body,
            'media_url'        => $hasMedia ? $mediaUrl : null,
            'media_type'       => $mediaType,
            'sent_at'          => now(),
            'is_internal_note' => false,
            'waha_message_id'  => $wahaId,
        ]);

        $ticket->forceFill(['last_message_at' => now()])->save();
        $customer->forceFill(['last_contact_at' => now()])->save();

        return response('', 200);
    }

    /**
     * POST /webhook/waha/events
     * Handles: message.ack + session.status events from WAHA
     */
    public function handleEvent(Request $request): Response
    {
        $payload = $request->all();
        $event   = $payload['event'] ?? 'unknown';

        Log::channel('daily')->info("WAHA webhook event [{$event}] received", ['payload' => $payload]);

        if ($event === 'message.ack') {
            $wahaId = $payload['payload']['id'] ?? null;
            $ack    = $payload['payload']['ack'] ?? null;
            Log::info('WAHA ack', ['waha_message_id' => $wahaId, 'ack' => $ack]);
        }

        if ($event === 'session.status') {
            $sessionName   = $payload['payload']['name'] ?? 'default';
            $sessionStatus = $payload['payload']['status'] ?? 'unknown';
            Log::warning("WAHA session [{$sessionName}] status changed to [{$sessionStatus}]");
        }

        return response('', 200);
    }
}

<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\QuickChatController;
use App\Http\Controllers\WahaWebhookController;
use Illuminate\Support\Facades\Route;

// WAHA Webhooks — no auth, no CSRF (WAHA posts to these)
Route::prefix('webhook/waha')->group(function (): void {
    Route::post('/messages', [WahaWebhookController::class, 'handleMessage']);
    Route::post('/events',   [WahaWebhookController::class, 'handleEvent']);
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', [HelpdeskController::class, 'index'])->name('workspace');
    Route::get('/admin', [HelpdeskController::class, 'admin'])->name('admin');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix('api')->group(function (): void {
        // User management (admin only)
        Route::post('/users',           [HelpdeskController::class, 'storeUser']);
        Route::patch('/users/{user}',   [HelpdeskController::class, 'updateUser']);
        Route::delete('/users/{user}',  [HelpdeskController::class, 'destroyUser']);

        // Knowledge Base (admin only)
        Route::post('/knowledge',                   [HelpdeskController::class, 'storeKnowledge']);
        Route::patch('/knowledge/{knowledgeBase}',  [HelpdeskController::class, 'updateKnowledge']);
        Route::delete('/knowledge/{knowledgeBase}', [HelpdeskController::class, 'destroyKnowledge']);

        // Quick Chat Templates
        Route::get('/quick-chats',                 [QuickChatController::class, 'index']);
        Route::get('/quick-chats/all',             [QuickChatController::class, 'indexAll']);
        Route::post('/quick-chats',                [QuickChatController::class, 'store']);
        Route::patch('/quick-chats/{quickChat}',   [QuickChatController::class, 'update']);
        Route::delete('/quick-chats/{quickChat}',  [QuickChatController::class, 'destroy']);
        // Workspace
        Route::get('/workspace', [HelpdeskController::class, 'workspace']);

        // Tickets
        Route::get('/tickets/archived',             [HelpdeskController::class, 'archivedTickets']);
        Route::get('/tickets/{ticket}',            [HelpdeskController::class, 'showTicket']);
        Route::post('/tickets/{ticket}/messages',  [HelpdeskController::class, 'storeMessage']);
        Route::patch('/tickets/{ticket}/status',   [HelpdeskController::class, 'updateStatus']);
        Route::patch('/tickets/{ticket}/archive',   [HelpdeskController::class, 'archiveTicket']);
        Route::patch('/tickets/{ticket}/unarchive', [HelpdeskController::class, 'unarchiveTicket']);
        Route::delete('/tickets/{ticket}',          [HelpdeskController::class, 'destroyTicket']);
        Route::post('/tickets/bulk',                [HelpdeskController::class, 'bulkTickets']);
        Route::patch('/tickets/{ticket}/subject',   [HelpdeskController::class, 'updateSubject']);
        Route::post('/tickets/{ticket}/move-messages', [HelpdeskController::class, 'moveMessages']);
        Route::post('/tickets/{ticket}/split',      [HelpdeskController::class, 'splitTicket']);

        // Contacts (customers as address book)
        Route::get('/contacts',                    [ContactController::class, 'index']);
        Route::post('/contacts',                   [ContactController::class, 'store']);
        Route::get('/contacts/{customer}',         [ContactController::class, 'show']);
        Route::patch('/contacts/{customer}',       [ContactController::class, 'update']);
        Route::delete('/contacts/{customer}',      [ContactController::class, 'destroy']);

        // New Chat (proactive ticket creation)
        Route::post('/chats/new',                  [ContactController::class, 'newChat']);
    });
});

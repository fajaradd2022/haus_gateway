<?php

use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest users are redirected to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('the login page loads successfully', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSee('Sign in');
});

test('an authenticated user can load the workspace', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::query()->where('role', 'agent')->firstOrFail();

    $this->actingAs($user);
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('Mini Helpdesk AI Assist')
        ->assertSee('HAUS');
});

test('a user can sign in with valid credentials', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->post('/login', [
        'email' => 'raka@minihelpdesk.test',
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticated();
});

test('an agent reply updates the ticket workspace payload', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::query()->where('role', 'agent')->firstOrFail();
    $ticket = Ticket::query()->firstOrFail();

    $this->actingAs($user);
    $response = $this->postJson("/api/tickets/{$ticket->id}/messages", [
        'content' => 'Siap, kami lanjutkan pengecekan sesuai SOP.',
        'is_internal_note' => false,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('workspace.activeTicket.id', $ticket->id)
        ->assertJsonPath('workspace.activeTicket.messages.3.content', 'Siap, kami lanjutkan pengecekan sesuai SOP.');
});

test('an admin can access the admin page', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::query()->where('role', 'admin')->firstOrFail();

    $this->actingAs($admin);
    $response = $this->get('/admin');

    $response
        ->assertOk()
        ->assertSee('User Management')
        ->assertSee('Context references');
});

test('an agent cannot access the admin page', function () {
    $this->seed(DatabaseSeeder::class);
    $agent = User::query()->where('role', 'agent')->firstOrFail();

    $this->actingAs($agent);
    $response = $this->get('/admin');

    $response->assertForbidden();
});

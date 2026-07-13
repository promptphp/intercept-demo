<?php

use App\Ai\Agents\DebugAgent;

test('the log debugger page renders', function () {
    $this->get(route('demos.debugger'))
        ->assertSuccessful()
        ->assertSee('Demo 3');
});

test('logs are analyzed with pii masked before reaching the provider', function () {
    DebugAgent::fake(['Root cause: duplicate email during import.']);

    $response = $this->postJson(route('demos.debugger.store'), [
        'log' => 'UNIQUE constraint failed: users.email for emily.carter@gmail.com, request ip 203.0.113.42',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('reply', 'Root cause: duplicate email during import.');

    expect($response->json('sentPrompt'))
        ->not->toContain('emily.carter@gmail.com')
        ->not->toContain('203.0.113.42');
});

test('logs containing a bearer token are blocked', function () {
    DebugAgent::fake();

    $this->postJson(route('demos.debugger.store'), [
        'log' => 'Request headers: {"Authorization":"Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJiaWxsaW5nIn0.k7pDMx9WvR4tYq2LsG8uZbNc"}',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('blocked', true);
});

test('logs containing an api key are blocked', function () {
    DebugAgent::fake();

    $this->postJson(route('demos.debugger.store'), [
        'log' => 'config dump: {"services.openai.key":"sk-Zq83jPlmN4vTbXcRw92KdYhF7GsAeU10"}',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('blocked', true);
});

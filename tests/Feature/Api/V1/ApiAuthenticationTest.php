<?php

use App\Models\Customer;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('rejects requests that carry no credentials', function () {
    $response = $this->getJson('/api/v1/customers');

    $response->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Basic realm="API"')
        ->assertJsonPath('message', 'Authentication credentials were not provided.');
});

it('rejects basic credentials that do not match a user', function () {
    $user = User::factory()->create();

    $this->withHeaders(basicAuth($user, 'wrong-password'))
        ->getJson('/api/v1/customers')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'The provided credentials are incorrect.');
});

it('allows requests authenticated with basic authentication', function () {
    $user = User::factory()->create();
    Customer::factory()->count(2)->create();

    $this->withHeaders(basicAuth($user))
        ->getJson('/api/v1/customers')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('allows requests authenticated with a personal access token', function () {
    $user = User::factory()->create();
    Customer::factory()->create();

    $this->withHeaders(bearerAuth($user))
        ->getJson('/api/v1/customers')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('rejects a malformed bearer token', function () {
    $this->withHeaders(['Authorization' => 'Bearer not-a-real-token'])
        ->getJson('/api/v1/customers')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'The provided token is invalid or has expired.');
});

it('rejects a token that has expired', function () {
    $user = User::factory()->create();
    $headers = bearerAuth($user);

    $this->travel(config()->integer('sanctum.expiration') + 1)->minutes();

    $this->withHeaders($headers)
        ->getJson('/api/v1/customers')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'The provided token is invalid or has expired.');
});

it('issues a token for valid login credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in'])
        ->assertJsonPath('token_type', 'bearer')
        ->assertJsonPath('expires_in', config()->integer('sanctum.expiration') * 60);

    expect($user->tokens()->count())->toBe(1);

    $this->withHeaders(['Authorization' => 'Bearer '.$response->json('access_token')])
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

it('rejects a login attempt with invalid credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'not-the-password',
    ])->assertUnauthorized()
        ->assertJsonPath('message', 'The provided credentials are incorrect.');

    expect(PersonalAccessToken::count())->toBe(0);
});

it('validates the login payload', function () {
    $this->postJson('/api/v1/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('rotates the token on refresh and revokes the previous one', function () {
    $user = User::factory()->create();
    $headers = bearerAuth($user);

    $response = $this->withHeaders($headers)
        ->postJson('/api/v1/auth/refresh')
        ->assertOk()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);

    expect($user->tokens()->count())->toBe(1);

    forgetResolvedGuards();

    $this->withHeaders($headers)
        ->getJson('/api/v1/customers')
        ->assertUnauthorized();

    forgetResolvedGuards();

    $this->withHeaders(['Authorization' => 'Bearer '.$response->json('access_token')])
        ->getJson('/api/v1/customers')
        ->assertOk();
});

it('does not refresh a request authenticated with basic credentials', function () {
    $user = User::factory()->create();

    $this->withHeaders(basicAuth($user))
        ->postJson('/api/v1/auth/refresh')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'The provided token cannot be refreshed.');
});

it('revokes the token on logout', function () {
    $user = User::factory()->create();
    $headers = bearerAuth($user);

    $this->withHeaders($headers)
        ->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Successfully logged out.');

    expect($user->tokens()->count())->toBe(0);

    forgetResolvedGuards();

    $this->withHeaders($headers)
        ->getJson('/api/v1/customers')
        ->assertUnauthorized();
});

it('does not log out a request authenticated with basic credentials', function () {
    $user = User::factory()->create();

    $this->withHeaders(basicAuth($user))
        ->postJson('/api/v1/auth/logout')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'The provided token cannot be invalidated.');
});

it('protects every customer and service endpoint', function (string $method, string $uri) {
    $this->json($method, $uri)->assertUnauthorized();
})->with([
    ['get', '/api/v1/customers'],
    ['post', '/api/v1/customers'],
    ['get', '/api/v1/customers/1'],
    ['put', '/api/v1/customers/1'],
    ['delete', '/api/v1/customers/1'],
    ['get', '/api/v1/customers/1/services'],
    ['post', '/api/v1/customers/1/services'],
    ['get', '/api/v1/services'],
    ['get', '/api/v1/services/1'],
    ['put', '/api/v1/services/1'],
    ['delete', '/api/v1/services/1'],
    ['get', '/api/v1/auth/me'],
]);

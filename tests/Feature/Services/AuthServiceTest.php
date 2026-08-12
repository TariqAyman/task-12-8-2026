<?php

use App\Models\User;
use App\Services\AuthService;
use App\Support\Auth\IssuedToken;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->auth = app(AuthService::class);
});

it('issues a token for valid credentials', function () {
    $user = User::factory()->create();

    $token = $this->auth->attempt(['email' => $user->email, 'password' => 'password']);

    expect($token)->toBeInstanceOf(IssuedToken::class)
        ->and($token->expiresIn)->toBe(config()->integer('sanctum.expiration') * 60)
        ->and($user->tokens()->count())->toBe(1)
        ->and(PersonalAccessToken::findToken($token->plainTextToken)?->tokenable_id)->toBe($user->id);
});

it('refuses credentials that do not match a user', function () {
    $user = User::factory()->create();

    expect($this->auth->attempt(['email' => $user->email, 'password' => 'wrong-password']))->toBeNull()
        ->and($this->auth->attempt(['email' => 'nobody@example.com', 'password' => 'password']))->toBeNull()
        ->and(PersonalAccessToken::count())->toBe(0);
});

it('names every issued token', function () {
    $user = User::factory()->create();

    $this->auth->issue($user);

    expect($user->tokens()->firstOrFail()->name)->toBe(AuthService::TOKEN_NAME);
});

it('reports a token with no expiration as never expiring', function () {
    config()->set('sanctum.expiration', null);

    expect($this->auth->issue(User::factory()->create())->expiresIn)->toBeNull();
});

it('replaces the current token when refreshing', function () {
    $user = User::factory()->create();
    $current = $this->auth->issue($user);

    $rotated = $this->auth->refresh($user, $current->plainTextToken);

    expect($rotated?->plainTextToken)->not->toBe($current->plainTextToken)
        ->and(PersonalAccessToken::findToken($current->plainTextToken))->toBeNull()
        ->and($user->tokens()->count())->toBe(1);
});

it('cannot refresh or revoke when the request carries no token', function () {
    $user = User::factory()->create();

    expect($this->auth->refresh($user, null))->toBeNull()
        ->and($this->auth->revoke($user, null))->toBeFalse()
        ->and($this->auth->revoke($user, 'not-a-real-token'))->toBeFalse();
});

it('refuses to revoke a token belonging to another user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $token = $this->auth->issue($owner);

    expect($this->auth->revoke($intruder, $token->plainTextToken))->toBeFalse()
        ->and($owner->tokens()->count())->toBe(1);
});

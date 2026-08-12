<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => Cache::flush())
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Build the HTTP Basic Authentication header for the given user.
 *
 * @return array<string, string>
 */
function basicAuth(User $user, string $password = 'password'): array
{
    return [
        'Authorization' => 'Basic '.base64_encode($user->email.':'.$password),
    ];
}

/**
 * Drop the guards the previous request resolved.
 *
 * Every test request runs against the same application instance, so a guard
 * that already resolved a user keeps returning it. A real request handles this
 * on its own by booting a fresh process.
 */
function forgetResolvedGuards(): void
{
    Auth::forgetGuards();
}

/**
 * Build the bearer token header for the given user.
 *
 * @return array<string, string>
 */
function bearerAuth(User $user): array
{
    return [
        'Authorization' => 'Bearer '.$user->createToken(AuthService::TOKEN_NAME)->plainTextToken,
    ];
}

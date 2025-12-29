<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Mockery;
use Roster\Domain\Helpers\TimezoneHelper;
use Roster\Http\Middleware\SetUserTimezone;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * Test suite for SetUserTimezone middleware.
 * Verifies timezone resolution from various sources with proper prioritization.
 */
final class SetUserTimezoneTest extends TestCase
{
    private SetUserTimezone $middleware;

    /**
     * Set up test environment before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new SetUserTimezone();

        // Reset timezone helper state
        TimezoneHelper::resetUserTimezone();

        // Configure default timezone settings
        Config::set('app.timezone', 'UTC');
        Config::set('roster.timezone', 'Europe/Paris');
        TimezoneHelper::initialize();
    }

    /**
     * Clean up test environment after each test.
     */
    protected function tearDown(): void
    {
        TimezoneHelper::resetUserTimezone();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that middleware sets timezone from X-Timezone header.
     */
    public function testSetsTimezoneFromHeader(): void
    {
        // Arrange: Create request with timezone header
        $request = Request::create(uri: '/', method: 'GET');
        $request->headers->set('X-Timezone', 'America/New_York');

        $next = function ($req) {
            // Assert: Verify timezone is set correctly during request handling
            $this->assertSame('America/New_York', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware sets timezone from session storage.
     */
    public function testSetsTimezoneFromSession(): void
    {
        // Arrange: Mock session with timezone value
        $session = Mockery::mock(Store::class);
        $session->shouldReceive('has')
            ->with('timezone')
            ->andReturn(true);
        $session->shouldReceive('get')
            ->with('timezone')
            ->andReturn('Asia/Tokyo');

        $request = Request::create(uri: '/', method: 'GET');
        $request->setLaravelSession($session);

        $next = function ($req) {
            // Assert: Verify session timezone is applied
            $this->assertSame('Asia/Tokyo', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware sets timezone from authenticated user preference.
     */
    public function testSetsTimezoneFromUserPreference(): void
    {
        // Arrange: Create user with timezone preference
        $user = new class {
            public function getTimezone()
            {
                return 'Australia/Sydney';
            }
        };

        $request = Request::create(uri: '/', method: 'GET');
        $request->setUserResolver(fn() => $user);

        $next = function ($req) {
            // Assert: Verify user timezone is applied
            $this->assertSame('Australia/Sydney', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware sets timezone from X-Client-Timezone header.
     */
    public function testSetsTimezoneFromClientHeader(): void
    {
        // Arrange: Create request with client timezone header
        $request = Request::create(uri: '/', method: 'GET');
        $request->headers->set('X-Client-Timezone', 'Pacific/Honolulu');

        $next = function ($req) {
            // Assert: Verify client timezone is applied
            $this->assertSame('Pacific/Honolulu', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware uses default timezone when no source is available.
     */
    public function testUsesDefaultWhenNoTimezoneSource(): void
    {
        // Arrange: Create request without any timezone sources
        $request = Request::create(uri: '/', method: 'GET');

        $next = function ($req) {
            // Assert: Verify default timezone is used
            $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware prioritizes X-Timezone header over session timezone.
     */
    public function testPrioritizesHeaderOverSession(): void
    {
        // Arrange: Create request with both header and session timezone
        $session = Mockery::mock(Store::class);
        $session->shouldReceive('has')
            ->with('timezone')
            ->andReturn(true);
        $session->shouldReceive('get')
            ->with('timezone')
            ->andReturn('Europe/London');

        $request = Request::create(uri: '/', method: 'GET');
        $request->setLaravelSession($session);
        $request->headers->set('X-Timezone', 'America/Chicago');

        $next = function ($req) {
            // Assert: Verify header takes priority over session
            $this->assertSame('America/Chicago', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware gracefully handles invalid timezone in header.
     */
    public function testHandlesInvalidTimezoneInHeader(): void
    {
        // Arrange: Create request with invalid timezone header
        $request = Request::create(uri: '/', method: 'GET');
        $request->headers->set('X-Timezone', 'Invalid/Timezone');

        $next = function ($req) {
            // Assert: Verify fallback to default timezone
            $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware normalizes timezone names.
     */
    public function testNormalizesTimezoneNames(): void
    {
        // Arrange: Create request with lowercase timezone name
        $request = Request::create(uri: '/', method: 'GET');
        $request->headers->set('X-Timezone', 'america/new_york');

        $next = function ($req) {
            // Assert: Verify timezone is properly normalized
            $this->assertSame('America/New_York', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware handles user without getTimezone method.
     */
    public function testWithUserWithoutGetTimezoneMethod(): void
    {
        // Arrange: Create user object without timezone method
        $user = new \stdClass();

        $request = Request::create(uri: '/', method: 'GET');
        $request->setUserResolver(fn() => $user);

        $next = function ($req) {
            // Assert: Verify default timezone is used
            $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware handles user returning null timezone.
     */
    public function testWithUserReturningNullTimezone(): void
    {
        // Arrange: Create user that returns null timezone
        $user = new class {
            public function getTimezone()
            {
                return null;
            }
        };

        $request = Request::create(uri: '/', method: 'GET');
        $request->setUserResolver(fn() => $user);

        $next = function ($req) {
            // Assert: Verify default timezone is used
            $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware handles invalid timezone from session.
     */
    public function testWithSessionReturningInvalidTimezone(): void
    {
        // Arrange: Mock session with invalid timezone
        $session = Mockery::mock(Store::class);
        $session->shouldReceive('has')
            ->with('timezone')
            ->andReturn(true);
        $session->shouldReceive('get')
            ->with('timezone')
            ->andReturn('Invalid/Timezone');

        $request = Request::create(uri: '/', method: 'GET');
        $request->setLaravelSession($session);

        $next = function ($req) {
            // Assert: Verify fallback to default timezone
            $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware works without session.
     */
    public function testWithoutSession(): void
    {
        // Arrange: Create request without session
        $request = Request::create(uri: '/', method: 'GET');

        $next = function ($req) {
            // Assert: Verify default timezone is used
            $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test middleware priority with all timezone sources present.
     */
    public function testWithAllSourcesPresent(): void
    {
        // Arrange: Create request with all timezone sources
        $session = Mockery::mock(Store::class);
        $session->shouldReceive('has')
            ->with('timezone')
            ->andReturn(true);
        $session->shouldReceive('get')
            ->with('timezone')
            ->andReturn('Europe/London');

        $user = new class {
            public function getTimezone()
            {
                return 'Asia/Dubai';
            }
        };

        $request = Request::create(uri: '/', method: 'GET');
        $request->setLaravelSession($session);
        $request->setUserResolver(fn() => $user);
        $request->headers->set('X-Timezone', 'America/Los_Angeles');
        $request->headers->set('X-Client-Timezone', 'Pacific/Auckland');

        $next = function ($req) {
            // Assert: Verify X-Timezone header has highest priority
            $this->assertSame('America/Los_Angeles', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware works with only X-Client-Timezone header.
     */
    public function testWithOnlyClientTimezoneHeader(): void
    {
        // Arrange: Create request with only client timezone header
        $request = Request::create(uri: '/', method: 'GET');
        $request->headers->set('X-Client-Timezone', 'Asia/Shanghai');

        $next = function ($req) {
            // Assert: Verify client timezone is used
            $this->assertSame('Asia/Shanghai', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware handles empty X-Timezone header.
     */
    public function testWithEmptyTimezoneHeader(): void
    {
        // Arrange: Create request with empty timezone header
        $request = Request::create(uri: '/', method: 'GET');
        $request->headers->set('X-Timezone', '');

        $next = function ($req) {
            // Assert: Verify default timezone is used
            $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware handles empty session timezone.
     */
    public function testWithEmptySessionTimezone(): void
    {
        // Arrange: Mock session with empty timezone value
        $session = Mockery::mock(Store::class);
        $session->shouldReceive('has')
            ->with('timezone')
            ->andReturn(true);
        $session->shouldReceive('get')
            ->with('timezone')
            ->andReturn('');

        $request = Request::create(uri: '/', method: 'GET');
        $request->setLaravelSession($session);

        $next = function ($req) {
            // Assert: Verify default timezone is used
            $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act: Process request through middleware
        $response = $this->middleware->handle($request, $next);

        // Assert: Verify successful response
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware correctly handles sequential requests with different timezones.
     */
    public function testResetsTimezoneAfterRequest(): void
    {
        // Arrange: First request with specific timezone
        $request1 = Request::create(uri: '/', method: 'GET');
        $request1->headers->set('X-Timezone', 'America/New_York');

        // Arrange: Second request with different timezone
        $request2 = Request::create(uri: '/', method: 'GET');
        $request2->headers->set('X-Timezone', 'Europe/Berlin');

        // First request handler
        $next1 = function ($req) {
            // Assert: Verify first request timezone
            $this->assertSame('America/New_York', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Second request handler
        $next2 = function ($req) {
            // Assert: Verify second request timezone
            $this->assertSame('Europe/Berlin', TimezoneHelper::getEffectiveTimezone());
            return response('OK');
        };

        // Act & Assert: Process first request
        $response1 = $this->middleware->handle($request1, $next1);
        $this->assertSame(200, $response1->getStatusCode());

        // Reset timezone state between requests
        TimezoneHelper::resetUserTimezone();
        TimezoneHelper::initialize();

        // Act & Assert: Process second request
        $response2 = $this->middleware->handle($request2, $next2);
        $this->assertSame(200, $response2->getStatusCode());
    }
}

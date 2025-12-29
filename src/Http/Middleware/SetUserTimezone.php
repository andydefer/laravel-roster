<?php

declare(strict_types=1);

namespace Roster\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Roster\Domain\Helpers\TimezoneHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to set user timezone from request headers, session, or user preferences.
 * Provides a prioritized fallback system for detecting the user's timezone.
 */
class SetUserTimezone
{
    /**
     * Handle an incoming request by detecting and setting the user's timezone.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @return Response The HTTP response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = $this->detectTimezone($request);

        if ($timezone !== null && TimezoneHelper::isValidTimezone($timezone)) {
            TimezoneHelper::setUserTimezone($timezone);
        }

        return $next($request);
    }

    /**
     * Detect the appropriate timezone from the request using a prioritized approach.
     *
     * Priority order:
     * 1. Explicit API header (X-Timezone)
     * 2. Session storage (web applications)
     * 3. Authenticated user preference
     * 4. Browser/client inferred timezone
     *
     * @param Request $request The HTTP request to analyze
     * @return string|null The detected timezone identifier or null if none found
     */
    private function detectTimezone(Request $request): ?string
    {
        return $this->getTimezoneFromExplicitHeader($request)
            ?? $this->getTimezoneFromSession($request)
            ?? $this->getTimezoneFromAuthenticatedUser($request)
            ?? $this->getTimezoneFromBrowser($request);
    }

    /**
     * Extract timezone from explicit API header (highest priority).
     *
     * @param Request $request The HTTP request
     * @return string|null Timezone identifier or null if not present/invalid
     */
    private function getTimezoneFromExplicitHeader(Request $request): ?string
    {
        if (!$request->hasHeader('X-Timezone')) {
            return null;
        }

        $timezone = $request->header('X-Timezone');
        return $this->validateAndReturnTimezone($timezone);
    }

    /**
     * Extract timezone from session storage.
     *
     * @param Request $request The HTTP request
     * @return string|null Timezone identifier or null if not present/invalid
     */
    private function getTimezoneFromSession(Request $request): ?string
    {
        if (!$request->hasSession() || !$request->session()->has('timezone')) {
            return null;
        }

        $timezone = $request->session()->get('timezone');
        return $this->validateAndReturnTimezone($timezone);
    }

    /**
     * Extract timezone from authenticated user's preferences.
     *
     * @param Request $request The HTTP request
     * @return string|null Timezone identifier or null if not present/invalid
     */
    private function getTimezoneFromAuthenticatedUser(Request $request): ?string
    {
        $user = $request->user();
        if ($user === null || !method_exists($user, 'getTimezone')) {
            return null;
        }

        $timezone = $user->getTimezone();
        return $this->validateAndReturnTimezone($timezone);
    }

    /**
     * Extract timezone from browser/client inferred header (lowest priority).
     *
     * @param Request $request The HTTP request
     * @return string|null Timezone identifier or null if not present/invalid
     */
    private function getTimezoneFromBrowser(Request $request): ?string
    {
        if (!$request->hasHeader('X-Client-Timezone')) {
            return null;
        }

        $timezone = $request->header('X-Client-Timezone');
        return $this->validateAndReturnTimezone($timezone);
    }

    /**
     * Validate and return timezone if it's non-empty and valid.
     *
     * @param mixed $timezone The timezone value to validate
     * @return string|null Validated timezone or null if invalid
     */
    private function validateAndReturnTimezone(mixed $timezone): ?string
    {
        if (empty($timezone) || !is_string($timezone)) {
            return null;
        }

        return TimezoneHelper::isValidTimezone($timezone) ? $timezone : null;
    }
}

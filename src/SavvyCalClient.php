<?php

namespace JeffersonGoncalves\SavvyCal;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SavvyCal REST API v1 HTTP layer. Wraps the `api.savvycal.com/v1` calls behind
 * a small static client, threading the bearer token and defensively
 * returning null (rather than throwing) on network failures or non-2xx
 * responses so callers never have to wrap every call in a try/catch.
 */
class SavvyCalClient
{
    private const BASE_URL = 'https://api.savvycal.com/v1';

    /**
     * @return array<string, mixed>|null
     */
    public static function me(): ?array
    {
        return self::json(self::get('/me'));
    }

    /**
     * @param  array<string, mixed>  $params  Additional filters: limit, after, before.
     * @return array<string, mixed>|null
     */
    public static function links(array $params = []): ?array
    {
        return self::json(self::get('/scheduling-links', $params));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function link(string $id): ?array
    {
        return self::json(self::get("/scheduling-links/{$id}"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function createLink(string $name, ?string $slug = null, ?int $durationMinutes = null): ?array
    {
        return self::json(self::post('/scheduling-links', [
            'name' => $name,
            'slug' => $slug,
            'duration_minutes' => $durationMinutes,
        ]));
    }

    /**
     * @param  array<string, mixed>  $attributes  Any writable field: name, slug, duration_minutes, ...
     * @return array<string, mixed>|null
     */
    public static function updateLink(string $id, array $attributes): ?array
    {
        return self::json(self::patch("/scheduling-links/{$id}", $attributes));
    }

    public static function deleteLink(string $id): bool
    {
        $response = self::delete("/scheduling-links/{$id}");

        return $response !== null && $response->successful();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function duplicateLink(string $id): ?array
    {
        return self::json(self::post("/scheduling-links/{$id}/duplicate"));
    }

    /**
     * Enable/disable a scheduling link.
     *
     * @return array<string, mixed>|null
     */
    public static function toggleLink(string $id): ?array
    {
        return self::json(self::post("/scheduling-links/{$id}/toggle"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function linkSlots(string $id, ?string $startTime = null, ?string $endTime = null): ?array
    {
        return self::json(self::get("/scheduling-links/{$id}/slots", [
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]));
    }

    /**
     * @param  array<string, mixed>  $params  Additional filters: limit, after, before.
     * @return array<string, mixed>|null
     */
    public static function events(array $params = []): ?array
    {
        return self::json(self::get('/events', $params));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function event(string $id): ?array
    {
        return self::json(self::get("/events/{$id}"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function createEvent(string $schedulingLinkId, string $startAt, string $name, string $email): ?array
    {
        return self::json(self::post('/events', [
            'scheduling_link_id' => $schedulingLinkId,
            'start_at' => $startAt,
            'name' => $name,
            'email' => $email,
        ]));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function cancelEvent(string $id): ?array
    {
        return self::json(self::post("/events/{$id}/cancel"));
    }

    /**
     * @param  array<string, mixed>  $params  Additional filters: limit, after, before.
     * @return array<string, mixed>|null
     */
    public static function webhooks(array $params = []): ?array
    {
        return self::json(self::get('/webhooks', $params));
    }

    /**
     * @param  list<string>  $events
     * @return array<string, mixed>|null
     */
    public static function createWebhook(string $url, array $events): ?array
    {
        return self::json(self::post('/webhooks', [
            'url' => $url,
            'events' => $events,
        ]));
    }

    public static function deleteWebhook(string $id): bool
    {
        $response = self::delete("/webhooks/{$id}");

        return $response !== null && $response->successful();
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private static function get(string $path, array $query = []): ?Response
    {
        try {
            return self::client()->get(self::BASE_URL.$path, self::filter($query));
        } catch (Throwable $e) {
            self::logFailure('get', $path, $e);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private static function post(string $path, array $body = []): ?Response
    {
        try {
            return self::client()->post(self::BASE_URL.$path, self::filter($body));
        } catch (Throwable $e) {
            self::logFailure('post', $path, $e);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private static function patch(string $path, array $body = []): ?Response
    {
        try {
            return self::client()->patch(self::BASE_URL.$path, self::filter($body));
        } catch (Throwable $e) {
            self::logFailure('patch', $path, $e);

            return null;
        }
    }

    private static function delete(string $path): ?Response
    {
        try {
            return self::client()->delete(self::BASE_URL.$path);
        } catch (Throwable $e) {
            self::logFailure('delete', $path, $e);

            return null;
        }
    }

    private static function client(): PendingRequest
    {
        $request = Http::timeout(self::timeout());

        if ($token = self::token()) {
            $request = $request->withToken($token);
        }

        return $request;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function json(?Response $response): ?array
    {
        if ($response === null || ! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? $data : null;
    }

    /**
     * Drop null values from a query/body payload before it's sent.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private static function filter(array $params): array
    {
        return array_filter($params, fn (mixed $value): bool => $value !== null);
    }

    private static function logFailure(string $context, string $target, Throwable $e): void
    {
        Log::warning('SavvyCalClient outbound fetch failed', [
            'context' => $context,
            'target' => $target,
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ]);
    }

    private static function token(): ?string
    {
        $token = config('savvycal.token') ?? config('services.savvycal.token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    private static function timeout(): int
    {
        return (int) config('savvycal.timeout', 8);
    }
}

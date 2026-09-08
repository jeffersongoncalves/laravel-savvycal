<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JeffersonGoncalves\SavvyCal\SavvyCalClient;

it('fetches the current user', function () {
    Http::fake([
        'api.savvycal.com/v1/me' => Http::response(['id' => 'user_1', 'display_name' => 'Jane Doe'], 200),
    ]);

    expect(SavvyCalClient::me())->toBe(['id' => 'user_1', 'display_name' => 'Jane Doe']);

    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer fake-token'));
});

it('returns null from me on a non-2xx', function () {
    Http::fake([
        'api.savvycal.com/v1/me' => Http::response('', 401),
    ]);

    expect(SavvyCalClient::me())->toBeNull();
});

it('lists scheduling links', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links*' => Http::response(['entries' => []], 200),
    ]);

    expect(SavvyCalClient::links(['limit' => 5]))->toBe(['entries' => []]);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'limit=5'));
});

it('fetches a single scheduling link', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links/link_1' => Http::response(['id' => 'link_1'], 200),
    ]);

    expect(SavvyCalClient::link('link_1'))->toBe(['id' => 'link_1']);
});

it('creates a scheduling link without optional fields', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links' => Http::response(['id' => 'link_1'], 201),
    ]);

    expect(SavvyCalClient::createLink('Intro call'))->toBe(['id' => 'link_1']);

    Http::assertSent(fn (Request $request) => $request->data() === ['name' => 'Intro call']);
});

it('creates a scheduling link with a slug and duration', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links' => Http::response(['id' => 'link_1'], 201),
    ]);

    SavvyCalClient::createLink('Intro call', 'intro', 30);

    Http::assertSent(fn (Request $request) => $request['slug'] === 'intro' && $request['duration_minutes'] === 30);
});

it('updates a scheduling link', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links/link_1' => Http::response(['id' => 'link_1', 'name' => 'Renamed'], 200),
    ]);

    expect(SavvyCalClient::updateLink('link_1', ['name' => 'Renamed']))->toBe(['id' => 'link_1', 'name' => 'Renamed']);

    Http::assertSent(fn (Request $request) => $request->method() === 'PATCH' && $request['name'] === 'Renamed');
});

it('deletes a scheduling link', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links/link_1' => Http::response('', 204),
    ]);

    expect(SavvyCalClient::deleteLink('link_1'))->toBeTrue();
});

it('returns false when a scheduling link deletion fails', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links/link_1' => Http::response('', 404),
    ]);

    expect(SavvyCalClient::deleteLink('link_1'))->toBeFalse();
});

it('duplicates a scheduling link', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links/link_1/duplicate' => Http::response(['id' => 'link_2'], 201),
    ]);

    expect(SavvyCalClient::duplicateLink('link_1'))->toBe(['id' => 'link_2']);
});

it('toggles a scheduling link', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links/link_1/toggle' => Http::response(['id' => 'link_1', 'state' => 'disabled'], 200),
    ]);

    expect(SavvyCalClient::toggleLink('link_1'))->toBe(['id' => 'link_1', 'state' => 'disabled']);
});

it('fetches slots for a scheduling link within a window', function () {
    Http::fake([
        'api.savvycal.com/v1/scheduling-links/link_1/slots*' => Http::response(['entries' => []], 200),
    ]);

    expect(SavvyCalClient::linkSlots('link_1', '2026-09-06T00:00:00Z', '2026-09-13T00:00:00Z'))
        ->toBe(['entries' => []]);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'start_time=2026-09-06'));
});

it('lists events', function () {
    Http::fake([
        'api.savvycal.com/v1/events*' => Http::response(['entries' => []], 200),
    ]);

    expect(SavvyCalClient::events())->toBe(['entries' => []]);
});

it('fetches a single event', function () {
    Http::fake([
        'api.savvycal.com/v1/events/event_1' => Http::response(['id' => 'event_1'], 200),
    ]);

    expect(SavvyCalClient::event('event_1'))->toBe(['id' => 'event_1']);
});

it('creates an event', function () {
    Http::fake([
        'api.savvycal.com/v1/events' => Http::response(['id' => 'event_1'], 201),
    ]);

    expect(SavvyCalClient::createEvent('link_1', '2026-09-08T14:00:00Z', 'Jane Doe', 'jane@example.com'))
        ->toBe(['id' => 'event_1']);

    Http::assertSent(fn (Request $request) => $request['scheduling_link_id'] === 'link_1'
        && $request['email'] === 'jane@example.com');
});

it('cancels an event', function () {
    Http::fake([
        'api.savvycal.com/v1/events/event_1/cancel' => Http::response(['id' => 'event_1', 'state' => 'canceled'], 200),
    ]);

    expect(SavvyCalClient::cancelEvent('event_1'))->toBe(['id' => 'event_1', 'state' => 'canceled']);
});

it('lists webhooks', function () {
    Http::fake([
        'api.savvycal.com/v1/webhooks*' => Http::response(['entries' => []], 200),
    ]);

    expect(SavvyCalClient::webhooks())->toBe(['entries' => []]);
});

it('creates a webhook', function () {
    Http::fake([
        'api.savvycal.com/v1/webhooks' => Http::response(['id' => 'hook_1'], 201),
    ]);

    expect(SavvyCalClient::createWebhook('https://example.com/webhook', ['event.created']))
        ->toBe(['id' => 'hook_1']);

    Http::assertSent(fn (Request $request) => $request['url'] === 'https://example.com/webhook'
        && $request['events'] === ['event.created']);
});

it('deletes a webhook', function () {
    Http::fake([
        'api.savvycal.com/v1/webhooks/hook_1' => Http::response('', 204),
    ]);

    expect(SavvyCalClient::deleteWebhook('hook_1'))->toBeTrue();
});

it('returns false when a webhook deletion fails', function () {
    Http::fake([
        'api.savvycal.com/v1/webhooks/hook_1' => Http::response('', 404),
    ]);

    expect(SavvyCalClient::deleteWebhook('hook_1'))->toBeFalse();
});

it('falls back to the services.savvycal.token config value', function () {
    config()->set('savvycal.token', null);
    config()->set('services.savvycal.token', 'services-token');

    Http::fake([
        'api.savvycal.com/v1/me' => Http::response(['id' => 'user_1'], 200),
    ]);

    SavvyCalClient::me();

    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer services-token'));
});

it('returns null and logs when the request throws', function () {
    Log::spy();

    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    expect(SavvyCalClient::me())->toBeNull();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context) => $message === 'SavvyCalClient outbound fetch failed'
            && $context['context'] === 'get')
        ->once();
});

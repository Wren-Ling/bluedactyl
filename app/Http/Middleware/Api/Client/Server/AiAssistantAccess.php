<?php

namespace Pterodactyl\Http\Middleware\Api\Client\Server;

use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Pterodactyl\Models\ApiKey;
use Pterodactyl\Services\Ai\AiSettingsService;
use Pterodactyl\Exceptions\Http\HttpForbiddenException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Keeps the AI assistant from being driven from outside the panel.
 *
 * The assistant spends real money upstream on every call, so it is held to
 * stricter rules than the rest of the client API:
 *
 *  - Requests authenticated with an API key are refused. The panel only calls
 *    these endpoints from the browser session, so a resolved ApiKey means
 *    someone is driving the assistant from a script, cron job or reverse proxy.
 *    Client API keys are self-service here, so without this anyone who can log
 *    in once could mint a key and resell the upstream as their own endpoint.
 *  - A per-user daily ceiling sits on top of the per-minute throttle so a slow
 *    trickle cannot spend the budget overnight while staying under the burst.
 *  - One in-flight streaming turn per user, so a client cannot fan out dozens
 *    of parallel requests and multiply the cost of a single conversation.
 */
class AiAssistantAccess
{
    /**
     * How long a streaming turn may hold its slot before it is treated as dead.
     * Slightly longer than the provider timeout so a slow reply is not cut off,
     * while a killed process still releases the slot.
     */
    private const SLOT_TTL = 900;

    public function __construct(
        private CacheRepository $cache,
        private AiSettingsService $settings,
    ) {
    }

    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user instanceof User) {
            throw new HttpForbiddenException('You must be signed in to use the AI assistant.');
        }

        if ($this->usedApiKey($request)) {
            throw new HttpForbiddenException('The AI assistant is only available from the panel interface and cannot be used with an API key.');
        }

        // Only the routes that actually reach the upstream are metered. Listing
        // the exempt routes rather than the metered ones means a new endpoint
        // is metered by default: an unlisted route cannot quietly become a free
        // path to the provider if someone forgets to update this list.
        if ($this->isLocalRead($request)) {
            return $next($request);
        }

        $this->assertDailyBudget($user);

        if (!$request->routeIs('api:client.servers.ai.stream')) {
            return $next($request);
        }

        $release = $this->acquireSlot($user);

        // A streamed response is not sent until after this middleware returns,
        // so the slot cannot be freed here: doing so would hand it back before
        // the body streamed. Register the release to run once the response has
        // been delivered, and keep the TTL as the backstop for a killed worker.
        app()->terminating($release);

        return $next($request);
    }

    /**
     * Routes served entirely from panel data, with no upstream call.
     */
    private function isLocalRead(Request $request): bool
    {
        return $request->routeIs(
            'api:client.servers.ai.config',
            'api:client.servers.ai.files',
            'api:client.servers.ai.file',
        );
    }

    /**
     * True when the request authenticated with an API key rather than the
     * browser session.
     *
     * Sanctum hands a first-party session request a TransientToken, while a
     * bearer token resolves to an ApiKey model. Checking the resolved token
     * rather than the raw Authorization header means a key passed any other
     * accepted way is still caught.
     */
    private function usedApiKey(Request $request): bool
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof ApiKey) {
            return true;
        }

        // Defensive: nothing here should ever see a raw token in the query
        // string, but refuse it if one shows up rather than spending on it.
        return $request->query('api_key') !== null || $request->query('token') !== null;
    }

    /**
     * Enforce the per-user daily request ceiling.
     *
     * The counter is keyed to the calendar day so it resets on its own, and the
     * entry expires shortly after the day rolls over. The limit comes from the
     * settings service rather than config('ai.*') because the administrator
     * sets it on the settings screen, which writes to the `settings` table.
     */
    private function assertDailyBudget(User $user): void
    {
        $limit = $this->settings->dailyLimitPerUser();

        if ($limit <= 0) {
            return;
        }

        $key = sprintf('ai:quota:%s:%s', $user->uuid, now()->format('Y-m-d'));

        // Increment before the call so simultaneous requests cannot all read
        // the same value and collectively overshoot the ceiling.
        $this->cache->add($key, 0, now()->endOfDay()->addHour());
        $used = (int) $this->cache->increment($key);

        if ($used > $limit) {
            throw new TooManyRequestsHttpException(null, 'You have reached the daily limit for AI assistant requests. The limit resets at midnight.');
        }
    }

    /**
     * Take the single concurrency slot for this user.
     *
     * @return callable():void a function that frees the slot
     */
    private function acquireSlot(User $user): callable
    {
        $key = 'ai:slot:' . $user->uuid;
        $token = bin2hex(random_bytes(8));

        // Cache::add only writes when the key is absent, which is the atomic
        // acquire primitive shared by the Redis and database stores.
        if (!$this->cache->add($key, $token, self::SLOT_TTL)) {
            throw new TooManyRequestsHttpException(null, 'You already have an AI assistant request in progress. Please wait for it to finish.');
        }

        return function () use ($key, $token) {
            // Only clear the slot if it is still ours, so a request that ran past
            // the TTL cannot free a slot that another request now holds.
            if ($this->cache->get($key) === $token) {
                $this->cache->forget($key);
            }
        };
    }
}

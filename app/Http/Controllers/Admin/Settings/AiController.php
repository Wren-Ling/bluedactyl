<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Pterodactyl\Enums\Ai\Provider;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Ai\AiClient;
use Pterodactyl\Services\Ai\AiSettingsService;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Http\Requests\Admin\Settings\AiSettingsFormRequest;

class AiController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private AiClient $client,
        private AiSettingsService $aiSettings,
        private Encrypter $encrypter,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view,
        private CacheRepository $cache,
    ) {}

    /**
     * Render the AI assistant settings screen.
     */
    public function index(): View
    {
        return $this->view->make('admin.settings.ai', [
            'providers' => Provider::toSchema(),
            'configuration' => $this->aiSettings->toAdminArray(),
        ]);
    }

    /**
     * Persist the AI assistant settings.
     *
     * An empty API key field means "leave the stored key alone" — otherwise an
     * administrator editing an unrelated option would wipe the credential.
     */
    public function update(AiSettingsFormRequest $request): RedirectResponse
    {
        $values = $request->normalize();
        $keyField = 'pterodactyl:ai:api_key';

        if (array_key_exists($keyField, $values)) {
            $submitted = trim((string) $values[$keyField]);

            if ($submitted === '') {
                // Keep whatever was there before.
                unset($values[$keyField]);
            } elseif ($request->boolean('pterodactyl:ai:clear_api_key')) {
                $values[$keyField] = '';
            } else {
                $values[$keyField] = $this->encrypter->encrypt($submitted);
            }
        }

        foreach ($values as $key => $value) {
            $this->settings->set('settings::' . $key, $value ?? '');
        }

        $this->alert->success('AI assistant settings have been updated.')->flash();

        return redirect()->route('admin.settings.ai');
    }

    /**
     * Try the stored credentials against the upstream provider.
     */
    public function test(Request $request): JsonResponse
    {
        try {
            $message = $this->client->testConnection();
        } catch (Throwable $exception) {
            return new JsonResponse(['success' => false, 'message' => $exception->getMessage()], 422);
        }

        return new JsonResponse(['success' => true, 'message' => $message]);
    }

    /**
     * Fetch the model list from the configured provider, or fall back to the
     * known model names for providers that do not expose an endpoint.
     */
    public function models(Request $request): JsonResponse
    {
        $manual = $this->aiSettings->provider();

        // Allow the admin screen to preview a provider before it is saved.
        $requested = $request->input('provider');
        if (is_string($requested) && Provider::tryFrom($requested) instanceof Provider) {
            $manual = Provider::tryFrom($requested);
        }

        if (!$manual instanceof Provider) {
            return new JsonResponse(['success' => false, 'message' => 'No provider selected.'], 422);
        }

        if (!$manual->supportsModelListing()) {
            return new JsonResponse([
                'success' => true,
                'models' => [],
                'message' => "{$manual->label()} does not publish a model list. Enter the model name manually.",
            ]);
        }

        $cacheKey = 'ai:models:' . $manual->value;

        $cached = $this->cache->get($cacheKey);
        if (is_array($cached) && $cached !== []) {
            return new JsonResponse(['success' => true, 'models' => $cached, 'cached' => true]);
        }

        try {
            $models = $this->client->listModels();
        } catch (Throwable $exception) {
            return new JsonResponse(['success' => false, 'message' => $exception->getMessage()], 422);
        }

        $this->cache->put($cacheKey, $models, now()->addMinutes(10));

        return new JsonResponse(['success' => true, 'models' => $models]);
    }
}

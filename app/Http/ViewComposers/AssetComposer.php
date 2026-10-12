<?php

namespace Pterodactyl\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Services\Ai\AiSettingsService;
use Pterodactyl\Services\Helpers\AssetHashService;
use Pterodactyl\Services\Captcha\CaptchaManager;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class AssetComposer
{
  protected CaptchaManager $captcha;
  protected SettingsRepositoryInterface $settings;
  protected AiSettingsService $ai;

  public function __construct(CaptchaManager $captcha, SettingsRepositoryInterface $settings, AiSettingsService $ai)
  {
    $this->captcha = $captcha;
    $this->settings = $settings;
    $this->ai = $ai;
  }

  /**
   * Provide access to the asset service in the views.
   */
  public function compose(View $view): void
  {
    $view->with('siteConfiguration', [
      'name' => config('app.name') ?? 'Bluedactyl',
      'locale' => config('app.locale') ?? 'en',
      'timezone' => config('app.timezone') ?? '',
      'captcha' => [
        'enabled' => $this->captcha->getDefaultDriver() !== 'none',
        'provider' => $this->captcha->getDefaultDriver(),
        'siteKey' => $this->getSiteKeyForCurrentProvider(),
        'scriptIncludes' => $this->captcha->getScriptIncludes(),
      ],
      // Only the feature flags travel to the browser. The provider credentials
      // stay on the server and are never serialized into the page.
      'ai' => [
        'enabled' => $this->ai->isUsable(),
        'allow_console' => $this->ai->allowConsole(),
        'allow_crash_analysis' => $this->ai->allowCrashAnalysis(),
      ],
    ]);
  }

  /**
   * Get the site key for the currently active captcha provider.
   */
  private function getSiteKeyForCurrentProvider(): string
  {
    $provider = $this->captcha->getDefaultDriver();

    if ($provider === 'none') {
      return '';
    }

    try {
      $driver = $this->captcha->driver();
      if (method_exists($driver, 'getSiteKey')) {
        return $driver->getSiteKey();
      }
    } catch (\Exception $e) {
      // Silently fail to avoid exposing errors to frontend
    }

    return '';
  }
}

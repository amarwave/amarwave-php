<?php

declare(strict_types=1);

namespace AmarWave\php;

use AmarWave\AmarWave;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;

/**
 * php Service Provider for AmarWave.
 *
 * Auto-discovered via composer.json "extra.php.providers".
 * Manual registration: add to config/app.php providers array:
 *   AmarWave\php\AmarWaveServiceProvider::class
 */
class AmarWaveServiceProvider extends ServiceProvider
{
    /**
     * Register the AmarWave singleton into the container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/amarwave.php',
            'amarwave'
        );

        $this->app->singleton(AmarWave::class, function ($app) {
            /** @var array $cfg */
            $cfg = $app['config']->get('amarwave', []);

            $cluster = ($cfg['cluster'] ?? null) ?: null; // null disables cluster mode

            if ($cluster !== null) {
                // Cluster mode — host/port/ssl are resolved automatically from the cluster.
                return new AmarWave(
                    appKey:    (string) ($cfg['app_key']    ?? ''),
                    appSecret: (string) ($cfg['app_secret'] ?? ''),
                    cluster:   $cluster,
                    timeout:   (int)    ($cfg['timeout']    ?? 10),
                );
            }

            // Manual mode — use explicit host / port / ssl.
            return new AmarWave(
                appKey:    (string) ($cfg['app_key']    ?? ''),
                appSecret: (string) ($cfg['app_secret'] ?? ''),
                cluster:   null,
                timeout:   (int)    ($cfg['timeout']    ?? 10),
            );
        });

        $this->app->alias(AmarWave::class, 'amarwave');
    }

    /**
     * Bootstrap services: publish config, register the broadcasting driver.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/amarwave.php' => config_path('amarwave.php'),
            ], 'amarwave-config');
        }

        // Register the 'amarwave' broadcasting driver so php's broadcast()
        // helper routes ShouldBroadcast events through AmarWave.
        // Set BROADCAST_DRIVER=amarwave (php ≤10) or
        //     BROADCAST_CONNECTION=amarwave (php 11+) in .env.
        $this->app->resolving(BroadcastManager::class, function (BroadcastManager $manager) {
            $manager->extend('amarwave', function ($app) {
                return new AmarWaveBroadcaster($app->make(AmarWave::class));
            });
        });
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [AmarWave::class, 'amarwave'];
    }
}

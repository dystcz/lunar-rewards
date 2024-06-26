<?php

namespace Dystcz\LunarRewards;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LunarRewardsServiceProvider extends ServiceProvider
{
    protected array $configFiles = [
        'rewards',
    ];

    protected $root = __DIR__.'/..';

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->registerConfig();

        $this->loadTranslationsFrom(
            "{$this->root}/lang",
            'lunar-rewards',
        );

        $this->booting(function () {
            $this->registerPolicies();
        });

        // Register the main class to use with the facade.
        $this->app->singleton(
            'lunar-rewards',
            fn () => new LunarRewards,
        );

        $this->app->singleton(
            \Dystcz\LunarRewards\Domain\Rewards\Contracts\RewardPointsCalculator::class,
            fn () => new (Config::get(
                'lunar-rewards.reward_point_calculator',
                \Dystcz\LunarRewards\Domain\Rewards\Calculators\RewardPointsCalculator::class,
            )),
        );
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom("{$this->root}/routes/api.php");
        $this->loadMigrationsFrom("{$this->root}/database/migrations");

        $this->registerModels();
        $this->registerObservers();
        $this->registerEvents();

        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishTranslations();
            $this->publishMigrations();
            $this->registerCommands();
        }
    }

    /**
     * Publish config files.
     */
    protected function publishConfig(): void
    {
        foreach ($this->configFiles as $configFile) {
            $this->publishes([
                "{$this->root}/config/{$configFile}.php" => config_path("lunar-rewards/{$configFile}.php"),
            ], 'lunar-rewards');
        }
    }

    /**
     * Publish translations.
     */
    protected function publishTranslations(): void
    {
        $this->publishes([
            "{$this->root}/lang" => $this->app->langPath('vendor/lunar-rewards'),
        ], 'lunar-rewards.translations');
    }

    /**
     * Register config files.
     */
    protected function registerConfig(): void
    {
        foreach ($this->configFiles as $configFile) {
            $this->mergeConfigFrom(
                "{$this->root}/config/{$configFile}.php",
                "lunar-rewards.{$configFile}",
            );
        }
    }

    /**
     * Publish migrations.
     */
    protected function publishMigrations(): void
    {
        $this->publishes([
            "{$this->root}/database/migrations/" => $this->app->databasePath('migrations'),
        ], 'lunar-rewards.migrations');
    }

    /**
     * Register commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            //
        ]);
    }

    /**
     * Register events.
     */
    protected function registerEvents(): void
    {
        $events = [
            //
        ];

        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Register observers.
     */
    protected function registerObservers(): void
    {
        //
    }

    /**
     * Swap models.
     */
    protected function registerModels(): void
    {
        //
    }

    /**
     * Register the application's policies.
     */
    public function registerPolicies(): void
    {
        //
    }
}

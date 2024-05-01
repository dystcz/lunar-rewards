<?php

namespace Dystcz\LunarRewards\Tests;

use Dystcz\LunarApi\Base\Facades\SchemaManifestFacade;
use Dystcz\LunarRewards\Tests\Stubs\Lunar\TestTaxDriver;
use Dystcz\LunarRewards\Tests\Stubs\Lunar\TestUrlGenerator;
use Dystcz\LunarRewards\Tests\Stubs\Users\User;
use Dystcz\LunarRewards\Tests\Stubs\Users\UserSchema;
use Dystcz\LunarRewards\Tests\Traits\CreatesTestingModels;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\Config;
use LaravelJsonApi\Testing\MakesJsonApiRequests;
use LaravelJsonApi\Testing\TestExceptionHandler;
use Lunar\Facades\Taxes;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use CreatesTestingModels;
    use MakesJsonApiRequests;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        Config::set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);

        Config::set('lunar.urls.generator', TestUrlGenerator::class);
        Config::set('lunar.taxes.driver', 'test');

        Taxes::extend('test', fn (Application $app) => $app->make(TestTaxDriver::class));

        activity()->disableLogging();
    }

    /**
     * @param  Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            // Ray
            \Spatie\LaravelRay\RayServiceProvider::class,

            // Spatie Permissions
            \Spatie\Permission\PermissionServiceProvider::class,

            // Laravel JsonApi
            \LaravelJsonApi\Encoder\Neomerx\ServiceProvider::class,
            \LaravelJsonApi\Laravel\ServiceProvider::class,
            \LaravelJsonApi\Spec\ServiceProvider::class,

            // Lunar core
            \Lunar\LunarServiceProvider::class,
            \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
            \Spatie\Activitylog\ActivitylogServiceProvider::class,
            \Cartalyst\Converter\Laravel\ConverterServiceProvider::class,
            \Kalnoy\Nestedset\NestedSetServiceProvider::class,
            \Spatie\LaravelBlink\BlinkServiceProvider::class,

            // Lunar Api
            \Dystcz\LunarApi\LunarApiServiceProvider::class,
            \Dystcz\LunarApi\JsonApiServiceProvider::class,

            // Livewire
            \Lunar\LivewireTables\LivewireTablesServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,

            // Lunar Hub
            \Lunar\Hub\AdminHubServiceProvider::class,

            // Laravel Wallet
            \O21\LaravelWallet\ServiceProvider::class,

            // Lunar Rewards
            \Dystcz\LunarRewards\LunarRewardsServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        /**
         * Lunar configuration.
         */
        Config::set('lunar.cart.auto_create', true);
        Config::set('lunar.payments.default', 'cash-in-hand');

        /**
         * App configuration.
         */
        Config::set('database.default', 'sqlite');
        Config::set('database.migrations', 'migrations');
        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        /**
         * Wallet configuration.
         */
        Config::set('wallet.default_currency', 'RP');
        Config::set('wallet.table_names', [
            'balances' => 'lunar_rewards_balances',
            'balance_states' => 'lunar_rewards_balance_states',
            'transactions' => 'lunar_rewards_transactions',
        ]);

        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => 'mysql',
            'port' => '3306',
            'database' => 'lunar-rewards-testing',
            'username' => 'homestead',
            'password' => 'secret',
        ]);

        /**
         * Schema configuration.
         */
        SchemaManifestFacade::registerSchema(UserSchema::class);
    }

    /**
     * Define database migrations.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }

    /**
     * Set up the database.
     */
    protected function setUpDatabase(): void
    {
        $walletMigrations = [
            'database/migrations/create_balances_table.php.stub',
            'database/migrations/create_transactions_table.php.stub',
            'database/migrations/create_balance_states_table.php.stub',
        ];

        foreach ($walletMigrations as $migration) {
            $migration = include __DIR__."/../vendor/021/laravel-wallet/{$migration}";

            $migration->up();
        }
    }

    /**
     * Resolve application HTTP exception handler implementation.
     */
    protected function resolveApplicationExceptionHandler($app): void
    {
        $app->singleton(
            ExceptionHandler::class,
            TestExceptionHandler::class
        );
    }
}

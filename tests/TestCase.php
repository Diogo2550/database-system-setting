<?php

namespace Diogo2550\DatabaseSystemSetting\Tests;

use Diogo2550\DatabaseSystemSetting\DatabaseSystemSettingServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static $migration;

    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$migration) {
            self::$migration = require __DIR__ . '/../database/migrations/2026_05_01_010949_system_setting.php';
        }

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            DatabaseSystemSettingServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set default config values (internal namespace)
        $app['config']->set('settings.__internal__.cache.enabled', true);
        $app['config']->set('settings.__internal__.cache.key', 'dsystem_setting_test');
        $app['config']->set('settings.__internal__.cache.ttl', 3600);
        $app['config']->set('settings.__internal__.table_name', 'system_settings');
    }

    protected function setUpDatabase(Application $app): void
    {
        $tableName = $app['config']->get('settings.__internal__.table_name', 'system_settings');
        $schema = $app['db']->connection()->getSchemaBuilder();

        if ($schema->hasTable($tableName)) {
            $schema->drop($tableName);
        }

        self::$migration->up();

        // Ensure test cache key is applied after migrations
        config(['settings.__internal__.cache.key' => 'dsystem_setting_test']);
        Cache::forget(config('settings.__internal__.cache.key'));
    }
}

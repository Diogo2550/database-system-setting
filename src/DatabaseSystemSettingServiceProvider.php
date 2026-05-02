<?php

namespace Diogo2550\DatabaseSystemSetting;

use Diogo2550\DatabaseSystemSetting\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;

class DatabaseSystemSettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/settings.php', 'settings');
    }

    public function boot() {
        $this->publishes([
            __DIR__ . '/../database/migrations/2026_05_01_010949_system_setting.php' => database_path('migrations/2026_05_01_010949_system_setting.php'),
            __DIR__ . '/../config/settings.php' => config_path('settings.php'),
            __DIR__ . '/../config/settings-schema.php' => config_path('settings-schema.php'),
        ], 'database-system-setting');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Diogo2550\DatabaseSystemSetting\Console\Commands\SyncSettingsCommand::class,
            ]);
        }
        
        /**
         * Load the settings from the database and set them in the config. 
         * We will cache the settings to avoid hitting the database on every request.
         */
        $this->mergeSettingsIntoConfig();
    }
    
    protected function mergeSettingsIntoConfig() {
        /**
         * Try is necessary to avoid errors when the database is not available (e.g. during the first migration). 
         * In this case, we will just return an empty array and the settings will be loaded on the next request.
         */
        try {
            $settings = $this->getSettings();
        } catch(\Throwable $e) {
            logger()->error('[SystemSettings] Erro ao carregar as configurações do sistema: ' . $e->getMessage());
            $settings = [];
        }
        
        foreach ($settings as $key => $value) {
            config()->set('settings.' . $key, $value);
        }
    }
    
    protected function getSettings(): array {
        $ttl = config('settings.__internal__.cache.ttl');
        $cacheKey = config('settings.__internal__.cache.key');
        $cacheEnabled = config('settings.__internal__.cache.enabled');
        
        /**
         * We only exclude null values so valid falsy settings like 0, false and ''
         * can still be loaded into the runtime config.
         */
        $settings = fn() => SystemSetting::all()->pluck('value', 'key')->reject(function ($value) {
            return is_null($value);
        })->toArray();
        
        if(!$cacheEnabled) {
            return $settings();
        }
        return cache()->remember($cacheKey, $ttl, function() use ($settings) {
            return $settings();
        });
    }
    
}

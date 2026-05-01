<?php

namespace Diogo2550\DatabaseSystemSetting;

use Closure;
use Diogo2550\DatabaseSystemSetting\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;
use Override;

class DatabaseSystemSettingServiceProvider extends ServiceProvider
{
    /**
     * Load the settings from the database and set them in the config. 
     * We will cache the settings to avoid hitting the database on every request.
     */
    public function boot() {
        $this->publishes([
            __DIR__ . '/database/migrations/2026_05_01_010949_system_setting.php' => database_path('migrations/2026_05_01_010949_system_setting.php'),
            __DIR__ . '/config/settings.php' => config_path('settings.php'),
        ], 'database-system-setting');
        
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
        $ttl = config('settings.cache.ttl');
        $cacheKey = config('settings.cache.key');
        $cacheEnabled = config('settings.cache.enabled');
        
        /**
         * We will filter the settings to avoid loading empty values. 
         * This is useful to avoid loading settings that are not set yet.
         * 
         * If you want to load empty values, you can leave the default value for config() as null.
         * If you cannot do it, you can open an issue or create a pull request to add this feature on Github.
         */
        $settings = fn() => SystemSetting::all()->pluck('value', 'key')->filter()->toArray();
        
        if(!$cacheEnabled) {
            return $settings();
        }
        return cache()->remember($cacheKey, $ttl, function() use ($settings) {
            return $settings();
        });
    }
    
}

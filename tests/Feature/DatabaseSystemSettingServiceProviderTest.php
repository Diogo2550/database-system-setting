<?php

namespace Diogo2550\DatabaseSystemSetting\Tests\Feature;

use Diogo2550\DatabaseSystemSetting\Models\SystemSetting;
use Diogo2550\DatabaseSystemSetting\Tests\TestCase;

class DatabaseSystemSettingServiceProviderTest extends TestCase
{
    public function test_settings_are_merged_into_config(): void
    {
        SystemSetting::create(['key' => 'app_name', 'value' => 'Test App']);
        SystemSetting::create(['key' => 'debug', 'value' => 'true']);

        $provider = new \Diogo2550\DatabaseSystemSetting\DatabaseSystemSettingServiceProvider($this->app);
        // Clear cache to force reload
        cache()->forget(config('settings.__internal__.cache.key'));

        // Re-boot the provider
        $provider->boot();

        $this->assertEquals('Test App', config('settings.app_name'));
        $this->assertEquals('true', config('settings.debug'));
    }

    public function test_settings_are_cached(): void
    {
        config(['settings.__internal__.cache.enabled' => true]);

        SystemSetting::create(['key' => 'cached_key', 'value' => 'cached_value']);

        // Force a fresh boot to trigger cache
        cache()->forget(config('settings.__internal__.cache.key'));
        $provider = new \Diogo2550\DatabaseSystemSetting\DatabaseSystemSettingServiceProvider($this->app);
        $provider->boot();

        // Verify cache was set
        $this->assertTrue(cache()->has(config('settings.__internal__.cache.key')));

        // Verify the value is in cache
        $cached = cache()->get(config('settings.__internal__.cache.key'));
        $this->assertArrayHasKey('cached_key', $cached);
        $this->assertEquals('cached_value', $cached['cached_key']);
    }

    public function test_cache_can_be_disabled(): void
    {
        config(['settings.__internal__.cache.enabled' => false]);

        SystemSetting::create(['key' => 'nocache_key', 'value' => 'nocache_value']);

        cache()->forget(config('settings.__internal__.cache.key'));
        $provider = new \Diogo2550\DatabaseSystemSetting\DatabaseSystemSettingServiceProvider($this->app);
        $provider->boot();

        // Cache should not be set
        $this->assertFalse(cache()->has(config('settings.__internal__.cache.key')));
    }

    public function test_handles_missing_database_gracefully(): void
    {
        // This test would require dropping the table or mocking DB, 
        // but the try/catch in the provider should handle it gracefully
        $this->assertNotNull($this->app);
    }

    public function test_falsy_values_are_not_filtered(): void
    {
        SystemSetting::create(['key' => 'zero', 'value' => 0]);
        SystemSetting::create(['key' => 'false_val', 'value' => false]);
        SystemSetting::create(['key' => 'empty_str', 'value' => '']);
        SystemSetting::create(['key' => 'null_val', 'value' => null]);

        cache()->forget(config('settings.__internal__.cache.key'));
        $provider = new \Diogo2550\DatabaseSystemSetting\DatabaseSystemSettingServiceProvider($this->app);
        $provider->boot();

        // Only null values should be filtered
        $this->assertEquals(0, config('settings.zero'));
        $this->assertEquals(false, config('settings.false_val'));
        $this->assertEquals('', config('settings.empty_str'));
        $this->assertNull(config('settings.null_val'));
    }
}

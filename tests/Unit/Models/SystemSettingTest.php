<?php

namespace Diogo2550\DatabaseSystemSetting\Tests\Unit\Models;

use Diogo2550\DatabaseSystemSetting\Models\SystemSetting;
use Diogo2550\DatabaseSystemSetting\Tests\TestCase;

class SystemSettingTest extends TestCase
{
    public function test_can_create_setting(): void
    {
        $setting = SystemSetting::create([
            'key' => 'test_key',
            'value' => 'test_value',
            'description' => 'A test setting',
            'schema' => ['type' => 'string'],
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_key',
            'value' => 'test_value',
            'description' => 'A test setting',
        ]);

        $this->assertEquals('test_key', $setting->key);
        $this->assertEquals('test_value', $setting->value);
    }

    public function test_schema_is_cast_to_array(): void
    {
        $schema = ['type' => 'string', 'required' => true];
        $setting = SystemSetting::create([
            'key' => 'schema_test',
            'value' => 'test',
            'schema' => $schema,
        ]);

        $retrieved = SystemSetting::where('key', 'schema_test')->first();
        
        $this->assertIsArray($retrieved->schema);
        $this->assertEquals($schema, $retrieved->schema);
    }

    public function test_fillable_fields(): void
    {
        $fillable = (new SystemSetting())->getFillable();
        
        $this->assertContains('key', $fillable);
        $this->assertContains('value', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('schema', $fillable);
    }

    public function test_no_created_at_timestamp(): void
    {
        $setting = SystemSetting::create([
            'key' => 'no_created_at',
            'value' => 'test',
        ]);

        $this->assertNull($setting->created_at);
    }

    public function test_cache_cleared_on_save(): void
    {
        // Set a cache entry
        cache()->set('dsystem_setting_test', ['old_key' => 'old_value']);
        $this->assertTrue(cache()->has('dsystem_setting_test'));

        // Create or update a setting
        SystemSetting::create([
            'key' => 'cache_test',
            'value' => 'test',
        ]);

        // Cache should be cleared
        $this->assertFalse(cache()->has('dsystem_setting_test'));
    }

    public function test_cache_cleared_on_delete(): void
    {
        $setting = SystemSetting::create([
            'key' => 'delete_cache_test',
            'value' => 'test',
        ]);

        cache()->set('dsystem_setting_test', ['data' => 'value']);
        $this->assertTrue(cache()->has('dsystem_setting_test'));

        $setting->delete();

        $this->assertFalse(cache()->has('dsystem_setting_test'));
    }
}

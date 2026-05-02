<?php

namespace Diogo2550\DatabaseSystemSetting\Tests\Feature\Commands;

use Diogo2550\DatabaseSystemSetting\Models\SystemSetting;
use Diogo2550\DatabaseSystemSetting\Tests\TestCase;

class SyncSettingsCommandTest extends TestCase
{
    public function test_sync_creates_missing_settings(): void
    {
        config(['settings-schema.schema' => [
            'app_name' => [
                'default' => 'My App',
                'description' => 'Application name',
                'schema' => ['type' => 'string'],
            ],
            'debug_mode' => [
                'default' => false,
                'description' => 'Enable debug mode',
                'schema' => ['type' => 'boolean'],
            ],
        ]]);

        $this->artisan('database-system-setting:sync')
            ->expectsOutput('2 setting(s) synchronized.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('system_settings', ['key' => 'app_name', 'value' => 'My App']);
        $this->assertDatabaseHas('system_settings', ['key' => 'debug_mode', 'value' => 0]);
    }

    public function test_sync_does_not_overwrite_existing_settings(): void
    {
        SystemSetting::create([
            'key' => 'existing_key',
            'value' => 'original_value',
        ]);

        config(['settings-schema.schema' => [
            'existing_key' => [
                'default' => 'new_default_value',
                'description' => 'Should not overwrite',
            ],
            'new_key' => [
                'default' => 'new_value',
                'description' => 'New setting',
            ],
        ]]);

        $this->artisan('database-system-setting:sync')
            ->expectsOutput('1 setting(s) synchronized.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('system_settings', ['key' => 'existing_key', 'value' => 'original_value']);
        $this->assertDatabaseHas('system_settings', ['key' => 'new_key', 'value' => 'new_value']);
    }

    public function test_sync_stores_description_and_schema(): void
    {
        config(['settings-schema.schema' => [
            'test_setting' => [
                'default' => 'value',
                'description' => 'Test description',
                'schema' => ['type' => 'string', 'max_length' => 100],
            ],
        ]]);

        $this->artisan('database-system-setting:sync')->assertExitCode(0);

        $setting = SystemSetting::where('key', 'test_setting')->first();

        $this->assertNotNull($setting);
        $this->assertEquals('Test description', $setting->description);
        $this->assertIsArray($setting->schema);
        $this->assertEquals('string', $setting->schema['type']);
        $this->assertEquals(100, $setting->schema['max_length']);
    }

    public function test_sync_handles_json_values(): void
    {
        config(['settings-schema.schema' => [
            'json_setting' => [
                'default' => ['key' => 'value', 'nested' => ['data' => 'test']],
                'description' => 'JSON setting',
            ],
        ]]);

        $this->artisan('database-system-setting:sync')->assertExitCode(0);

        $setting = SystemSetting::where('key', 'json_setting')->first();

        $this->assertNotNull($setting);
        $this->assertStringContainsString('key', $setting->value);
        $this->assertStringContainsString('nested', $setting->value);
    }

    public function test_sync_handles_falsy_values(): void
    {
        config(['settings-schema.schema' => [
            'zero_value' => ['default' => 0],
            'false_value' => ['default' => false],
            'empty_string' => ['default' => ''],
            'null_value' => ['default' => null],
        ]]);

        $this->artisan('database-system-setting:sync')->assertExitCode(0);

        $this->assertDatabaseHas('system_settings', ['key' => 'zero_value', 'value' => 0]);
        $this->assertDatabaseHas('system_settings', ['key' => 'false_value', 'value' => 0]);
        $this->assertDatabaseHas('system_settings', ['key' => 'empty_string', 'value' => '']);
        $this->assertDatabaseHas('system_settings', ['key' => 'null_value', 'value' => null]);
    }

    public function test_sync_with_empty_schema_does_nothing(): void
    {
        config(['settings-schema.schema' => []]);

        $this->artisan('database-system-setting:sync')
            ->expectsOutput('No settings definitions were found in config/settings-schema.php.')
            ->assertExitCode(0);
    }

    public function test_prune_flag_deletes_orphaned_settings(): void
    {
        SystemSetting::create(['key' => 'orphaned_1', 'value' => 'test1']);
        SystemSetting::create(['key' => 'orphaned_2', 'value' => 'test2']);
        SystemSetting::create(['key' => 'keep_this', 'value' => 'keep']);

        config(['settings-schema.schema' => [
            'keep_this' => ['default' => 'keep'],
        ]]);

        $this->artisan('database-system-setting:sync --prune')
            ->expectsConfirmation('Are you sure you want to delete these 2 setting(s)?', 'yes')
            ->expectsOutput('2 orphaned setting(s) deleted.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('system_settings', ['key' => 'orphaned_1']);
        $this->assertDatabaseMissing('system_settings', ['key' => 'orphaned_2']);
        $this->assertDatabaseHas('system_settings', ['key' => 'keep_this']);
    }

    public function test_prune_flag_cancels_on_user_refusal(): void
    {
        SystemSetting::create(['key' => 'orphaned', 'value' => 'test']);

        config(['settings-schema.schema' => [
            'keep_this' => ['default' => 'keep'],
        ]]);

        $this->artisan('database-system-setting:sync --prune')
            ->expectsConfirmation('Are you sure you want to delete these 1 setting(s)?', 'no')
            ->expectsOutput('Pruning cancelled.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('system_settings', ['key' => 'orphaned']);
    }

    public function test_prune_flag_finds_no_orphans(): void
    {
        SystemSetting::create(['key' => 'keep_this', 'value' => 'keep']);

        config(['settings-schema.schema' => [
            'keep_this' => ['default' => 'keep'],
        ]]);

        $this->artisan('database-system-setting:sync --prune')
            ->expectsOutput('No orphaned settings to prune.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('system_settings', ['key' => 'keep_this']);
    }
}

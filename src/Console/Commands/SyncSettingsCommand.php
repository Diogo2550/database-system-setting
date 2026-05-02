<?php

namespace Diogo2550\DatabaseSystemSetting\Console\Commands;

use Diogo2550\DatabaseSystemSetting\Models\SystemSetting;
use Illuminate\Console\Command;

class SyncSettingsCommand extends Command
{
    protected $signature = 'database-system-setting:sync {--prune : Delete settings that no longer exist in the schema}';

    protected $description = 'Create missing database settings using the defaults defined in config/settings-schema.php, optionally prune orphaned settings.';

    public function handle(): int
    {
        $definitions = config('settings-schema.schema', []);
        
        if (!is_array($definitions) || $definitions === []) {
            $this->info('No settings definitions were found in config/settings-schema.php.');

            return self::SUCCESS;
        }

        $definitionKeys = array_keys($definitions);
        $existingKeys = array_flip(
            SystemSetting::query()
                ->whereIn('key', $definitionKeys)
                ->pluck('key')
                ->all()
        );

        $created = 0;

        foreach ($definitions as $key => $definition) {
            if (isset($existingKeys[$key])) {
                continue;
            }

            $defaultValue = is_array($definition) ? ($definition['default'] ?? null) : $definition;
            $description = is_array($definition) ? ($definition['description'] ?? null) : null;
            $schema = is_array($definition) ? ($definition['schema'] ?? null) : null;

            SystemSetting::query()->create([
                'key' => $key,
                'value' => $this->normalizeValue($defaultValue),
                'description' => $description,
                'schema' => $schema,
            ]);

            $created++;
        }

        $this->info(sprintf('%d setting(s) synchronized.', $created));

        if ($this->option('prune')) {
            return $this->pruneOrphanedSettings($definitions);
        }

        return self::SUCCESS;
    }

    protected function pruneOrphanedSettings(array $definitions): int
    {
        $definitionKeys = array_keys($definitions);
        $orphanedSettings = SystemSetting::query()
            ->whereNotIn('key', $definitionKeys)
            ->get();

        if ($orphanedSettings->isEmpty()) {
            $this->info('No orphaned settings to prune.');
            return self::SUCCESS;
        }

        $this->line('');
        $this->warn('WARNING: This action cannot be undone!');
        $this->line('The following settings will be deleted:');
        $this->line('');
        foreach ($orphanedSettings as $setting) {
            $this->line('  - ' . $setting->key);
        }
        $this->line('');

        if (!$this->confirm('Are you sure you want to delete these ' . $orphanedSettings->count() . ' setting(s)?')) {
            $this->info('Pruning cancelled.');
            return self::SUCCESS;
        }

        $deleted = $orphanedSettings->count();
        $orphanedSettings->each(fn($setting) => $setting->delete());

        $this->info(sprintf('%d orphaned setting(s) deleted.', $deleted));
        return self::SUCCESS;
    }

    protected function normalizeValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $value;
    }
}
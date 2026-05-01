<?php

namespace Diogo2550\DatabaseSystemSetting\Console\Commands;

use Diogo2550\DatabaseSystemSetting\Models\SystemSetting;
use Illuminate\Console\Command;

class SyncSettingsCommand extends Command
{
    protected $signature = 'database-system-setting:sync';

    protected $description = 'Create missing database settings using the defaults defined in config/settings.php.';

    public function handle(): int
    {
        $definitions = config('settings.settings', []);

        if (!is_array($definitions) || $definitions === []) {
            $this->info('No settings definitions were found in config/settings.php.');

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
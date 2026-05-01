<?php

return [
    /**
     * Don't modify these values unless you know what you're doing.
     */
    'cache' => [
        /**
         * If you want to disable caching, set this value to false. 
         * This will make the package hit the database on every request, which can be a performance issue.
         * This is useful during development or if you have a very small number of settings and you don't want to cache them.
         */
        'enabled' => true,
        'key' => 'dsystem_setting',
        'ttl' => 60 * 60 * 24, // 24 hours
    ],
    'table_name' => 'system_settings',
    
    /**
     * Here you can define the settings that should be synchronized to the database.
     *
     * Each item may contain:
     * - default: the value that will be inserted when the setting does not exist yet.
     * - description: a human-readable description for the admin UI.
     * - schema: extra metadata for validation and rendering.
     */
    'settings' => [
        'date_format' => [
            'default' => 'Y-m-d',
            'description' => 'Formato de data padrão do sistema.',
            'schema' => [
                'type' => 'string',
                'required' => true,
                'max_length' => 12,
            ],
        ],
    ],
    
    'date_format' => 'Y-m-d',
];
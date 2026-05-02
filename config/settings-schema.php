<?php

return [
    /**
     * Here you can define the settings that should be synchronized to the database.
     *
     * Each item may contain:
     * - default: the value that will be inserted when the setting does not exist yet.
     * - description: a human-readable description for the admin UI.
     * - schema: extra metadata for validation and rendering.
     */
    'date_format' => [
        'default' => 'Y-m-d',
        'description' => 'Formato de data padrão do sistema.',
        'schema' => [
            'type' => 'string',
            'required' => true,
            'max_length' => 12,
        ],
    ],
];
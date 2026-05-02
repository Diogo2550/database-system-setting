<?php

return [
    /**
     * Don't modify these values unless you know what you're doing.
     */
    '___internal___' => [
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
    ],
    
    /**
     * Define your system settings below here. The key of each setting will be used to access it via the config('settings.KEY') helper.
     * The date_format setting here and in settings-schema.php is just an example. You can remove it and add your own settings as needed.
     */
    'date_format' => 'Y-m-d',
];
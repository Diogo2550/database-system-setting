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
     * Here you can set the default values for your settings. You can also set the validation rules for your settings here.
     */
    
];
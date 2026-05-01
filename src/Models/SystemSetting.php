<?php

namespace Diogo2550\DatabaseSystemSetting\Models;

class SystemSetting extends \Illuminate\Database\Eloquent\Model {
    
    const CREATED_AT = null;
    protected $fillable = ['key', 'value'];
    
    public function getTable() {
        return config('settings.table_name');
    }
    
    public static function booted() {
        /**
         * Clean cache when a setting is saved or deleted. 
         */
        $clearCache = function() {
            $key = config('settings.cache.key');
            cache()->forget($key);
        };
        
        static::saved($clearCache);
        static::deleted($clearCache);
    }
    
}
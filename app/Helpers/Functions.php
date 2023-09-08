<?php

use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;


function SettingWithoutCache($name = null, $default = null) {
    return Setting($name, $default, true);
}

function Setting($name = null, $default = null, $disableCache = false) {
    try {
        if (!$disableCache) {
            // 尝试从缓存中获取
            $cachedValue = Cache::remember('setting:' . $name, 60, function () use ($name, $default) {
                return app(SettingService::class)->get($name, $default);
            });

            return $cachedValue;
        }

        if ($name === null) {
            return app(SettingService::class)->getAll();
        }

        return app(SettingService::class)->get($name, $default);
    } catch (Exception $e) {
        return null;
    }
}

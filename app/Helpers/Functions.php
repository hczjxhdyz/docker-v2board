<?php

use App\Services\SettingService;

function Setting($name = null, $default = null){
    if ($name === null) {
    return app(SettingService::class)->getAll();
    }

    return app(SettingService::class)->get($name, $default);
}

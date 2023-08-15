<?php

use App\Services\SettingService;

function Setting($name = null, $default = null){
    try{
        if ($name === null) {
        return app(SettingService::class)->getAll();
        }
        return app(SettingService::class)->get($name, $default);
    }catch(Exception $e){
        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'v2_settings';
    protected $guarded  = [];

    public function getValueAttribute($value)
    {
        $decodedValue = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decodedValue;
        }

        // 如果不是有效的 JSON 数据，则保持为字符串
        return $value;
    }
}

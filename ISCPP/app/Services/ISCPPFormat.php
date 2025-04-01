<?php

namespace App\Services;

use Carbon\Carbon;

class ISCPPFormat {
    public static function formatDateWithSeconds(Carbon $carbon){
        return $carbon->tz(auth()->user()->timezone())->toDateTimeString();
    }
}
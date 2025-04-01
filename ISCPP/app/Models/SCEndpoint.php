<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SCEndpoint extends Model
{
    protected $table = 'sc_endpoints';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'hostname',
    ];
}

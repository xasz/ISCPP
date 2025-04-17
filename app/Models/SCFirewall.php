<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SCFirewall extends Model
{
    
    protected $table = 'sc_firewalls';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'hostname',
    ];
}

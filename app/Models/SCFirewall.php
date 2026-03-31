<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SCFirewall extends Model
{
    use HasFactory;

    protected $table = 'sc_firewalls';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenantId',
        'hostname',
        'rawData',
    ];

    protected $casts = [
        'rawData' => 'array',
    ];
}

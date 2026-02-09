<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SCAlertAutoAction extends Model
{
    use HasFactory;

    protected $table = 'sc_alert_auto_actions';
    protected $fillable = [
        'type',
        'action',
    ];
}

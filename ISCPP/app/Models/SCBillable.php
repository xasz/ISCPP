<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Jobs\SendSCBillableToHalo;
use App\Services\HaloService;

class SCBillable extends Model
{
    protected $table = 'sc_billable';
    protected $fillable = [
        'month',
        'year',
        'tenantId',
        'orderLineItemNumber',
        'productGroup',
        'billableQuantity',
        'orderedQuantity',
        'actualQuantity',
        'productCode',
        'sku',
        'productDescription',
        'rawData',
        'sent_to_halo',
    ];
    protected $casts = [
        'month' => 'int',
        'year' => 'int',
        'billableQuantity' => 'int',
        'orderedQuantity' => 'int',
        'actualQuantity' => 'int',
        'rawData' => 'array',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }


    public function ISCPPProductCode(){
        return "ISCPP-" . $this->productCode;
    }
}

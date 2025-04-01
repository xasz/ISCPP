<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCBillable;
use App\Models\SCTenant;
use App\Services\HaloService;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class PushToHaloSCTenantSCBillables implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    public $tries = 5;

    public function __construct(
        public SCTenant $sctenant,
        public int $year,
        public int $month,
    ) {
        $this->sctenant = $sctenant->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(HaloService $haloService): void
    {
        Event::log("scbillables", "info" , [
            'message' => 'Start sending SCBillables for SCTenant to Halo',
            'SCTenantID' => $this->sctenant->id
        ]);

        if($this->sctenant->haloclient_id == -1){
            Event::log("scbillables", "error" , [
                'message' => __('SCBillables sent to halo failed - no haloclient_id'),
                'SCTenantID' => $this->sctenant->id
            ]);
            $this->release(120);
            return;
        }

        $scbillables = $this->sctenant->SCBillables()
            ->where('year', $this->year)
            ->where('month', $this->month)
            ->get();

        if($scbillables->count() == 0){
            Event::log("scbillables", "warning" , [
                'message' => __('No SCBillables to send to Halo'),
                'SCTenantID' => $this->sctenant->id,
                'year' => $this->year,
                'month' => $this->month,
            ]);
            return;
        }

        $licences = $haloService->haloGetPaginate('softwarelicence', [
            'client_id' => $this->sctenant->haloclient_id,
            'licence_type' => 1,
            'includeinactive' => false
        ], 'licences');


        $endofMonth = Carbon::create($this->year, $this->month)
            ->endOfMonth()
            ->format('Y-m-d\Th:i:s');


        $targets = collect();

        foreach($scbillables as $scbillable){
            $licence = $licences->where('snowid', $scbillable->ISCPPProductCode())
                                ->first();            
            
            $target = [
                "client_id" => $this->sctenant->haloclient_id,
                "end_date" => $endofMonth,
                "name" => $scbillable->productDescription,
                "count" => $scbillable->billableQuantity,
                "snowid" => $scbillable->ISCPPProductCode(),
            ];

            if($licence != null){
                $target['id'] = $licence['id'];
            }else{
                $target = array_merge($target, [
                    "type" => "1",
                    "billing_cycle" => "Monthly",
                    "purchase_price" => 0,
                    "price" => 0,
                    "monthly_cost" => 0,
                    "monthly_price" => 0,
                ]);      
            }                          
            $targets->push($target);
        }

        $licences->whereNotIn('snowid', $targets->pluck('snowid'))
                    ->each(function($licence) use ($targets){
                        $target = array();
                        $target['id'] = $licence['id'];
                        $target['client_id'] = $licence['client_id'];
                        $target['name'] = $licence['name'];
                        $target['snowid'] = $licence['snowid'];
                        $target['count'] = 0;
                        $targets->push($target);
                    });
        
        $query = $targets->map(function($target){
            return (object)$target;
        })->toArray();

        $response = $haloService->haloPost('SoftwareLicence', $query);
        
        if($response != null && ($response->ok() || $response->created())){
            $scbillables->each(function($scbillable){
                $scbillable->update(['sent_to_halo' => 'success']);
            });
            Event::log("scbillables", "info" , [
                'message' => __('SCBillables pushed successfull'),
                'SCTenantID' => $this->sctenant->id,
                'query' => $query,
            ]);
            return;
        }

    }

    public function failed(Throwable $exception): void
    {                 
        Event::log("scbillables", "error" , [
            'message' => __('SCBillable sent to halo failed - no more retry'),
            'SCTenantID' => $this->sctenant->id,
            'year' => $this->year,
            'month' => $this->month,
        ]);    
    }

    public function uniqueId(): string
    {
        return $this->sctenant->id;
    }
}

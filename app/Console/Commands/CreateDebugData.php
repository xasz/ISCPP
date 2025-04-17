<?php

namespace App\Console\Commands;

use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Models\User;
use Database\Factories\SCTenantFactory;
use Exception;
use Illuminate\Console\Command;

class CreateDebugData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-debug-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Dummy user');
        try{
            $user = User::factory()->create([
                'name' => 'Dummy User',
                'email' => 'admin@local.local',
                'password' => bcrypt('admin'),
            ]);
        }catch( Exception $e){
            $this->warn('Dummy user could not be created');
        }

        $this->info('Creating SCTenants data');
        SCTenant::factory()
            ->count(100)
            ->has(SCAlert::factory()->count(rand(0,50)))
            ->create();    
        $this->info('SCTenants data created');
    }
}

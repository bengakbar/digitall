<?php

namespace App\Jobs;

use App\Http\Controllers\Sirangga\Admin\DBRController;
use App\Http\Controllers\Sirangga\Admin\ImportSaktiController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IngatkanUnitDBR implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tarikdata = new DBRController();
        $tarikdata = $tarikdata->aksikirimperingatankeunit();
    }
}

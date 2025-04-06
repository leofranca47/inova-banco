<?php

namespace App\Jobs;

use App\Adapter\Contracts\NotifyRetailerAdapterInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyRetailerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tries = 3;

    public function handle(NotifyRetailerAdapterInterface $notifyRetailerAdapter): void
    {
        $notifyRetailerAdapter->notify();
    }
}

<?php

namespace App\Adapter;

use App\Adapter\Contracts\NotifyRetailerAdapterInterface;
use Illuminate\Support\Facades\Http;

class NotifyRetailerAdapter implements NotifyRetailerAdapterInterface
{
    public function notify(): void
    {
        try {
            $response = Http::post('https://util.devi.tools/api/v1/notify');
        } catch (\Exception $e) {
            //notifcar o erro no sentry ou bugsnag
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}

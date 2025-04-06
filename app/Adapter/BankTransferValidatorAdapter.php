<?php

namespace App\Adapter;

use App\Adapter\Contracts\BankTranferValidatorAdapterInterface;
use Illuminate\Support\Facades\Http;

class BankTransferValidatorAdapter implements BankTranferValidatorAdapterInterface
{
    public function verifyTransfer(): array
    {
        try {
            $response = Http::get('https://util.devi.tools/api/v2/authorize')
                ->json();

            if ($response['data']['authorization'] === false) {
                return [
                    'success' => false,
                    'message' => 'Falha ao autorizar a transferência!',
                ];
            }

            return [
                'success' => true,
                'message' => 'OK',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

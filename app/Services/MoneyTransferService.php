<?php

declare(strict_types=1);

namespace App\Services;

use App\Adapter\Contracts\BankTranferValidatorAdapterInterface;
use App\Models\User;
use App\Services\DTOs\TransferDto;

class MoneyTransferService
{

    public function __construct(
        private readonly BankTranferValidatorAdapterInterface $bankTranferValidator
    )
    {
    }

    public function transfer(TransferDto $data): array
    {
        $userSend = User::find($data->senderId);

        if ($userSend->user_type_id === User::RETAILER) {
            return [
                'success' => false,
                'message' => 'Lojistas não podem transferir dinheiro!',
            ];
        }

        if ($userSend->balance < $data->value) {
            return [
                'success' => false,
                'message' => 'Usuário sem saldo suficiente para efetuar a transferência!',
            ];
        }

        $userReceive = User::find($data->receiverId);

        $userSend->balance -= $data->value;
        $userReceive->balance += $data->value;

        $transferValidator = $this->bankTranferValidator->verifyTransfer();

        if (!$transferValidator['success']) {
            return $transferValidator;
        }

        $userSend->save();
        $userReceive->save();

        return [
            'success' => true,
            'message' => 'Transferência realizada com sucesso!',
        ];
    }

}

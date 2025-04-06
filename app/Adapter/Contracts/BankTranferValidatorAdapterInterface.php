<?php

namespace App\Adapter\Contracts;

interface BankTranferValidatorAdapterInterface
{
    public function verifyTransfer(): array;
}

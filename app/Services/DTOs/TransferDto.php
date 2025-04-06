<?php

namespace App\Services\DTOs;

class TransferDto
{
    public function __construct(
        public float $value,
        public int $receiverId,
        public int $senderId
    )
    {
    }
}

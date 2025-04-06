<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
        UserType::updateOrCreate(
            [
                'id' => 1,
            ],
            [
                'type' => 'usuario',
            ]
        );

        UserType::updateOrCreate(
            [
                'id' => 2,
            ],
            [
                'type' => 'lojista',
            ]
        );
    }
}

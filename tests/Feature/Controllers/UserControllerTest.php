<?php

namespace Tests\Feature\Controllers;

use App\Adapter\Contracts\BankTranferValidatorAdapterInterface;
use App\Models\User;
use App\Services\DTOs\TransferDto;
use App\Services\MoneyTransferService;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testDeveriaCadastrarUsuario(): void
    {
        $response = $this->post('/api/user', [
            'name' => 'Teste',
            'email' => 'Wl6o9@example.com',
            'password' => '123456',
            'user_type_id' => User::USER,
            'balance' => 50.2,
            'cpf_cnpj' => '15580042035',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Teste',
                'email' => 'Wl6o9@example.com',
                'user_type_id' => User::USER,
                'balance' => 50.2,
                'cpf_cnpj' => '15580042035',
            ]);
    }

    public function testDeveriaBuscarUsuario(): void
    {
        $user = User::factory()->create([
            'cpf_cnpj' => '50221161031',
            'user_type_id' => User::USER,
            'balance' => 90,
        ]);

        $response = $this->get("/api/user/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type_id' => $user->user_type_id,
                'balance' => $user->balance,
                'cpf_cnpj' => $user->cpf_cnpj,
            ]);
    }

    public function testDeveriaTranferirDinheiro(): void
    {
        $this->instance(
            BankTranferValidatorAdapterInterface::class,
            \Mockery::mock(BankTranferValidatorAdapterInterface::class, function ($mock) {
                $mock->shouldReceive('verifyTransfer')
                    ->once()
                    ->andReturn([
                        'success' => true,
                        'message' => 'OK',
                    ]);
            })
        );

        $user = User::factory()->create([
            'cpf_cnpj' => '24327611034',
            'user_type_id' => User::USER,
            'balance' => 100,
        ]);

        $retailerUser = User::factory()->create([
            'cpf_cnpj' => '31213351000104',
            'user_type_id' => User::RETAILER,
            'balance' => 0,
        ]);

        $response = $this->post('/api/user/transfer', [
            'value' => 10,
            'payer' => $user->id,
            'payee' => $retailerUser->id,
        ]);

        $response->assertStatus(201)
            ->assertJson(
            [
                'message' => 'Transferência realizada com sucesso!',
            ]
        );

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'balance' => 90,
        ]);

        $this->assertDatabaseHas(User::class, [
            'id' => $retailerUser->id,
            'balance' => 10,
        ]);
    }
}

<?php

namespace Tests\Feature\Services;

use App\Adapter\Contracts\BankTranferValidatorAdapterInterface;
use App\Jobs\NotifyRetailerJob;
use App\Models\User;
use App\Services\DTOs\TransferDto;
use App\Services\MoneyTransferService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MoneyTransferServiceTest extends TestCase
{
    private const USUARIO = 1;
    private const LOJISTA = 2;

    public function testUsuariosDeveriamTransferirDinheiroParaTodos(): void
    {
        $this->instance(
            BankTranferValidatorAdapterInterface::class,
            \Mockery::mock(BankTranferValidatorAdapterInterface::class, function ($mock) {
                $mock->shouldReceive('verifyTransfer')
                    ->twice()
                    ->andReturn([
                        'success' => true,
                        'message' => 'OK',
                    ]);
            })
        );

        $user = User::factory()->create([
            'cpf_cnpj' => '36372760037',
            'user_type_id' => self::USUARIO,
            'balance' => 100,
        ]);

        $userTwo = User::factory()->create([
            'cpf_cnpj' => '76740065063',
            'user_type_id' => self::USUARIO,
            'balance' => 90,
        ]);

        $retailerUser = User::factory()->create([
            'cpf_cnpj' => '39084289000100',
            'user_type_id' => self::LOJISTA,
            'balance' => 0,
        ]);

        $response = app(MoneyTransferService::class)->transfer(new TransferDto(
            value: 10,
            receiverId: $userTwo->id,
            senderId: $user->id
        ));

        $this->assertIsArray($response);
        $this->assertEquals(
            [
                'success' => true,
                'message' => 'Transferência realizada com sucesso!',
            ],
            $response
        );

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'balance' => 90,
        ]);

        $this->assertDatabaseHas(User::class, [
            'id' => $userTwo->id,
            'balance' => 100,
        ]);

        $response = app(MoneyTransferService::class)->transfer(new TransferDto(
            value: 10,
            receiverId: $retailerUser->id,
            senderId: $user->id
        ));

        $this->assertIsArray($response);
        $this->assertEquals(
            [
                'success' => true,
                'message' => 'Transferência realizada com sucesso!',
            ],
            $response
        );

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'balance' => 80,
        ]);

        $this->assertDatabaseHas(User::class, [
            'id' => $retailerUser->id,
            'balance' => 10,
        ]);
    }

    public function testLojistasDeveriamApenasReceberDinheiro(): void
    {
        $user = User::factory()->create([
            'cpf_cnpj' => '63078148025',
            'user_type_id' => self::USUARIO,
            'balance' => 90,
        ]);

        $retailerUser = User::factory()->create([
            'cpf_cnpj' => '86423324000158',
            'user_type_id' => self::LOJISTA,
            'balance' => 0,
        ]);

        $response = app(MoneyTransferService::class)->transfer(new TransferDto(
            value: 10,
            receiverId: $user->id,
            senderId: $retailerUser->id
        ));

        $this->assertIsArray($response);
        $this->assertEquals(
            [
                'success' => false,
                'message' => 'Lojistas não podem transferir dinheiro!',
            ],
            $response
        );
    }

    public function testDeveriaValidarSeUsuariosTemSaldoAntesDeTransferir(): void
    {
        $user = User::factory()->create([
            'cpf_cnpj' => '57674126005',
            'user_type_id' => self::USUARIO,
            'balance' => 90,
        ]);

        $retailerUser = User::factory()->create([
            'cpf_cnpj' => '13461691000172',
            'user_type_id' => self::LOJISTA,
            'balance' => 0,
        ]);

        $response = app(MoneyTransferService::class)->transfer(new TransferDto(
            value: 100,
            receiverId: $retailerUser->id,
            senderId: $user->id
        ));

        $this->assertIsArray($response);
        $this->assertEquals(
            [
                'success' => false,
                'message' => 'Usuário sem saldo suficiente para efetuar a transferência!',
            ],
            $response
        );
    }

    public function testCasoUsuarioLojistaDeveriaReceberNotificacao(): void
    {
        Queue::fake();
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
            'cpf_cnpj' => '71192018087',
            'user_type_id' => self::USUARIO,
            'balance' => 90,
        ]);

        $retailerUser = User::factory()->create([
            'cpf_cnpj' => '44007216000154',
            'user_type_id' => self::LOJISTA,
            'balance' => 0,
        ]);

        $response = app(MoneyTransferService::class)->transfer(new TransferDto(
            value: 10,
            receiverId: $retailerUser->id,
            senderId: $user->id
        ));

        $this->assertIsArray($response);
        $this->assertEquals(
            [
                'success' => true,
                'message' => 'Transferência realizada com sucesso!',
            ],
            $response
        );

        Queue::assertPushed(NotifyRetailerJob::class);
    }
}

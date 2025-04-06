<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use App\Services\DTOs\TransferDto;
use App\Services\MoneyTransferService;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function create(UserCreateRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'user_type_id' => $user->user_type_id,
            'balance' => $user->balance,
            'cpf_cnpj' => $user->cpf_cnpj,
        ], Response::HTTP_CREATED);
    }

    public function show(int $id)
    {
        $user = User::find($id);

        if (empty($user)) {
            return response()->json([], Response::HTTP_OK);
        }

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'user_type_id' => $user->user_type_id,
            'balance' => $user->balance,
            'cpf_cnpj' => $user->cpf_cnpj,
        ], Response::HTTP_OK);
    }

    public function transfer(TransferRequest $request, MoneyTransferService $moneyTransferService)
    {
        $reponse = $moneyTransferService->transfer(new TransferDto(
            value: $request->value,
            receiverId: $request->payee,
            senderId: $request->payer
        ));

        if (!$reponse['success']) {
            return response()->json([
                'message' => $reponse['message'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => $reponse['message'],
        ], Response::HTTP_OK);
    }
}

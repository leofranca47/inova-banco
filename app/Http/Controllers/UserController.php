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
    /**
     * @OA\Post (
     *     path="/user",
     *     tags={"Usuários"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="password",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="user_type_id",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="balance",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="cpf_cnpj",
     *                          type="string"
     *                      )
     *                 ),
     *                 example={
     *                     "name":"leofranca",
     *                     "email":"leo@email.com",
     *                     "password":"123456"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="leofranca"),
     *              @OA\Property(property="email", type="string", example="leo@email.com"),
     *              @OA\Property(property="user_type_id", type="integer", example=1),
     *              @OA\Property(property="balance", type="string", example="100.00"),
     *              @OA\Property(property="cpf_cnpj", type="string", example="12345678901")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="invalid",
     *          @OA\JsonContent(
     *              @OA\Property(property="msg", type="string", example="fail"),
     *          )
     *      )
     * )
     */
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

    /**
     * @OA\Get (
     *     path="/user/{id}",
     *     tags={"Usuários"},
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              @OA\Property(property="id", type="number", example=1),
     *              @OA\Property(property="name", type="string", example="Leo"),
     *              @OA\Property(property="email", type="string", example="leo@email.com"),
     *              @OA\Property(property="user_type_id", type="integer", example=58),
     *              @OA\Property(property="balance", type="string", example="10.2"),
     *              @OA\Property(property="cpf_cnpj", type="string", example="57649769000152")
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $user = User::find($id);

        if (empty($user)) {
            return response()->json([], Response::HTTP_OK);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_type_id' => $user->user_type_id,
            'balance' => $user->balance,
            'cpf_cnpj' => $user->cpf_cnpj,
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post (
     *     path="/user/transfer",
     *     tags={"Usuários"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="value",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="payer",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="payee",
     *                          type="integer"
     *                      )
     *                 ),
     *                 example={
     *                     "value":"100.00",
     *                     "payer":2,
     *                     "payee":1
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Transferência realizada com sucesso!")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="invalid",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="falhou"),
     *          )
     *      )
     * )
     */
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
        ], Response::HTTP_CREATED);
    }
}

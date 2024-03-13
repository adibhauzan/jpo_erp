<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Token\TokenRepositoryInterface;
use App\Utils\GenerateRandomToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

/**
 * @OA\Tag(
 *     name="Token",
 *     description="Endpoints for token"
 * )
 */
class TokenController extends Controller
{
    private $tokenRepository;

    /**
     * Create a new AuthController instance.
     *
     * @param TokenRepositoryInterface $tokenRepository
     * @return void
     */
    public function __construct(TokenRepositoryInterface $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/token/c/{jumlah}",
     *     summary="Create a new token",
     *     operationId="createToken",
     *     tags={"Token"},
     *     @OA\Parameter(
     *         name="jumlah",
     *         in="path",
     *         required=true,
     *         description="Input total token to create",
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             default=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="adibkeren", description="Token to generate other tokens")
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Success create new Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="Message", type="string", example="success create token"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request, $jumlah)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $tokens = [];

            for ($i = 0; $i < $jumlah; $i++) {
                if ($jumlah === null) {
                    return response()->json(['error' => $validator->errors()], 422);
                }
                $timestamp = Carbon::now()->timestamp;
                $randomString = Str::random(10);

                $tokenData = [
                    'token' => GenerateRandomToken::generateRandomToken($request->input('token') . $timestamp . $randomString),
                ];

                $tokens[] = $this->tokenRepository->create($tokenData);
            }

            return response()->json(['Message' => 'success create token', 'data' => $tokens], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create token. ' . $e->getMessage()], 422);
        }
    }

    /**
     * 
     * Get All Tokens
     * 
     * @OA\Get(
     *     path="/api/auth/tokens/",
     *     summary="Get all Tokens",
     *     operationId="indexTokens",
     *     tags={"Token"},
     *     @OA\Response(
     *         response=200,
     *         description= "success fetch Tokens",
     *         @OA\JsonContent(
     *             @OA\Property(property="Tokens", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to fetch Tokens.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $tokens = $this->tokenRepository->findAll();

            return response()->json(['Message' => 'success fetch Tokens', 'data' => $tokens], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Tokens. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Token by id.
     * 
     * @OA\Get(
     *     path="/api/auth/token/{id}",
     *     summary="Get token by id.",
     *     operationId="showToken",
     *     tags={"Token"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the token",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $tokenId UUID of the token
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $tokenId)
    {
        try {
            $token = $this->tokenRepository->find($tokenId);
            return response()->json(['message' => 'Token retrieved successfully', 'data' => $token], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Token. ' . $e->getMessage()], 422);
        }
    }

      /**
     * Delete Token by ID.
     *
     * @OA\Delete(
     *     path="/api/auth/token/d/{id}",
     *     summary="Delete token by ID",
     *     operationId="deleteToken",
     *     tags={"Token"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the token to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to delete Token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $tokenId UUID of the token to delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $tokenId)
    {
        try {
            $token = $this->tokenRepository->delete($tokenId);
            return response()->json(['message' => 'Token deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete Token. ' . $e->getMessage()], 422);
        }
    }
}
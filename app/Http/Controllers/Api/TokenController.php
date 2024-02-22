<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Token\TokenRepositoryInterface;
use App\Utils\GenerateRandomToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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
                if($jumlah === null){
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

    public function index(Request $request)
    {
        try {
            $tokens = $this->tokenRepository->findAll();

            return response()->json(['Message' => 'success fetch Tokens', 'data' => $tokens], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Tokens. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $tokenId)
    {
        try {
            $token = $this->tokenRepository->find($tokenId);
            return response()->json(['message' => 'Token retrieved successfully', 'data' => $token], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Token. ' . $e->getMessage()], 422);
        }
    }

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

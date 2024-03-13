<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Bank\BankRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Bank",
 *     description="Endpoints for bank"
 * )
 */
class BankControler extends Controller
{
    private $bankRepository;

    /**
     * Create a new BankController instance.
     *
     * @param BankRepositoryInterface $bankRepository
     * @return void
     */
    public function __construct(BankRepositoryInterface $bankRepository)
    {
        $this->bankRepository = $bankRepository;
    }


    /**
     * @OA\Post(
     *     path="/api/auth/bank",
     *     summary="Create a new bank",
     *     operationId="createBank",
     *     tags={"Bank"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name_rek", type="string", default="bank1"),
     *             @OA\Property(property="no_rek", type="string", default="1234567890"),
     *             @OA\Property(property="bank", type="string", default="BCA"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description= "success create new Bank",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object"),
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * 
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_rek' => 'required|string|min:3',
            'no_rek' => 'required|string|min:10|max:17|unique:banks,no_rek|regex:/^([0-9\s\-\+\(\)]*)$/',
            'bank' => 'required|string|min:3',
            'status' => 'nullable|in:used,not'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $bankData = [
                'name_rek' => $request->input('name_rek'),
                'no_rek' => $request->input('no_rek'),
                'bank' => $request->input('bank'),
                'status' => $request->input('status', 'used'),
            ];

            $bank = $this->bankRepository->create($bankData);
            return  response()->json(['Message' => 'success create new Bank', 'data' => $bank]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create Bank. ' . $e->getMessage()], 422);
        }
    }

    /**
     * 
     * Get All Banks
     * 
     * @OA\Get(
     *     path="/api/auth/banks/",
     *     summary="Get all banks",
     *     operationId="indexBank",
     *     tags={"Bank"},
     *     @OA\Response(
     *         response=200,
     *         description= "success fetch banks",
     *         @OA\JsonContent(
     *             @OA\Property(property="banks", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to fetch banks.",
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
            $banks = $this->bankRepository->findAll();

            return response()->json(['Message' => 'success fetch Banks', 'data' => $banks], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Banks. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get bank by id.
     * 
     * @OA\Get(
     *     path="/api/auth/bank/{id}",
     *     summary="Get bank by id.",
     *     operationId="showBank",
     *     tags={"Bank"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the bank",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bank details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string"),
     *             @OA\Property(property="name_rek", type="string"),
     *             @OA\Property(property="no_rek", type="string"),
     *             @OA\Property(property="bank", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch bank",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $bankId UUID of the bank
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $bankId)
    {
        try {
            $bank = $this->bankRepository->find($bankId);
            return response()->json(['message' => 'Bank retrieved successfully', 'data' => $bank], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Bank. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Update Bank by ID.
     *
     * @OA\Put(
     *     path="/api/auth/bank/u/{id}",
     *     summary="Update bank by ID",
     *     operationId="updateBank",
     *     tags={"Bank"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the bank to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *               *             @OA\Property(property="name_rek", type="string", default="bank updated"),
     *             @OA\Property(property="no_rek", type="string", default="1234567891"),
     *             @OA\Property(property="bank", type="string", default="BCA"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bank updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bank updated successfully"),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="string"),
     *             @OA\Property(property="name_rek", type="string"),
     *             @OA\Property(property="no_rek", type="string"),
     *             @OA\Property(property="bank", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or failed to update bank",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $bankId UUID of the bank to update
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $bankId)
    {
        $validator = Validator::make($request->all(), [
            'name_rek' => 'required|string|min:3',
            'no_rek' => 'required|string|min:10|max:17|unique:banks,no_rek|regex:/^([0-9\s\-\+\(\)]*)$/',
            'bank' => 'required|string|min:3',
            'status' => 'nullable|in:used,not'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['name_rek', 'no_rek', 'bank', 'status']);

            $bank = $this->bankRepository->update($bankId, $data);

            return response()->json(['message' => 'Bank updated successfully', 'data' => $bank], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Bank. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Delete Bank by ID.
     *
     * @OA\Delete(
     *     path="/api/auth/bank/d/{id}",
     *     summary="Delete bank by ID",
     *     operationId="deleteBank",
     *     tags={"Bank"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the bank to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bank deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bank deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to delete bank",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $bankId UUID of the bank to delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $bankId)
    {
        try {
            $bank = $this->bankRepository->delete($bankId);
            return response()->json(['message' => 'Bank deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete Bank. ' . $e->getMessage()], 422);
        }
    }
}
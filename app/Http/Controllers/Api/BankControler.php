<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Bank\BankRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        } catch (\Exception $e){
            return response()->json(['error' => 'Failed to create Bank. ' . $e->getMessage()], 422);
        }
    }

    public function index(Request $request)
    {
        try {
            $banks = $this->bankRepository->findAll();

            return response()->json(['Message' => 'success fetch Banks', 'data' => $banks], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Banks. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $bankId)
    {
        try {
            $bank = $this->bankRepository->find($bankId);
            return response()->json(['message' => 'Bank retrieved successfully', 'data' => $bank], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Bank. ' . $e->getMessage()], 422);
        }
    }

    public function update(Request $request, string $bankId)
    {
        $validator = Validator::make($request->all(), [
            'name_rek' => 'required|string|min:3',
            'no_rek' => 'required|string|min:10|max:17|unique:banks,no_rek|regex:/^([0-9\s\-\+\(\)]*)$/',
            'bank' => 'required|string|min:3',
            'status' => 'required|in:used,not'
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

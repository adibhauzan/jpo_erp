<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Bill\BillRepositoryInterface;
use Illuminate\Support\Facades\Validator;

class BillController extends Controller
{
    private $billRepository;


    /**
     * Create a new billController instance.
     *
     * @param BillRepositoryInterface $billRepository
     * @return void
     */
    public function __construct(BillRepositoryInterface $billRepository)
    {
        $this->billRepository = $billRepository;
    }

    public function index(Request $request)
    {
        try {
            $bills = $this->billRepository->findAll();

            return response()->json(['code' => 200, 'Message' => 'success fetch Bills', 'data' => $bills], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Bills. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $billId)
    {
        try {
            $bill = $this->billRepository->find($billId);
            return response()->json(['message' => 'Bill retrieved successfully', 'data' => $bill], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Bill. ' . $e->getMessage()], 422);
        }
    }

    public function pay(Request $request, string $billId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'paid_price' => 'nullable|integer',
                'bank_id' => 'nullable|exists:banks,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $paid_price = $request->input('paid_price', 0);
            $bank_id = $request->input('bank_id', null);

            $this->billRepository->pay($billId,$paid_price,$bank_id);

            return response()->json(["code" => 200, "Message" => "Success Pay Bill Price"], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
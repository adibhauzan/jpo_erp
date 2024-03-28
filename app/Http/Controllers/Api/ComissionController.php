<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Commision\CommisionRepositoryInterface;

class ComissionController extends Controller
{
    private $commisionRepository;


    /**
     * Create a new commisionController instance.
     *
     * @param CommisionRepositoryInterface $commisionRepository
     * @return void
     */
    public function __construct(CommisionRepositoryInterface $commisionRepository)
    {
        $this->commisionRepository = $commisionRepository;
    }

    public function index(Request $request)
    {
        try {
            $commisions = $this->commisionRepository->findAll();

            return response()->json(['code' => 200, 'Message' => 'success fetch commisions', 'data' => $commisions], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch commisions. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $commisionId)
    {
        try {
            $commision = $this->commisionRepository->find($commisionId);
            return response()->json(['message' => 'commision retrieved successfully', 'data' => $commision], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve commision. ' . $e->getMessage()], 422);
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

            $this->commisionRepository->pay($billId, $paid_price, $bank_id);

            return response()->json(["code" => 200, "Message" => "Success Pay Bill Price"], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}

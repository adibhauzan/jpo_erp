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

            $bill = $this->billRepository->find($billId);

            $validator = Validator::make($request->all(), [
                'paid_price' => 'required|integer',
                'nama_bank' => 'required',
                'nama_rekening' => 'required',
                'no_rekening' => 'required|string|min:10|max:17|regex:/^([0-9\s\-\+\(\)]*)$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $paid_price = $request->input('paid_price') ?? $bill->paid_price;
            $nama_bank = $request->input('nama_bank') ??  $bill->nama_bank;
            $nama_rekening = $request->input('nama_rekening') ?? $bill->nama_rekening;
            $no_rekening = $request->input('no_rekening') ?? $bill->no_rekening;


            $this->billRepository->pay($billId, $paid_price, $nama_bank, $nama_rekening, $no_rekening);

            return response()->json(["code" => 200, "Message" => "Success Pay Bill Price"], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}

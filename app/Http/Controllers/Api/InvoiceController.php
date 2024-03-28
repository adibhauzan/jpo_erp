<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Invoice\InvoiceRepositoryInterface;

class InvoiceController extends Controller
{
    private $invoiceRepository;


    /**
     * Create a new billController instance.
     *
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @return void
     */
    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function index(Request $request)
    {
        try {
            $invoices = $this->invoiceRepository->findAll();

            return response()->json(['code' => 200, 'Message' => 'success fetch Invoices', 'data' => $invoices], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Invoices. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $invoiceId)
    {
        try {
            $bill = $this->invoiceRepository->find($invoiceId);
            return response()->json(['message' => 'Bill retrieved successfully', 'data' => $bill], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Bill. ' . $e->getMessage()], 422);
        }
    }

    public function pay(Request $request, string $invoiceId)
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

            $this->invoiceRepository->pay($invoiceId, $paid_price, $bank_id);

            return response()->json(["code" => 200, "Message" => "Success Pay Bill Price"], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}

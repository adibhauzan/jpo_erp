<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

    public function show(string $billId)
    {
        try {
            $bill = $this->invoiceRepository->find($billId);
            return response()->json(['message' => 'Bill retrieved successfully', 'data' => $bill], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Bill. ' . $e->getMessage()], 422);
        }
    }
}

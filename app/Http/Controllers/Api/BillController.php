<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Bill\BillRepositoryInterface;

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
}

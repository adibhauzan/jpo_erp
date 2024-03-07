<?php

namespace App\Http\Controllers\Api\Inventory\Transfer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Inventory\Transfer\In\TransferInRepositoryInterface;

class TransferInController extends Controller
{
    private $transferInRepository;


    /**
     * Create a new stockController instance.
     *
     * @param TransferInRepositoryInterface $stockRepository
     * @return void
     */
    public function __construct(TransferInRepositoryInterface $transferInRepository)
    {
        $this->transferInRepository = $transferInRepository;
    }

    public function index()
    {
        try {
            $transferIn = $this->transferInRepository->findAll();
            return response()->json(['message' => ' Transfer In retrieved successfully', 'data' => $transferIn], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed retrieved Stock', 'data' => $e->getMessage()], 500);
        }
    }
}

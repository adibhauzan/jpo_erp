<?php

namespace App\Http\Controllers\Api\Inventory\Stock;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Inventory\Stock\StockRepositoryInterface;

class StockController extends Controller
{
    private $stockRepository;


    /**
     * Create a new stockController instance.
     *
     * @param StockRepositoryInterface $stockRepository
     * @return void
     */
    public function __construct(StockRepositoryInterface $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    public function index(){
        try {
            $stock = $this->stockRepository->findAll();
            return response()->json(['message' => 'Stock fetch successfully', 'data' => $stock], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed fetch Stock', 'data' => $e->getMessage()], 500);

        }
    }

    public function show(string $stockId)
    {
        try {
            $transferIn = $this->stockRepository->find($stockId);
            return response()->json(['message' => 'Stock retrieved successfully', 'data' => $transferIn], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Stock. ' . $e->getMessage()], 422);
        }
    }
}
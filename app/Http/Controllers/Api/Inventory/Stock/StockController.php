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
            return response()->json(['message' => 'Stock retrieved successfully', 'data' => $stock], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed retrieved Stock', 'data' => $e->getMessage()], 500);

        }
    }
}
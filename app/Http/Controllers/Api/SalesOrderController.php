<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Validator;
use App\Repositories\SalesOrder\SalesOrderRepositoryInterface;

class SalesOrderController extends Controller
{
    private $salesOrderRepository;


    /**
     * Create a new salesOrderController instance.
     *
     * @param SalesOrderRepositoryInterface $salesOrderRepository
     * @return void
     */
    public function __construct(SalesOrderRepositoryInterface $salesOrderRepository)
    {
        $this->salesOrderRepository = $salesOrderRepository;
    }

    public function getSku(string $sku)
    {
        try {
            $skuData = $this->salesOrderRepository->getBySku($sku);

            if ($skuData->isEmpty()) {
                return response()->json(['message' => 'SKU not found'], 404);
            }

            return response()->json(['message' => 'Success get SKU', 'data' => $skuData], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to get SKU', 'data' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sku' => 'required|string|exists:purchase_orders,sku',
                'broker_fee' => 'required|integer',
                'date' => 'required|date',
                'stock_rev' => 'nullable|integer',
                'stock_rib_rev' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $currentDate = now();

            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');

            $lastSequence = SalesOrder::whereDate('created_at', $currentDate)->count() + 1;

            $no_so = 'INV/out/' . $year . '/' . $month . '/' . $day . '/' . $lastSequence;
            $data = [
                'sku' => $request->input('sku'),
                'broker_fee' => $request->input('broker_fee'),
                'stock_rev' => $request->input('stock_rev'),
                'stock_rib_rev' => $request->input('stock_rib_rev'),
                'date' => $request->input('date'),
                'no_so' => $no_so,
            ];

            $salesData = $this->salesOrderRepository->create($data);

            return response()->json(['message' => 'success create SO', 'data' => $salesData], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'failed create SO ' . $e->getMessage()], 422);
        }
    }

    public function index()
    {
        try {
            $salesOrders = $this->salesOrderRepository->findAll();
            return response()->json(['message' => 'success fetch SO', 'data' => $salesOrders], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'failed fetch SO' . $e->getMessage()], 422);
        }
    }

    public function show(string $soId)
    {
        try {
            $salesOrder = $this->salesOrderRepository->find($soId);

            return response()->json(['message' => 'success retrive SO', 'data' => $salesOrder], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'failed retrive SO' . $e->getMessage()], 422);
        }
    }

    public function update(string $soId, Request $request)
    {

        try {
            $so = $this->salesOrderRepository->find($soId);
            $validator = Validator::make($request->all(), [
                'stock_rev' => 'nullable|integer',
                'broker_fee' => 'nullable|integer',
                'price' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }


            $data = [
                'stock_rev' => $request->input('stock_rev') ?? $so->stock_rev,
                'broker_fee' => $request->input('broker_fee') ?? $so->broker_fee,
                'price' => $request->input('price') ?? $so->price,
            ];

            $updatedSO = $this->salesOrderRepository->update($soId, $data);
            $soYangSudahDiUpdate = $this->salesOrderRepository->find($soId);



            return response()->json(["message" => 'success updated SO', "data" => $soYangSudahDiUpdate], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'failed update SO' . $e->getMessage()], 422);
        }
    }
}

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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sku' => 'required|string|exists:purchase_orders,sku',
                'contact_id' => 'required|string|exists:contacts,id',
                'warehouse_id' => 'required|string|exists:warehouses,id',
                'broker' => 'nullable|string|exists:contacts,id',
                'broker_fee' => 'nullable|integer',
                'date' => 'required|date',
                'stock_roll' => 'required|integer|min:0',
                'stock_kg' => 'required|integer|min:0',
                'stock_rib' => 'required|integer|min:0',
                'price' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $currentDate = now();
            $sequence = SalesOrder::whereDate('created_at', $currentDate)->count() + 1;
            $no_do = 'INV/out/' . $currentDate->format('Y/m/d') . '/' . $sequence; // Perbaikan 3: Nomor DO menggunakan timestamp
            $no_so = 'SO' . str_pad($sequence, 5, '0', STR_PAD_LEFT); // Perbaikan 4: Nomor SO menggunakan timestamp

            $data = [
                'sku' => $request->input('sku'),
                'contact_id' => $request->input('contact_id'),
                'warehouse_id' => $request->input('warehouse_id'),
                'broker' => $request->input('broker'),
                'broker_fee' => $request->input('broker_fee'),
                'date' => $request->input('date'),
                'stock_roll' => $request->input('stock_roll'),
                'stock_kg' => $request->input('stock_kg'),
                'stock_rib' => $request->input('stock_rib'),
                'price' => $request->input('price'),
                'no_do' => $no_do,
                'no_so' => $no_so,
            ];

            $salesData = $this->salesOrderRepository->create($data);

            return response()->json(['message' => 'success create SO', 'data' => $salesData], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'failed create SO ' . $e->getMessage()], 422);
        }
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



    public function index()
    {
        try {
            $salesOrders = $this->salesOrderRepository->findAll();
            return response()->json(['message' => 'success fetch SO', 'data' => $salesOrders], 200);
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

    public function findAllSku()
    {
        try {
            $sku = $this->salesOrderRepository->getAllSku();
            return response()->json(["message" => 'success find all SKU', "data" => $sku], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'failed find all' . $e->getMessage()], 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ValidationTokenUpdate;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Token\TokenRepositoryInterface;
use App\Repositories\Inventory\Stock\StockRepositoryInterface;
use App\Repositories\SalesOrder\SalesOrderRepositoryInterface;

class SalesOrderController extends Controller
{
    private $salesOrderRepository;
    private $stockRepository;
    private $tokenRepository;

    /**
     * Create a new SalesOrderController instance.
     *
     * @param SalesOrderRepositoryInterface $salesOrderRepository
     * @param StockRepositoryInterface $stockRepository
     * @param TokenRepositoryInterface $tokenRepository
     * @return void
     */
    public function __construct(
        SalesOrderRepositoryInterface $salesOrderRepository,
        StockRepositoryInterface $stockRepository,
        TokenRepositoryInterface $tokenRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->stockRepository = $stockRepository;
        $this->tokenRepository = $tokenRepository;
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sku' => 'required|string|exists:stocks,sku',
                'contact_id' => 'required|string|exists:contacts,id',
                'warehouse_id' => 'required|string|exists:warehouses,id',
                'broker' => 'nullable|string|exists:contacts,id',
                'broker_fee' => 'nullable|integer',
                'date' => 'required|date',
                'stock_roll' => 'required|numeric',
                'stock_kg' => 'required|numeric',
                'stock_rib' => 'required|numeric',
                'price' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $currentDate = now();
            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');
            $totalOrders = SalesOrder::count();
            $sequence = $totalOrders + 1;
            $no_do = 'INV/OUT/' . $year . '/' . $month . '/' . $day . '/' . $sequence;
            $no_so = 'SO' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

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

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $validationToken = ValidationTokenUpdate::where('update_key', $request->input('update_key'))
            ->where('status', 'not')
            ->first();

        if (!$validationToken) {
            return response()->json(['error' => 'Update key not found or already used'], 400);
        }

        $validator = Validator::make($request->all(), [
            'update_key' => 'required',
            'broker_fee' => 'nullable|integer',
            'stock_roll' => 'nullable|numeric',
            'stock_kg' => 'nullable|numeric',
            'stock_rib' => 'nullable|numeric',
            'price' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'errors' => $validator->errors()], 400);
        }

        return DB::transaction(function () use ($id, $request, $user, $validationToken) {
            try {
                $salesOrder = SalesOrder::findOrFail($id);

                $oldStock = [
                    'stock_roll' => $salesOrder->stock_roll,
                    'stock_kg' => $salesOrder->stock_kg,
                    'stock_rib' => $salesOrder->stock_rib,
                ];

                $salesOrder->update($request->only('stock_roll', 'stock_kg', 'stock_rib'));

                $diffStock = [
                    'stock_roll' => $request->stock_roll - $oldStock['stock_roll'],
                    'stock_kg' => $request->stock_kg - $oldStock['stock_kg'],
                    'stock_rib' => $request->stock_rib - $oldStock['stock_rib'],
                ];

                $purchaseOrder = PurchaseOrder::where('sku', $salesOrder->sku)->firstOrFail();

                if (($purchaseOrder->stock_roll_rev - $diffStock['stock_roll'] < 0) ||
                    ($purchaseOrder->stock_kg_rev - $diffStock['stock_kg'] < 0) ||
                    ($purchaseOrder->stock_rib_rev - $diffStock['stock_rib'] < 0)
                ) {
                    return response()->json(['error' => 'Requested stock exceeds available purchase order capacity'], 400);
                }

                $purchaseOrder->stock_roll_rev -= $diffStock['stock_roll'];
                $purchaseOrder->stock_kg_rev -= $diffStock['stock_kg'];
                $purchaseOrder->stock_rib_rev -= $diffStock['stock_rib'];
                $purchaseOrder->save();

                $validationToken->update([
                    'status' => 'used',
                    'user_id' => $user->id,
                ]);

                return response()->json(['message' => 'Data updated successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to update the SO. ' . $e->getMessage()], 500);
            }
        });
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

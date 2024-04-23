<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Validator;
use App\Repositories\SalesOrder\SalesOrderRepositoryInterface;
use App\Repositories\Token\TokenRepositoryInterface;

class SalesOrderController extends Controller
{
    private $salesOrderRepository;
    private $tokenRepository;


    /**
     * Create a new salesOrderController instance.
     *
     * @param SalesOrderRepositoryInterface $salesOrderRepository
     * @param TokenRepositoryInterface $tokenRepository
     * @return void
     */
    public function __construct(SalesOrderRepositoryInterface $salesOrderRepository, TokenRepositoryInterface $tokenRepository)
    {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->tokenRepository = $tokenRepository;
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

            // Mengambil jumlah total entri dari tabel PurchaseOrder
            $totalOrders = SalesOrder::count();

            // Nomor urutan adalah jumlah total entri ditambah 1
            $sequence = $totalOrders + 1;
            $no_do = 'INV/OUT/' . $year . '/' . $month . '/' . $day . '/' . $sequence;
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
                'broker_fee' => 'nullable',
                'harga_jual' => 'nullable',
                'stock_roll' => 'nullable',
                'stock_kg' => 'nullable',
                'stock_rib' => 'nullable',
                'token' => 'required|exist:tokens,name'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $token = $this->tokenRepository->find($request->input('token'));

            $data = [
                'stock_rev' => $request->input('stock_rev') ?? $so->stock_rev,
                'broker_fee' => $request->input('broker_fee') ?? $so->broker_fee,
                'price' => $request->input('price') ?? $so->price,
            ];

            $updatedSO = $this->salesOrderRepository->update($soId, $data, $token);
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
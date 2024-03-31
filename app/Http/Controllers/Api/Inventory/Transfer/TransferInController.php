<?php

namespace App\Http\Controllers\Api\Inventory\Transfer;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
            return response()->json(['message' => ' Transfer In Fetch successfully', 'data' => $transferIn], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed retrieved Stock', 'data' => $e->getMessage()], 500);
        }
    }

    public function show(string $inId)
    {
        try {
            $transferIn = $this->transferInRepository->find($inId);
            return response()->json(['message' => 'Transfer In retrieved successfully', 'data' => $transferIn], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Transfer In. ' . $e->getMessage()], 422);
        }
    }

    public function update(Request $request, string $inId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_barang' => 'nullable|string',
                'grade' => 'nullable|string',
                'sku' => 'nullable|string|unique:purchase_orders,sku',
                'description' => 'nullable|string',
                'ketebalan' => 'nullable|integer',
                'setting' => 'nullable|integer',
                'gramasi' => 'nullable|integer',
                'stock_roll' => 'nullable|numeric',
                'stock_kg' => 'nullable|numeric',
                'stock_rib' => 'nullable|numeric',
                'attachment_image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $data = $request->only([
                'nama_barang', 'grade', 'sku', 'description',
                'ketebalan', 'setting', 'gramasi', 'stock', 'stock_rib'
            ]);

            if ($request->hasFile('attachment_image')) {
                $originalImageName = $request->file('attachment_image')->getClientOriginalName();
                $path = $request->file('attachment_image')->storeAs('public/images/PurchaseOrder', $originalImageName);
                $data['attachment_image'] = $originalImageName;
            }

            $transferIn = $this->transferInRepository->update($inId, $data);

            $transferInUpdated = $this->transferInRepository->find($inId);

            return response()->json(['message' => 'Transfer In updated successfully', 'data' => $transferInUpdated], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Transfer In. ' . $e->getMessage()], 422);
        }
    }

    public function receive(Request $request, string $inId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'stock_roll_rev' => 'nullable|numeric',
                'stock_kg_rev' => 'nullable|numeric',
                'stock_rib_rev' => 'nullable|numeric',
                'date_received' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $quantityStockRollReceived = $request->input('stock_roll_rev', 0);
            $quantityStockKgReceived = $request->input('stock_kg_rev', 0);
            $quantityRibReceived = $request->input('stock_rib_rev', 0);
            $date_received = $request->input('date_received', Carbon::now()->toDateString());

            $transferin = $this->transferInRepository->receive($inId, $quantityStockRollReceived, $quantityStockKgReceived, $quantityRibReceived, $date_received);

            return response()->json(['message' => 'Transfer in received successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'contact_id' => 'required|exists:contacts,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'date' => 'required|date',
                'nama_barang' => 'required|string',
                'grade' => 'required|string',
                'sku' => 'required|string|unique:purchase_orders',
                'description' => 'required|string',
                'ketebalan' => 'required',
                'setting' => 'required',
                'gramasi' => 'required',
                'stock_roll' => 'required|numeric',
                'stock_kg' => 'required|numeric',
                'attachment_image' => 'required',
                'price' => 'required|numeric',
                'stock_rib' => 'required|numeric',
            ]);


            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $currentDate = now();

            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');

            // Mengambil jumlah total entri dari tabel PurchaseOrder
            $totalOrders = PurchaseOrder::count();

            // Nomor urutan adalah jumlah total entri ditambah 1
            $sequence = $totalOrders + 1;

            // Menggunakan nomor urutan yang baru untuk membuat nomor DO dan nomor PO
            $no_do = 'INV/IN/' . $year . '/' . $month . '/' . $day . '/' . $sequence;
            $no_po = 'SO' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

            $originalImageName = $request->file('attachment_image')->getClientOriginalName();
            $extension = $request->file('attachment_image')->getClientOriginalExtension();
            $randomFileName = Str::random(40) . '.' . $extension;

            $request->file('attachment_image')->storeAs('public/images/PurchaseOrder', $randomFileName);
            $request->file('attachment_image')->storeAs('public/images/InventoryTransferIn', $randomFileName);

            $purchaseOrderData = [
                'contact_id' => $request->input('contact_id'),
                'warehouse_id' => $request->input('warehouse_id'),
                'type' => 'in',
                'no_po' => $no_po,
                'no_do' => $no_do,
                'date' => $request->input('date'),
                'nama_barang' => $request->input('nama_barang'),
                'grade' => $request->input('grade'),
                'sku' => $request->input('sku'),
                'description' => $request->input('description'),
                'ketebalan' => $request->input('ketebalan'),
                'setting' => $request->input('setting'),
                'gramasi' => $request->input('gramasi'),
                'stock_roll' => $request->input('stock_roll'),
                'stock_kg' => $request->input('stock_kg'),
                'stock_rib' => $request->input('stock_rib'),
                'attachment_image' => $originalImageName,
                'price' => $request->input('price'),
            ];

            $purchaseOrder = $this->transferInRepository->create($purchaseOrderData);

            return response()->json(['Message' => 'success create new Transfer In', 'data' => $purchaseOrder], 201);
        } catch (\Exception $e) {
            if (isset($randomFileName)) {
                Storage::delete('public/images/PurchaseOrder/' . $randomFileName);
            }
            return response()->json(['error' => 'Failed to create PurchaseOrder. ' . $e->getMessage()], 422);
        }
    }
}

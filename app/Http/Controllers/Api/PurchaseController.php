<?php

namespace App\Http\Controllers\Api;

use App\Models\Warehouse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\PurchaseOrder\PurchaseOrderRepositoryInterface;

/**
 * @OA\Tag(
 *     name="PurchaseOrder",
 *     description="Endpoints for Purchase Order"
 * )
 */
class PurchaseController extends Controller
{
    private $purchaseOrderRepository;
    private $inventoryRepository;


    /**
     * Create a new purchaseOrderController instance.
     *
     * @param PurchaseOrderRepositoryInterface $purchaseOrderRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     * @return void
     */
    public function __construct(PurchaseOrderRepositoryInterface $purchaseOrderRepository, InventoryRepositoryInterface $inventoryRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'contact_id' => 'required|exists:contacts,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                // 'no_po' => 'required|string',
                // 'no_do' => 'required|string',
                'date' => 'required|date',
                'nama_barang' => 'required|string',
                'grade' => 'required|string',
                'sku' => 'required|string',
                'description' => 'required|string',
                'ketebalan' => 'required|integer',
                'setting' => 'required|integer',
                'gramasi' => 'required|integer',
                'stock' => 'required|integer',
                'attachment_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                'price' => 'required|numeric',
                'stock_rib' => 'required|integer',
            ]);


            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $currentDate = now();

            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');

            $lastSequence = PurchaseOrder::whereDate('created_at', $currentDate)->count() + 1;

            $no_do = 'INV/IN/' . $year . '/' . $month . '/' . $day . '/' . $lastSequence;
            $no_po = 'PO00' . $lastSequence;

            $originalImageName = $request->file('attachment_image')->getClientOriginalName();
            $extension = $request->file('attachment_image')->getClientOriginalExtension();
            $randomFileName = Str::random(40) . '.' . $extension;

            $request->file('attachment_image')->storeAs('public/images/PurchaseOrder', $randomFileName);
            $request->file('attachment_image')->storeAs('public/images/InventoryTransferIn', $randomFileName);

            $purchaseOrderData = [
                'contact_id' => $request->input('contact_id'),
                'warehouse_id' => $request->input('warehouse_id'),
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
                'stock' => $request->input('stock'),
                'attachment_image' => $originalImageName,
                'price' => $request->input('price'),
                'stock_rib' => $request->input('stock_rib'),
            ];

            $this->inventoryRepository->create($purchaseOrderData);
            $purchaseOrder = $this->purchaseOrderRepository->create($purchaseOrderData);

            return response()->json(['Message' => 'success create new PurchaseOrder', 'data' => $purchaseOrder], 201);
        } catch (\Exception $e) {
            if (isset($randomFileName)) {
                Storage::delete('public/images/PurchaseOrder/' . $randomFileName);
                Storage::delete('public/images/InventoryTransferIn/' . $randomFileName);
            }
            return response()->json(['error' => 'Failed to create PurchaseOrder. ' . $e->getMessage()], 422);
        }
    }

    public function index(Request $request)
    {
        try {
            $purchaseOrders = $this->purchaseOrderRepository->findAll();

            return response()->json(['Message' => 'success fetch PurchaseOrders', 'data' => $purchaseOrders], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch PurchaseOrders. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $poId)
    {
        try {
            $purchaseOrder = $this->purchaseOrderRepository->find($poId);
            return response()->json(['message' => 'PurchaseOrder retrieved successfully', 'data' => $purchaseOrder], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve PurchaseOrder. ' . $e->getMessage()], 422);
        }
    }

    public function update(Request $request, string $poId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'nullable|date',
                'nama_barang' => 'nullable|string',
                'grade' => 'nullable|string',
                'sku' => 'nullable|string',
                'description' => 'nullable|string',
                'ketebalan' => 'nullable|integer',
                'setting' => 'nullable|integer',
                'gramasi' => 'nullable|integer',
                'stock' => 'nullable|integer',
                'attachment_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'stock_rib' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $purchaseOrder = $this->purchaseOrderRepository->find($poId);

            $data = [
                'date' => $request->input('date') ?? $purchaseOrder->date,
                'nama_barang' => $request->input('nama_barang') ?? $purchaseOrder->nama_barang,
                'grade' => $request->input('grade') ?? $purchaseOrder->grade,
                'sku' => $request->input('sku') ?? $purchaseOrder->sku,
                'description' => $request->input('description') ?? $purchaseOrder->description,
                'ketebalan' => $request->input('ketebalan') ?? $purchaseOrder->ketebalan,
                'setting' => $request->input('setting') ?? $purchaseOrder->setting,
                'gramasi' => $request->input('gramasi') ?? $purchaseOrder->gramasi,
                'stock' => $request->input('stock') ?? $purchaseOrder->stock,
                'stock_rib' => $request->input('stock_rib') ?? $purchaseOrder->stock_rib,
            ];

            if ($request->hasFile('attachment_image')) {
                $originalImageName = $request->file('attachment_image')->getClientOriginalName();
                $path = $request->file('attachment_image')->storeAs('public/images/PurchaseOrder', $originalImageName);

                // Delete old attachment if exists
                if ($purchaseOrder->attachment_image) {
                    Storage::delete('public/images/PurchaseOrder/' . $purchaseOrder->attachment_image);
                }

                $data['attachment_image'] = $originalImageName;
            }

            $po =  $this->purchaseOrderRepository->update($poId, $data);

            return response()->json(['message' => 'PurchaseOrder updated successfully', 'data' => $po], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update PurchaseOrder. ' . $e->getMessage()], 422);
        }
    }


    public function delete(string $poId)
    {
        try {
            $purchaseOrder = $this->purchaseOrderRepository->delete($poId);
            return response()->json(['message' => 'PurchaseOrder deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete PurchaseOrder. ' . $e->getMessage()], 422);
        }
    }
}
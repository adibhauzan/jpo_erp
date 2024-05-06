<?php

namespace App\Http\Controllers\Api;

use App\Models\Token;
use App\Models\Warehouse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ValidationTokenUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Token\TokenRepositoryInterface;
use App\Service\PurchaseOrder\EloquentPurchaseOrderService;
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
    private $purchaseOrderService;


    /**
     * Create a new purchaseOrderController instance.
     *
     * @param PurchaseOrderRepositoryInterface $purchaseOrderRepository
     * @param EloquentPurchaseOrderService $purchaseOrderService
     * @return void
     */
    public function __construct(PurchaseOrderRepositoryInterface $purchaseOrderRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
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
                'ketebalan' => 'required|string',
                'setting' => 'required|string',
                'gramasi' => 'required|string',
                'stock_roll' => 'required|numeric',
                'stock_kg' => 'required|numeric',
                'stock_rib' => 'required|numeric',
                'attachment_image' => 'required',
                'price' => 'required|numeric',
            ]);


            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $currentDate = now();

            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');

            $totalOrders = PurchaseOrder::count();

            $sequence = $totalOrders + 1;
            $no_do = 'INV/IN/' . $year . '/' . $month . '/' . $day . '/' . $sequence;
            $no_po = 'PO' . str_pad($sequence, 5, '0', STR_PAD_LEFT); // Perbaikan 4: Nomor SO menggunakan timestamp


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
                'stock_roll' => $request->input('stock_roll'),
                'stock_kg' => $request->input('stock_kg'),
                'stock_rib' => $request->input('stock_rib'),
                'attachment_image' => $originalImageName,
                'price' => $request->input('price'),
            ];

            $purchaseOrder = $this->purchaseOrderRepository->create($purchaseOrderData);

            return response()->json(['Message' => 'success create new PurchaseOrder', 'data' => $purchaseOrder], 201);
        } catch (\Exception $e) {
            if (isset($randomFileName)) {
                Storage::delete('public/images/PurchaseOrder/' . $randomFileName);
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

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            if (!$request->has('update_key')) {
                return response()->json(['error' => 'Update key is required'], 422);
            }
            $updateKey = $request->input('update_key');

            $validationToken = ValidationTokenUpdate::where('update_key', $updateKey)
                ->where('status', 'not')
                ->first();

            if (!$validationToken) {
                return response()->json(['error' => 'Update key not found or already used'], 404);
            }

            $purchaseOrder = PurchaseOrder::find($id);
            if (!$purchaseOrder) {
                return response()->json(['error' => 'Purchase Order not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'date' => 'nullable|date',
                'nama_barang' => 'nullable|string',
                'grade' => 'nullable|string',
                'sku' => 'nullable|string',
                'description' => 'nullable|string',
                'ketebalan' => 'nullable|string',
                'setting' => 'nullable|string',
                'gramasi' => 'nullable|string',
                'stock_roll' => 'nullable|numeric',
                'stock_kg' => 'nullable|numeric',
                'stock_rib' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            if ($request->hasFile('attachment_image')) {
                $validator = Validator::make($request->all(), [
                    'attachment_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:6144',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }
                if ($purchaseOrder->attachment_image) {
                    Storage::delete('public/images/PurchaseOrder/' . $purchaseOrder->attachment_image);
                }

                // Simpan gambar baru
                $originalImageName = $request->file('attachment_image')->getClientOriginalName();
                $slug = Str::slug($request->input('nama_barang'));
                $request->file('attachment_image')->storeAs('public/images/PurchaseOrder/', $originalImageName);

                // Update NewsArticle
                $purchaseOrder->update([
                    'date' => $request->input('date') ?? $purchaseOrder->date,
                    'nama_barang' => $request->input('nama_barang') ?? $purchaseOrder->nama_barang,
                    'grade' => $request->input('grade') ?? $purchaseOrder->grade,
                    'sku' => $request->input('sku') ?? $purchaseOrder->sku,
                    'attachment_image' => $originalImageName,
                    'description' => $request->input('description') ?? $purchaseOrder->description,
                    'ketebalan' => $request->input('ketebalan') ?? $purchaseOrder->ketebalan,
                    'setting' => $request->input('setting') ?? $purchaseOrder->setting,
                    'gramasi' => $request->input('gramasi') ?? $purchaseOrder->gramasi,
                    'stock_roll' => $request->input('stock_roll') ?? $purchaseOrder->stock_roll,
                    'stock_kg' => $request->input('stock_kg') ?? $purchaseOrder->stock_kg,
                    'stock_rib' => $request->input('stock_rib') ?? $purchaseOrder->stock_rib,
                ]);
            } else {
                $purchaseOrder->update([
                    'date' => $request->input('date') ?? $purchaseOrder->date,
                    'nama_barang' => $request->input('nama_barang') ?? $purchaseOrder->nama_barang,
                    'grade' => $request->input('grade') ?? $purchaseOrder->grade,
                    'sku' => $request->input('sku') ?? $purchaseOrder->sku,
                    'description' => $request->input('description') ?? $purchaseOrder->description,
                    'ketebalan' => $request->input('ketebalan') ?? $purchaseOrder->ketebalan,
                    'setting' => $request->input('setting') ?? $purchaseOrder->setting,
                    'gramasi' => $request->input('gramasi') ?? $purchaseOrder->gramasi,
                    'stock_roll' => $request->input('stock_roll') ?? $purchaseOrder->stock_roll,
                    'stock_kg' => $request->input('stock_kg') ?? $purchaseOrder->stock_kg,
                    'stock_rib' => $request->input('stock_rib') ?? $purchaseOrder->stock_rib,
                ]);
            }

            $validationToken->update([
                'status' => 'used',
                'user_id' => $user->id,
            ]);

            return response()->json(['data' => $purchaseOrder], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update the NewsArticle. ' . $e->getMessage()], 500);
        }
    }




    public function delete(string $poId)
    {
        try {
            $purchaseOrder = $this->purchaseOrderRepository->delete($poId);
            return response()->json(['message' => 'PurchaseOrder deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'sFailed to delete PurchaseOrder. ' . $e->getMessage()], 422);
        }
    }
}

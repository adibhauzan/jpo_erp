<?php

namespace App\Http\Controllers\Api\Inventory\Transfer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Inventory\Transfer\Out\TransferOutRepositoryInterface;

class TransferOutController extends Controller
{
    private $transferOutRepository;


    /**
     * Create a new stockController instance.
     *
     * @param TransferOutRepositoryInterface $stockRepository
     * @return void
     */
    public function __construct(TransferOutRepositoryInterface $transferOutRepository)
    {
        $this->transferOutRepository = $transferOutRepository;
    }

    public function index()
    {
        try {
            $transferOut = $this->transferOutRepository->findAll();
            return response()->json(['message' => ' Transfer Out Fetch successfully', 'data' => $transferOut], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed retrieved Stock', 'data' => $e->getMessage()], 500);
        }
    }

    public function show(string $inId)
    {
        try {
            $transferIn = $this->transferOutRepository->find($inId);
            return response()->json(['message' => 'Transfer Out retrieved successfully', 'data' => $transferIn], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Transfer In. ' . $e->getMessage()], 422);
        }
    }

    // public function update(Request $request, string $inId)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'nama_barang' => 'nullable|string',
    //             'grade' => 'nullable|string',
    //             'sku' => 'nullable|string|unique:purchase_orders,sku',
    //             'description' => 'nullable|string',
    //             'ketebalan' => 'nullable|integer',
    //             'setting' => 'nullable|integer',
    //             'gramasi' => 'nullable|integer',
    //             'stock' => 'nullable|integer',
    //             'attachment_image' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
    //             'stock_rib' => 'nullable|integer',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['error' => $validator->errors()], 422);
    //         }

    //         $data = $request->only([
    //             'nama_barang', 'grade', 'sku', 'description',
    //             'ketebalan', 'setting', 'gramasi', 'stock', 'stock_rib'
    //         ]);

    //         if ($request->hasFile('attachment_image')) {
    //             $originalImageName = $request->file('attachment_image')->getClientOriginalName();
    //             $path = $request->file('attachment_image')->storeAs('public/images/PurchaseOrder', $originalImageName);
    //             $data['attachment_image'] = $originalImageName;
    //         }

    //         $transferIn = $this->transferOutRepository->update($inId, $data);

    //         $transferInUpdated = $this->transferOutRepository->find($inId);

    //         return response()->json(['message' => 'Transfer In updated successfully', 'data' => $transferInUpdated], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to update Transfer In. ' . $e->getMessage()], 422);
    //     }
    // }

    // public function receive(Request $request, string $inId)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'stock_rev' => 'nullable|integer',
    //             'stock_rib_rev' => 'nullable|integer',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['error' => $validator->errors()], 422);
    //         }

    //         $quantityStockReceived = $request->input('stock_rev');
    //         $quantityRibReceived = $request->input('stock_rib_rev');

    //         $transferin = $this->transferOutRepository->receive($inId, $quantityStockReceived, $quantityRibReceived);

    //         return response()->json(['message' => 'Transfer in received successfully'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 422);
    //     }
    // }

    // public function store(Request $request)
    // {
    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'contact_id' => 'required|exists:contacts,id',
    //             'warehouse_id' => 'required|exists:warehouses,id',
    //             'date' => 'required|date',
    //             'nama_barang' => 'required|string',
    //             'grade' => 'required|string',
    //             'sku' => 'required|string|unique:purchase_orders',
    //             'description' => 'required|string',
    //             'ketebalan' => 'required|integer',
    //             'setting' => 'required|integer',
    //             'gramasi' => 'required|integer',
    //             'stock' => 'required|integer',
    //             'attachment_image' => 'required',
    //             'price' => 'required|numeric',
    //             'stock_rib' => 'required|integer',
    //         ]);


    //         if ($validator->fails()) {
    //             return response()->json(['error' => $validator->errors()], 422);
    //         }

    //         $currentDate = now();

    //         $year = $currentDate->format('Y');
    //         $month = $currentDate->format('m');
    //         $day = $currentDate->format('d');

    //         $lastSequence = PurchaseOrder::whereDate('created_at', $currentDate)->count() + 1;

    //         $no_do = 'INV/IN/' . $year . '/' . $month . '/' . $day . '/' . $lastSequence;
    //         $no_po = 'PO00' . $lastSequence;

    //         $originalImageName = $request->file('attachment_image')->getClientOriginalName();
    //         $extension = $request->file('attachment_image')->getClientOriginalExtension();
    //         $randomFileName = Str::random(40) . '.' . $extension;

    //         $request->file('attachment_image')->storeAs('public/images/PurchaseOrder', $randomFileName);
    //         $request->file('attachment_image')->storeAs('public/images/InventoryTransferIn', $randomFileName);

    //         $purchaseOrderData = [
    //             'contact_id' => $request->input('contact_id'),
    //             'warehouse_id' => $request->input('warehouse_id'),
    //             'type' => 'in',
    //             'no_po' => $no_po,
    //             'no_do' => $no_do,
    //             'date' => $request->input('date'),
    //             'nama_barang' => $request->input('nama_barang'),
    //             'grade' => $request->input('grade'),
    //             'sku' => $request->input('sku'),
    //             'description' => $request->input('description'),
    //             'ketebalan' => $request->input('ketebalan'),
    //             'setting' => $request->input('setting'),
    //             'gramasi' => $request->input('gramasi'),
    //             'stock' => $request->input('stock'),
    //             'stock_rib' => $request->input('stock_rib'),
    //             'attachment_image' => $originalImageName,
    //             'price' => $request->input('price'),
    //         ];

    //         $purchaseOrder = $this->transferOutRepository->create($purchaseOrderData);

    //         return response()->json(['Message' => 'success create new PurchaseOrder', 'data' => $purchaseOrder], 201);
    //     } catch (\Exception $e) {
    //         if (isset($randomFileName)) {
    //             Storage::delete('public/images/PurchaseOrder/' . $randomFileName);
    //         }
    //         return response()->json(['error' => 'Failed to create PurchaseOrder. ' . $e->getMessage()], 422);
    //     }
    // }
}

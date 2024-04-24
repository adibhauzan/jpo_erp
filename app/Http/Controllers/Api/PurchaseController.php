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
use App\Repositories\Token\TokenRepositoryInterface;

/**
 * @OA\Tag(
 *     name="PurchaseOrder",
 *     description="Endpoints for Purchase Order"
 * )
 */
class PurchaseController extends Controller
{
    private $purchaseOrderRepository;
    private $tokenRepository;


    /**
     * Create a new purchaseOrderController instance.
     *
     * @param PurchaseOrderRepositoryInterface $purchaseOrderRepository
     * @param TokenRepositoryInterface $tokenRepository
     * @return void
     */
    public function __construct(PurchaseOrderRepositoryInterface $purchaseOrderRepository, TokenRepositoryInterface $tokenRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->tokenRepository = $tokenRepository;
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

    // public function update(Request $request, string $poId)
    // {
    //     try {

    //         $validator = validator()->make($request->all(), [
    //             'stock_roll' => 'nullable|numeric',
    //             'stock_kg' => 'nullable|numeric',
    //             'stock_rib' => 'nullable|numeric',
    //             'token_update' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['error' => $validator->errors()], 422);
    //         }

    //         $data = $request->only(['stock_roll', 'stock_kg', 'stock_rib']);
    //         $token = $request->input('token_update');

    //         $po = $this->purchaseOrderRepository->update($poId, $data, $token);
    //         if (isset($po['stock_kg'])) {
    //             $po['stock_kg'] = (float) $po['stock_kg'];
    //         }
    //         if (isset($po['stock_rib'])) {
    //             $po['stock_rib'] = (float) $po['stock_rib'];
    //         }
    //         return response()->json(['message' => 'PurchaseOrder updated successfully', 'data' => $po], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 422);
    //     }
    // }

    // public function update(Request $request, string $poId)
    // {
    //     try {
    //         $validator = validator()->make($request->all(), [
    //             'stock_roll' => 'nullable|numeric',
    //             'stock_kg' => 'nullable|numeric',
    //             'stock_rib' => 'nullable|numeric',
    //             'token_update' => 'required|string',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['error' => $validator->errors()], 422);
    //         }

    //         $data = $request->only(['stock_roll', 'stock_kg', 'stock_rib']);
    //         $token = $request->input((string)'token_update');

    //         $po = $this->purchaseOrderRepository->update($poId, $data, $token);
    //         $po['stock_roll'] = (float) $po['stock_roll'];
    //         $po['stock_kg'] = (float) $po['stock_kg'];
    //         $po['stock_rib'] = (float) $po['stock_rib'];

    //         return response()->json(['message' => 'PurchaseOrder updated successfully'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 422);
    //     }
    // }

    // public function update(Request $request, string $poId)
    // {
    //     // try {

    //     //     $validator1 = Validator::make($request->only('stock_roll', 'stock_kg', 'stock_rib'), [
    //     //         'stock_roll' => 'nullable|numeric',
    //     //         'stock_kg' => 'nullable|numeric',
    //     //         'stock_rib' => 'nullable|numeric',
    //     //     ]);

    //     //     // Validasi untuk parameter ke-3 dari repository
    //     //     $validator2 = Validator::make($request->only('token_update'), [
    //     //         'token_update' => 'nullable|string',
    //     //     ]);

    //     //     // Periksa jika validasi gagal untuk salah satu validator
    //     //     if ($validator1->fails()) {
    //     //         // Lakukan sesuatu jika validasi gagal untuk parameter ke-2 dari repository
    //     //         return response()->json(['error' => $validator1->errors()], 422);
    //     //     }

    //     //     if ($validator2->fails()) {
    //     //         // Lakukan sesuatu jika validasi gagal untuk parameter ke-3 dari repository
    //     //         return response()->json(['error' => $validator2->errors()], 422);
    //     //     }

    //     //     // Mengambil data dari request
    //     //     $data = $request->only(['stock_roll', 'stock_kg', 'stock_rib']);
    //     //     $token = $request->input((string)'token_update');

    //     //     // Melakukan pembaruan pada repository dengan data yang diberikan
    //     //     $po = $this->purchaseOrderRepository->update($poId, $data, $token);

    //     //     // Mengonversi data yang diperbarui ke float (jika diperlukan)
    //     //     $po['stock_roll'] = (float) $po['stock_roll'];
    //     //     $po['stock_kg'] = (float) $po['stock_kg'];
    //     //     $po['stock_rib'] = (float) $po['stock_rib'];

    //     //     return response()->json(['message' => 'PurchaseOrder updated successfully'], 200);
    //     // } catch (\Exception $e) {
    //     //     return response()->json(['error' => $e->getMessage()], 422);
    //     // }

    //     try {
    //         // Validasi input request
    //         $validator = Validator::make($request->all(), [
    //             'stock_roll' => 'nullable|numeric',
    //             'stock_kg' => 'nullable|numeric',
    //             'stock_rib' => 'nullable|numeric',
    //             'token_update' => 'nullable|string', // Token harus disertakan dalam request
    //         ]);

    //         // Jika validasi gagal, kembalikan respons dengan kesalahan
    //         if ($validator->fails()) {
    //             return response()->json(['error' => $validator->errors()], 422);
    //         }

    //         // Mengambil data dari request
    //         $data = $validator->validated();
    //         $token = $data['token_update']; // Mendapatkan token dari data validasi
    //         unset($data['token_update']); // Menghapus token dari data update

    //         // Melakukan pembaruan pada repository dengan data yang diberikan
    //         $po = $this->purchaseOrderRepository->update($poId, $data, (string)$token);

    //         // Mengonversi data yang diperbarui ke float (jika diperlukan)
    //         $po['stock_roll'] = (float) $po['stock_roll'];
    //         $po['stock_kg'] = (float) $po['stock_kg'];
    //         $po['stock_rib'] = (float) $po['stock_rib'];

    //         // Memberikan respons bahwa PurchaseOrder berhasil diperbarui
    //         return response()->json(['message' => 'PurchaseOrder updated successfully'], 200);
    //     } catch (\Exception $e) {
    //         // Jika terjadi kesalahan, kembalikan respons dengan pesan kesalahan
    //         return response()->json(['error' => $e->getMessage()], 422);
    //     }
    // }


    public function update(Request $request, string $poId)
    {
        try {
            // Validasi request
            $request->validate([
                'stock_roll' => 'nullable|numeric',
                'stock_kg' => 'nullable|numeric',
                'stock_rib' => 'nullable|numeric',
                // 'bujang' => 'nullable|exists:tokens,token_update'
            ]);

            $data = $request->only(['stock_roll', 'stock_kg', 'stock_rib']);
            $tokenUpdate = $request->input('token_update');

            $success = $this->purchaseOrderRepository->update($poId, $data, $tokenUpdate);

            if ($success) {
                return response()->json(['message' => 'Purchase order berhasil diperbarui'], 200);
            } else {
                return response()->json(['message' => 'Gagal memperbarui purchase order. Token tidak valid'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
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

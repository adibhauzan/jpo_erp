<?php

namespace App\Http\Controllers\Api\Inventory\Stock;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ValidationTokenUpdate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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

    public function index()
    {
        try {
            $stock = $this->stockRepository->findAll();
            return response()->json(['message' => 'Stock fetch successfully', 'data' => $stock], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed fetch Stock', 'data' => $e->getMessage()], 500);
        }
    }

    public function show(string $stockId)
    {
        try {
            $transferIn = $this->stockRepository->find($stockId);
            return response()->json(['message' => 'Stock retrieved successfully', 'data' => $transferIn], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Stock. ' . $e->getMessage()], 422);
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
                return response()->json(['error' => 'Stock not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'nama_barang' => 'nullable|string',
                'grade' => 'nullable|string',
                'description' => 'nullable|string',
                'ketebalan' => 'nullable|string',
                'setting' => 'nullable|string',
                'gramasi' => 'nullable|string',
                'price' => 'nullable'
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

                $originalImageName = $request->file('attachment_image')->getClientOriginalName();
                $slug = Str::slug($request->input('nama_barang'));
                $request->file('attachment_image')->storeAs('public/images/PurchaseOrder/', $originalImageName);

                $purchaseOrder->update([
                    'nama_barang' => $request->input('nama_barang') ?? $purchaseOrder->nama_barang,
                    'grade' => $request->input('grade') ?? $purchaseOrder->grade,
                    'description' => $request->input('description') ?? $purchaseOrder->description,
                    'ketebalan' => $request->input('ketebalan') ?? $purchaseOrder->ketebalan,
                    'setting' => $request->input('setting') ?? $purchaseOrder->setting,
                    'gramasi' => $request->input('gramasi') ?? $purchaseOrder->gramasi,
                    'price' => $request->input('price') ?? $purchaseOrder->gramasi,
                    'attachment_image' => $originalImageName,
                ]);
            } else {
                $purchaseOrder->update([
                    'nama_barang' => $request->input('nama_barang') ?? $purchaseOrder->nama_barang,
                    'grade' => $request->input('grade') ?? $purchaseOrder->grade,
                    'description' => $request->input('description') ?? $purchaseOrder->description,
                    'ketebalan' => $request->input('ketebalan') ?? $purchaseOrder->ketebalan,
                    'setting' => $request->input('setting') ?? $purchaseOrder->setting,
                    'gramasi' => $request->input('gramasi') ?? $purchaseOrder->gramasi,
                    'price' => $request->input('price') ?? $purchaseOrder->price,
                ]);
            }

            $validationToken->update([
                'status' => 'used',
                'user_id' => $user->id,
            ]);

            return response()->json(['data' => $purchaseOrder], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update the Stock. ' . $e->getMessage()], 500);
        }
    }
}

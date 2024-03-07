<?php

namespace App\Http\Controllers\Api\Inventory\Transfer;

use Illuminate\Http\Request;
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
                'stock_rev' => 'nullable|integer',
                'stock_rib_rev' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $quantityStockReceived = $request->input('stock_rev');
            $quantityRibReceived = $request->input('stock_rib_rev');

            $transferin = $this->transferInRepository->receive($inId, $quantityStockReceived, $quantityRibReceived);

            return response()->json(['message' => 'Transfer in received successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}

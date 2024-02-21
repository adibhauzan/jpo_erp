<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Warehouse\WarehouseRepositoryInterface;

class WarehouseController extends Controller
{
    private $warehouseRepository;

    /**
     * Create a new WarehouseController instance.
     *
     * @param WarehouseRepositoryInterface $warehouseRepository
     * @return void
     */
    public function __construct(WarehouseRepositoryInterface $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|unique:warehouses,address',
            'phone_number' => 'required|string|min:8|max:15|unique:warehouses,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',
            'store_id' => [
                'required_without:convection_id',
                'string',
            ],
            'convection_id' => [
                'required_without:store_id',
                'string',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        try {
            $warehouseData = [
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'phone_number' => $request->input('phone_number'),
                'store_id' => $request->input('store_id'),
                'convection_id' => $request->input('convection_id'),
            ];

            $warehouse = $this->warehouseRepository->create($warehouseData);

            return response()->json(['Message' => 'success create new warehouse', 'data' => $warehouse], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create warehouse. ' . $e->getMessage()], 422);
        }
    }

    public function index(Request $request)
    {
        try {
            $warehouses = $this->warehouseRepository->findAll();

            return response()->json(['Message' => 'success fetch warehouses', 'data' => $warehouses], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch warehouses. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $warehouseId)
    {
        try {
            $warehouse = $this->warehouseRepository->find($warehouseId);
            return response()->json(['message' => 'warehouse retrieved successfully', 'data' => $warehouse], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve warehouse. ' . $e->getMessage()], 422);
        }
    }

    public function update(Request $request, string $warehouseId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|unique:warehouses,address',
            'phone_number' => 'required|string|min:8|max:15|unique:warehouses,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['name', 'address', 'phone_number']);

            $warehouse = $this->warehouseRepository->update($warehouseId, $data);

            return response()->json(['message' => 'warehouse updated successfully', 'data' => $warehouse], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update warehouse. ' . $e->getMessage()], 422);
        }
    }

    public function delete(string $warehouseId)
    {
        try {
            $warehouse = $this->warehouseRepository->delete($warehouseId);
            return response()->json(['message' => 'warehouse deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete warehouse. ' . $e->getMessage()], 422);
        }
    }
}
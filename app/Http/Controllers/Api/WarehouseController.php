<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Warehouse\WarehouseRepositoryInterface;

/**
 * @OA\Tag(
 *     name="Warehouse",
 *     description="Endpoints for warehouse"
 * )
 */
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

    /**
     * @OA\Post(
     *     path="/api/auth/warehouse",
     *     summary="Create a new warehouse",
     *     operationId="createWarehouse",
     *     tags={"Warehouse"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Choose either store_id or convection_id",
     *         @OA\JsonContent(
     *             @OA\Property(property="store_id", type="string", format="uuid",example="", description="ID of the store (optional)"),
     *             @OA\Property(property="convection_id", type="string",format="uuid",example="", description="ID of the convection (optional)"),
     *             @OA\Property(property="name", type="string", default="toko1"),
     *             @OA\Property(property="address", type="string", default="toko1 bandung"),
     *             @OA\Property(property="phone_number", type="string", default="12345677821"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description= "success create new Warehouse",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object"),
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * 
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => [
                'required_without:convection_id',
                'string',
            ],
            'convection_id' => [
                'required_without:store_id',
                'string',
            ],
            'name' => 'required|string',
            'address' => 'required|unique:warehouses,address',
            'phone_number' => 'required|string|min:8|max:15|unique:warehouses,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        try {
            $warehouseData = [
                'store_id' => $request->input('store_id'),
                'convection_id' => $request->input('convection_id'),
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'phone_number' => $request->input('phone_number'),
            ];

            $warehouse = $this->warehouseRepository->create($warehouseData);

            return response()->json(['Message' => 'success create new warehouse', 'data' => $warehouse], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create warehouse. ' . $e->getMessage()], 422);
        }
    }

    /**
     * 
     * Get All Warehouse
     * 
     * @OA\Get(
     *     path="/api/auth/warehouses/",
     *     summary="Get all warehouses",
     *     operationId="indexWarehouse",
     *     tags={"Warehouse"},
     *     @OA\Response(
     *         response=200,
     *         description= "success fetch Warehouses",
     *         @OA\JsonContent(
     *             @OA\Property(property="Warehouses", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to fetch Warehouses.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $warehouses = $this->warehouseRepository->findAll();

            return response()->json(['Message' => 'success fetch warehouses', 'data' => $warehouses], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch warehouses. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get warehouse by id.
     * 
     * @OA\Get(
     *     path="/api/auth/warehouse/{id}",
     *     summary="Get warehouse by id.",
     *     operationId="showWarehouse",
     *     tags={"Warehouse"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the warehouse",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string"),
     *             @OA\Property(property="store_id", type="string"),
     *             @OA\Property(property="convection_id", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch warehouse",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $warehouseId UUID of the warehouse
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $warehouseId)
    {
        try {
            $warehouse = $this->warehouseRepository->find($warehouseId);
            return response()->json(['message' => 'warehouse retrieved successfully', 'data' => $warehouse], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve warehouse. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Update Warehouse by ID.
     *
     * @OA\Put(
     *     path="/api/auth/warehouse/u/{id}",
     *     summary="Update warehouse by ID",
     *     operationId="updateWarehouse",
     *     tags={"Warehouse"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the warehouse to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Warehouse Name"),
     *             @OA\Property(property="address", type="string", example="Updated Warehouse Address"),
     *             @OA\Property(property="phone_number", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Warehouse updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Warehouse updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="store_id", type="string"),
     *                 @OA\Property(property="convection_id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="phone_number", type="string"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or failed to update warehouse",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $warehouseId UUID of the warehouse to update
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $warehouseId)
    {
        try {
            $warehouse = $this->warehouseRepository->find($warehouseId);

            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'address' => 'nullable|string',
                'phone_number' => 'nullable|string|min:8|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $data = [
                'name' => $request->input('name') ?? $warehouse->name,
                'address' => $request->input('address') ?? $warehouse->address,
                'phone_number' => $request->input('phone_number') ?? $warehouse->phone_number,
            ];

            $this->warehouseRepository->update($warehouseId, $data);

            $updatedWarehouse = $this->warehouseRepository->find($warehouseId);

            return response()->json(['message' => 'Warehouse updated successfully', 'data' => $updatedWarehouse], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update warehouse. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Ban Warehouse.
     *
     * @OA\Post(
     *     path="/api/auth/warehouse/ban/{id}",
     *     summary="Ban warehouse",
     *     operationId="banWarehouse",
     *     tags={"Warehouse"},
     *     @OA\Response(
     *         response=200,
     *         description="Success message",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized. Token is missing or invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to ban warehouse",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the warehouse to ban",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function banWarehouse(Request $request, $id)
    {
        try {
            $this->warehouseRepository->banWarehouse($id);
            $warehouse = $this->warehouseRepository->find($id);
            $warehouse->status = 'suspend';
            $warehouse->save();
            return response()->json(['message' => 'warehouse berhasil dibanned.']);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal memban gudang. Terjadi kesalahan database: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memban gudang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * UnBan Warehouse.
     *
     * @OA\Post(
     *     path="/api/auth/warehouse/unban/{id}",
     *     summary="UnBan Warehouse",
     *     operationId="unBanWarehouse",
     *     tags={"Warehouse"},
     *     @OA\Response(
     *         response=200,
     *         description="Success message",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized. Token is missing or invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to ban Warehouse",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the warehouse to ban",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unBanWarehouse(Request $request, $id)
    {
        try {
            $warehouse = $this->warehouseRepository->find($id);
            $warehouse->status = 'active';
            $warehouse->save();
            $this->warehouseRepository->unBanWarehouse($id);
            return response()->json(['message' => 'Warehouse berhasil dipulihkan.']);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal memulihkan pengguna. Terjadi kesalahan database: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memulihkan pengguna: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Warehouse by ID.
     *
     * @OA\Delete(
     *     path="/api/auth/warehouse/d/{id}",
     *     summary="Delete warehouse by ID",
     *     operationId="deleteWarehouse",
     *     tags={"Warehouse"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the warehouse to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Warehouse deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Warehouse deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to delete warehouse",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $warehouseId UUID of the warehouse to delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $warehouseId)
    {
        try {
            $warehouse = $this->warehouseRepository->delete($warehouseId);
            return response()->json(['message' => 'warehouse deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete warehouse. ' . $e->getMessage()], 422);
        }
    }

    public function getWarehousesByLoggedUser()
    {
        try {
            $warehouses = $this->warehouseRepository->getByLoggedInUser();
            return response()->json($warehouses);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve warehouses.'], 500);
        }
    }
}

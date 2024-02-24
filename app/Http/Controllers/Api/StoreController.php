<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;

/**
 * @OA\Tag(
 *     name="Store",
 *     description="Endpoints for store"
 * )
 */
class StoreController extends Controller
{
    private $storeRepository;
    private $userRepository;


    /**
     * Create a new StoreController instance.
     *
     * @param StoreRepositoryInterface $storeRepository
     * @param UserRepositoryInterface $userRepository
     * @return void
     */
    public function __construct(StoreRepositoryInterface $storeRepository, UserRepositoryInterface $userRepository)
    {
        $this->storeRepository = $storeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/store",
     *     summary="Create a new store",
     *     operationId="createStore",
     *     tags={"Store"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", default="toko1"),
     *             @OA\Property(property="address", type="string", default="toko1 bandung"),
     *             @OA\Property(property="phone_number", type="string", default="12345677821"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description= "success create new Store",
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
            'name' => 'required|string',
            'address' => 'required|unique:stores,address',
            'phone_number' => 'required|string|min:8|max:15|unique:stores,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        try {
            $storeData = [
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'phone_number' => $request->input('phone_number'),
            ];

            $store = $this->storeRepository->create($storeData);

            return response()->json(['Message' => 'success create new Store', 'data' => $store], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create Store. ' . $e->getMessage()], 422);
        }
    }

    /**
     * 
     * Get All Stores
     * 
     * @OA\Get(
     *     path="/api/auth/stores/",
     *     summary="Get all stores",
     *     operationId="indexStore",
     *     tags={"Store"},
     *     @OA\Response(
     *         response=200,
     *         description= "success fetch Stores",
     *         @OA\JsonContent(
     *             @OA\Property(property="stores", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to fetch Stores.",
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
            $stores = $this->storeRepository->findAll();

            return response()->json(['Message' => 'success fetch Stores', 'data' => $stores], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Stores. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get store by id.
     * 
     * @OA\Get(
     *     path="/api/auth/store/{id}",
     *     summary="Get store by id.",
     *     operationId="showStore",
     *     tags={"Store"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the store",
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
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch store",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $storeId UUID of the store
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);
            return response()->json(['message' => 'Store retrieved successfully', 'data' => $store], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Store. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Update store by ID.
     *
     * @OA\Put(
     *     path="/api/auth/store/u/{id}",
     *     summary="Update store by ID",
     *     operationId="updateStore",
     *     tags={"Store"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the store to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Store Name"),
     *             @OA\Property(property="address", type="string", example="Updated Store Address"),
     *             @OA\Property(property="phone_number", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Store updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="phone_number", type="string"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or failed to update store",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $storeId UUID of the store to update
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $storeId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|unique:stores,address',
            'phone_number' => 'required|string|min:8|max:15|unique:stores,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['name', 'address', 'phone_number']);

            $store = $this->storeRepository->update($storeId, $data);

            return response()->json(['message' => 'Store updated successfully', 'data' => $store], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Store. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Delete store by ID.
     *
     * @OA\Delete(
     *     path="/api/auth/store/d/{id}",
     *     summary="Delete store by ID",
     *     operationId="deleteStore",
     *     tags={"Store"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the store to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Store deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to delete store",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $storeId UUID of the store to delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $storeId)
    {
        try {
            $store = $this->storeRepository->delete($storeId);
            return response()->json(['message' => 'Store deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete Store. ' . $e->getMessage()], 422);
        }
    }

    public function banStore(string $id)
    {
        try {
            $users = User::where('store_id', $id)->get();

            foreach ($users as $user) {
                try {
                    $this->userRepository->banUser($user->id);

                    $user->status = 'suspend';
                    $user->save();
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Gagal melarang pengguna: ' . $e->getMessage()], 500);
                }
            }

            try {
                $this->storeRepository->banStore($id);

                $store = $this->storeRepository->find($id);
                $store->status = 'suspend';
                $store->save();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal melarang toko: ' . $e->getMessage()], 500);
            }

            return response()->json(['message' => 'Store berhasil dibanned.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal banned Store: ' . $e->getMessage()], 500);
        }
    }


    public function unBanStore(Request $request, $id)
    {
        try {
            $users = User::where('store_id', $id)->get();

            foreach ($users as $user) {
                try {
                    $this->userRepository->unBanUser($user->id);

                    $user->status = 'active';
                    $user->save();
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Gagal membatalkan pelarangan pengguna: ' . $e->getMessage()], 500);
                }
            }

            try {
                $this->storeRepository->unBanStore($id);

                $store = $this->storeRepository->find($id);
                $store->status = 'active';
                $store->save();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal membatalkan pelarangan toko: ' . $e->getMessage()], 500);
            }

            return response()->json(['message' => 'Store berhasil dipulihkan.']);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal memulihkan Store. Terjadi kesalahan database: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memulihkan Store: ' . $e->getMessage()], 500);
        }
    }
}
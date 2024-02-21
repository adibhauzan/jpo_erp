<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Store\StoreRepositoryInterface;
use Illuminate\Support\Facades\Validator;


class StoreController extends Controller
{
    private $storeRepository;

    /**
     * Create a new StoreController instance.
     *
     * @param StoreRepositoryInterface $storeRepository
     * @return void
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

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

    public function index(Request $request)
    {
        try {
            $stores = $this->storeRepository->findAll();

            return response()->json(['Message' => 'success fetch Stores', 'data' => $stores], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Stores. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);
            return response()->json(['message' => 'Store retrieved successfully', 'data' => $store], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Store. ' . $e->getMessage()], 422);
        }
    }

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

    public function delete(string $storeId)
    {
        try {
            $store = $this->storeRepository->delete($storeId);
            return response()->json(['message' => 'Store deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete Store. ' . $e->getMessage()], 422);
        }
    }
}
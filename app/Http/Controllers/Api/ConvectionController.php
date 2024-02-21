<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Convection\ConvectionRepositoryInterface;
use Illuminate\Support\Facades\Validator;


class ConvectionController extends Controller
{
    private $convectionRepository;

    /**
     * Create a new ConvectionController instance.
     *
     * @param convectionRepositoryInterface $convectionRepository
     * @return void
     */
    public function __construct(ConvectionRepositoryInterface $convectionRepository)
    {
        $this->convectionRepository = $convectionRepository;
    }

    public function convection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|unique:convections,address',
            'phone_number' => 'required|string|min:8|max:15|unique:convections,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        try {
            $convectionData = [
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'phone_number' => $request->input('phone_number'),
            ];

            $convection = $this->convectionRepository->create($convectionData);

            return response()->json(['Message' => 'success create new Convection', 'data' => $convection], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create Convection. ' . $e->getMessage()], 422);
        }
    }

    public function index(Request $request)
    {
        try {
            $convections = $this->convectionRepository->findAll();

            return response()->json(['Message' => 'success fetch Convections', 'data' => $convections], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Convections. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $convectionId)
    {
        try {
            $convection = $this->convectionRepository->find($convectionId);
            return response()->json(['message' => 'Convection retrieved successfully', 'data' => $convection], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Convection. ' . $e->getMessage()], 422);
        }
    }

    public function update(Request $request, string $convectionId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|unique:convections,address',
            'phone_number' => 'required|string|min:8|max:15|unique:convections,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['name', 'address', 'phone_number']); 

            $convection = $this->convectionRepository->update($convectionId, $data);

            return response()->json(['message' => 'Convection updated successfully', 'data' => $convection], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Convection. ' . $e->getMessage()], 422);
        }
    }

    public function delete(string $convectionId)
    {
        try {
            $convection = $this->convectionRepository->delete($convectionId);
            return response()->json(['message' => 'Convection deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete Convection. ' . $e->getMessage()], 422);
        }
    }
}
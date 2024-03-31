<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Convection\ConvectionRepositoryInterface;

/**
 * @OA\Tag(
 *     name="Convection",
 *     description="Endpoints for convection"
 * )
 */
class ConvectionController extends Controller
{
    private $convectionRepository;
    private $userRepository;


    /**
     * Create a new ConvectionController instance.
     *
     * @param ConvectionRepositoryInterface $convectionRepository
     * @param UserRepositoryInterface $userRepository
     * @return void
     */
    public function __construct(ConvectionRepositoryInterface $convectionRepository, UserRepositoryInterface $userRepository)
    {
        $this->convectionRepository = $convectionRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/convection",
     *     summary="Create a new convection",
     *     operationId="createConvection",
     *     tags={"Convection"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", default="pabrik1"),
     *             @OA\Property(property="address", type="string", default="pabrik1 bandung"),
     *             @OA\Property(property="phone_number", type="string", default="12345677821"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description= "success create new Convection",
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
            'address' => 'required',
            'phone_number' => 'required|string|min:8|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
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

    /**
     * 
     * Get All Convections
     * 
     * @OA\Get(
     *     path="/api/auth/convections/",
     *     summary="Get all convections",
     *     operationId="indexConvection",
     *     tags={"Convection"},
     *     @OA\Response(
     *         response=200,
     *         description= "success fetch Convections",
     *         @OA\JsonContent(
     *             @OA\Property(property="convections", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to fetch Convections.",
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
            $convections = $this->convectionRepository->findAll();

            return response()->json(['Message' => 'success fetch Convections', 'data' => $convections], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Convections. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get convection by id.
     * 
     * @OA\Get(
     *     path="/api/auth/convection/{id}",
     *     summary="Get convection by id.",
     *     operationId="showConvection",
     *     tags={"Convection"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the convection",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Convection details",
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
     *         description="Failed to fetch convection",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $convectionId UUID of the convection
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $convectionId)
    {
        try {
            $convection = $this->convectionRepository->find($convectionId);
            return response()->json(['message' => 'Convection retrieved successfully', 'data' => $convection], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Convection. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Update convection by ID.
     *
     * @OA\Put(
     *     path="/api/auth/convection/u/{id}",
     *     summary="Update convection by ID",
     *     operationId="updateConvection",
     *     tags={"Convection"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the convection to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Convection Name"),
     *             @OA\Property(property="address", type="string", example="Updated Convection Address"),
     *             @OA\Property(property="phone_number", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Convection updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Convection updated successfully"),
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
     *         description="Validation error or failed to update convection",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $convectionId UUID of the convection to update
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $convectionId)
    {
        try {
            $convection = $this->convectionRepository->find($convectionId);

            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'address' => 'nullable|string',
                'phone_number' => 'nullable|string|min:8|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $data = [
                'name' => $request->input('name') ?? $convection->name,
                'address' => $request->input('address') ?? $convection->address,
                'phone_number' => $request->input('phone_number') ?? $convection->phone_number,
            ];

            $this->convectionRepository->update($convectionId, $data);

            $updatedConvection = $this->convectionRepository->find($convectionId);

            return response()->json(['message' => 'Convection updated successfully', 'data' => $updatedConvection], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Convection. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Ban convection by ID.
     *
     * @OA\Post(
     *     path="/api/auth/convection/ban/{id}",
     *     summary="ban convection by ID and bulk ban user with convection_id",
     *     operationId="banConvection",
     *     tags={"Convection"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the convection to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Convection banned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="convection banned successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to banned Convection",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $id UUID of the convection to ban
     * @return \Illuminate\Http\JsonResponse
     */
    public function banConvection(string $id)
    {
        try {
            $users = User::where('convection_id', $id)->get();

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
                $this->convectionRepository->banConvection($id);

                $convection = $this->convectionRepository->find($id);
                $convection->status = 'suspend';
                $convection->save();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal melarang convection: ' . $e->getMessage()], 500);
            }

            return response()->json(['message' => 'convection berhasil dibanned.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal banned convection: ' . $e->getMessage()], 500);
        }
    }


    /**
     * UnBan convection by ID.
     *
     * @OA\Post(
     *     path="/api/auth/convection/unban/{id}",
     *     summary="unBan convection by ID and bulk unBan user with convection_id",
     *     operationId="unbanConvection",
     *     tags={"Convection"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the convection to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Convection unBanned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="convection unbanned successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to unbanned Convection",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $id UUID of the convection to unban
     * @return \Illuminate\Http\JsonResponse
     */
    public function unBanConvection(Request $request, $id)
    {
        try {
            $users = User::where('convection_id', $id)->get();

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
                $this->convectionRepository->unBanConvection($id);

                $convection = $this->convectionRepository->find($id);
                $convection->status = 'active';
                $convection->save();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal membatalkan pelarangan convection: ' . $e->getMessage()], 500);
            }

            return response()->json(['message' => 'convection berhasil dipulihkan.']);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal memulihkan convection. Terjadi kesalahan database: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memulihkan convection: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete convection by ID.
     *
     * @OA\Delete(
     *     path="/api/auth/convection/d/{id}",
     *     summary="Delete convection by ID",
     *     operationId="deleteConvection",
     *     tags={"Convection"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the convection to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Convection deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="convection deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to delete Convection",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $convectionId UUID of the convection to delete
     * @return \Illuminate\Http\JsonResponse
     */
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

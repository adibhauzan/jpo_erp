<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Repositories\User\UserRepositoryInterface;

/**
 * @OA\Tag(
 *     name="User",
 *     description="Endpoints for user control"
 * )
 */
class UserController extends Controller
{

    private $userRepository;

    /**
     * Create a new AuthController instance.
     *
     * @param UserRepositoryInterface $userRepository
     * @return void
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
        $this->userRepository = $userRepository;
    }


    /**
     * @OA\Put(
     *     path="/api/auth/user/update/{id}",
     *     summary="Update user information",
     *     operationId="updateUser",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="roles", type="string", example="store or convection or superadmin"),
     *             @OA\Property(property="phone_number", type="string", example="92348943292"),
     *             @OA\Property(property="password", type="string", example="userupdated"),
     *             @OA\Property(property="store_id", type="string", example=""),
     *             @OA\Property(property="convection_id", type="string", example=""),
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to ban",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User information updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or failed to update user information",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $this->userRepository->find($id);
            $currentRoles = $user->roles;

            $validator = $this->validateUpdateRequest($request, $currentRoles);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $userData = [
                'roles' => $request->input('roles') ?? $currentRoles,
                'phone_number' => $request->input('phone_number') ?? $user->phone_number,
                'password' => $request->input('password') ?? $user->password,
            ];

            if ($currentRoles === 'convection' && $request->input('roles') === 'superadmin') {
                $userData['store_id'] = null;
                $userData['convection_id'] = null;
            }
            if ($request->input('roles') === 'superadmin') {
                $validator->sometimes('store_id', 'nullable', function ($input) {
                    return true;
                });
                $validator->sometimes('convection_id', 'nullable', function ($input) {
                    return true;
                });
            }

            $updatedUser = $this->userRepository->update($id, $userData);

            return response()->json(['message' => 'User information updated successfully', 'user' => $updatedUser], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user information. ' . $e->getMessage()], 422);
        }
    }

    protected function validateUpdateRequest(Request $request, $currentRoles)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'phone_number' => 'nullable|string|min:8|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
                'password' => ['nullable', 'string', 'min:8', Password::defaults()],
                'store_id' => ($currentRoles === 'superadmin' && $request->input('roles') !== 'superadmin') ? 'nullable' : ($request->input('roles') === 'store' ? 'required' : 'nullable'),
                'convection_id' => ($currentRoles === 'superadmin' && $request->input('roles') !== 'superadmin') ? 'nullable' : ($request->input('roles') === 'convection' ? 'required' : 'nullable'),
                'roles' => ['nullable', Rule::in(['superadmin', 'store', 'convection'])],
            ]
        );

        $validator->after(function ($validator) use ($request, $currentRoles) {
            $roles = $request->input('roles');
            $store_id = $request->input('store_id');
            $convection_id = $request->input('convection_id');

            // Pengecekan untuk roles superadmin
            $this->validateSuperAdminRoles($validator, $roles, $store_id, $convection_id, $currentRoles);

            // Pengecekan untuk roles convection
            $this->validateConvectionRoles($validator, $roles, $store_id, $convection_id, $currentRoles);

            // Pengecekan untuk roles store
            $this->validateStoreRoles($validator, $roles, $store_id, $convection_id, $currentRoles);
        });

        return $validator;
    }

    protected function validateSuperAdminRoles($validator, $roles, $store_id, $convection_id, $currentRoles)
    {
        if ($currentRoles === 'superadmin' || $roles === 'superadmin') {
            if ($store_id !== null || $convection_id !== null) {
                $validator->errors()->add('roles', 'Roles superadmin tidak boleh menggunakan store_id atau convection_id.');
            }

            // Pengecualian untuk roles superadmin saat ini dan roles yang diminta juga superadmin
            if ($currentRoles === 'superadmin' && $roles === 'superadmin') {
                $validator->sometimes('convection_id', 'nullable', function ($input) {
                    return true;
                });
            }
        }
    }

    protected function validateConvectionRoles($validator, $roles, $store_id, $convection_id, $currentRoles)
    {
        if ($currentRoles === 'convection' && $roles !== 'convection') {
            if ($store_id !== null) {
                $validator->errors()->add('store_id', 'Roles convection tidak boleh menggunakan store_id.');
            }
            if ($convection_id === null) {
                $validator->errors()->add('convection_id', 'convection_id is required.');
            }
        }
    }

    protected function validateStoreRoles($validator, $roles, $store_id, $convection_id, $currentRoles)
    {
        if ($currentRoles === 'store' && $roles !== 'store') {
            if ($convection_id !== null) {
                $validator->errors()->add('convection_id', 'Roles store tidak boleh menggunakan convection_id.');
            }
            if ($store_id === null) {
                $validator->errors()->add('store_id', 'store_id is required.');
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/users/",
     *     summary="Get all users",
     *     operationId="indexUser",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="success fetch Users",
     *         @OA\JsonContent(
     *             @OA\Property(property="users", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to fetch users",
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
            $users = $this->userRepository->findAll();

            return response()->json(['Message' => 'success fetch Users', 'users' => $users], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch users. ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/user/{id}",
     *     summary="find user",
     *     operationId="findUser",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="success find user",
     *         @OA\JsonContent(
     *             @OA\Property(property="users", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="user not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to find",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  String $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(string $id)
    {
        try {
            $user = $this->userRepository->find($id);
            return response()->json(['message' => 'success find user', 'user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }

    public function findByParameters(Request $request)
    {
        try {
            $parameters = $request->all();
            $users = $this->userRepository->findByParameters($parameters);
            return response()->json(['users' => $users], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch users. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ban user.
     *
     * @OA\Post(
     *     path="/api/auth/user/ban/{id}",
     *     summary="Ban user",
     *     operationId="banUser",
     *     tags={"User"},
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
     *         description="Failed to ban user",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to ban",
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
    public function banUser(Request $request, $id)
    {
        try {
            $this->userRepository->banUser($id);
            $user = $this->userRepository->find($id);
            $user->status = 'suspend';
            $user->save();
            return response()->json(['message' => 'User berhasil dibanned.']);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal memban pengguna. Terjadi kesalahan database: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memban pengguna: ' . $e->getMessage()], 500);
        }
    }

    /**
     * UnBan user.
     *
     * @OA\Post(
     *     path="/api/auth/user/unban/{id}",
     *     summary="UnBan user",
     *     operationId="unBanUser",
     *     tags={"User"},
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
     *         description="Failed to ban user",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to ban",
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
    public function unBanUser(Request $request, $id)
    {
        try {
            $user = $this->userRepository->find($id);
            $user->status = 'active';
            $user->save();
            $this->userRepository->unBanUser($id);
            return response()->json(['message' => 'User berhasil dipulihkan.']);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal memulihkan pengguna. Terjadi kesalahan database: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memulihkan pengguna: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/auth/user/d/{id}",
     *     summary="delete user",
     *     operationId="deleteUser",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="delete user succesfull",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="user not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to delete",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  \Illuminate\Http\Request  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $id)
    {

        try {
            $user = $this->userRepository->delete($id);
            return response()->json(['message' => 'delete user succesfull'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }
}
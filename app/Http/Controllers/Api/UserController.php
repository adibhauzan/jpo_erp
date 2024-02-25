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
     *             @OA\Property(property="name", type="string", example="user updated"),
     *             @OA\Property(property="roles", type="string", example="store or convection"),
     *             @OA\Property(property="phone_number", type="string", example="92348943292"),
     *             @OA\Property(property="username", type="string", example="userupdated"),
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
        $validator = $this->validateUpdateRequest($request);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $userData = [
                'name' => $request->input('name'),
                'roles' => $request->input('roles'),
                'phone_number' => $request->input('phone_number'),
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'store_id' => $request->input('store_id'),
                'convection_id' => $request->input('convection_id'),
            ];

            $updatedUser = $this->userRepository->update($id, $userData);

            return response()->json(['message' => 'User information updated successfully', 'user' => $updatedUser], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user information. ' . $e->getMessage()], 422);
        }
    }

    protected function validateUpdateRequest(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'nullable|string',
                'roles' => ['nullable', Rule::in(['superadmin', 'store', 'convection'])],
                'phone_number' => 'nullable|string|min:8|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
                'username' => 'nullable|string|max:255|unique:users',
                'password' => ['nullable', 'string', 'min:8', Password::defaults()],
                'store_id' => '',
                'convection_id' => '',
            ]
        );
        // if ($request->roles !== 'superadmin' && $request->roles !== 'convection') {
        //     $validator->sometimes('store_id', 'required', function ($input) {
        //         return $input->roles !== 'superadmin';
        //     });
        // }

        // if ($request->roles !== 'superadmin' && $request->roles !== 'store') {
        //     $validator->sometimes('convection_id', 'required', function ($input) {
        //         return $input->roles !== 'superadmin' && $input->roles !== 'store';
        //     });
        // }

        // if ($request->roles === 'superadmin'){
        //     $validator->sometimes()
        // }

        // Jika roles adalah 'superadmin', maka kedua ID menjadi nullable
        $validator->sometimes('store_id', 'nullable', function ($input) {
            return $input->roles === 'superadmin';
        });

        $validator->sometimes('convection_id', 'nullable', function ($input) {
            return $input->roles === 'superadmin';
        });

        // Jika roles adalah 'convection', maka 'convection_id' required, 'store_id' nullable
        $validator->sometimes('convection_id', 'required', function ($input) {
            return $input->roles === 'convection';
        });

        $validator->sometimes('store_id', 'nullable', function ($input) {
            return $input->roles === 'convection';
        });

        // Jika roles adalah 'store', maka 'store_id' required, 'convection_id' nullable
        $validator->sometimes('store_id', 'required', function ($input) {
            return $input->roles === 'store';
        });

        $validator->sometimes('convection_id', 'nullable', function ($input) {
            return $input->roles === 'store';
        });
        return $validator;
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

    // /**
    //  * Get users by roles.
    //  * 
    //  * @OA\Get(
    //  *     path="/api/auth/user/roles",
    //  *     summary="Get users by roles",
    //  *     operationId="getByRoles",
    //  *     tags={"User"},
    //  *     @OA\Parameter(
    //  *         name="token",
    //  *         in="query",
    //  *         required=true,
    //  *         description="Bearer token",
    //  *         @OA\Schema(type="string")
    //  *     ),
    //  *     @OA\Parameter(
    //  *         name="roles",
    //  *         in="query",
    //  *         required=true,
    //  *         description="Roles of the users",
    //  *         @OA\Schema(type="string")
    //  *     ),
    //  *     @OA\Response(
    //  *         response=200,
    //  *         description="List of users",
    //  *         @OA\JsonContent(
    //  *             type="array",
    //  *             @OA\Items(
    //  *                 @OA\Property(property="id", type="string"),
    //  *                 @OA\Property(property="roles", type="string"),
    //  *                 @OA\Property(property="phone_number", type="string"),
    //  *                 @OA\Property(property="username", type="string"),
    //  *                 @OA\Property(property="name", type="string"),
    //  *                 @OA\Property(property="status", type="string", example="active"),
    //  *                 @OA\Property(property="created_at", type="string"),
    //  *                 @OA\Property(property="updated_at", type="string"),
    //  *             )
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response=500,
    //  *         description="Failed to fetch users by roles",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="error", type="string")
    //  *         )
    //  *     ),
    //  *     security={{"bearerAuth": {}}}
    //  * )
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function getByRoles(Request $request)
    // {
    //     try {
    //         $roles = $request->query('roles');
    //         $users = $this->userRepository->findByRoles($roles);
    //         return response()->json(['users' => $users], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to fetch users by roles. ' . $e->getMessage()], 500);
    //     }
    // }

    // public function getByStatus(Request $request)
    // {
    //     try {
    //         $roles = $request->query('status');
    //         $users = $this->userRepository->findByStatus($roles);
    //         return response()->json(['users' => $users], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to fetch users by status. ' . $e->getMessage()], 500);
    //     }
    // }

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

    public function find(string $userId)
    {
        try {
            $user = $this->userRepository->find($userId);
            return response()->json(['user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }
}
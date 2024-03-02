<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Convection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Repositories\User\UserRepositoryInterface;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for user authentication"
 * )
 *
 */
class AuthController extends Controller
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
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register a new user",
     *     operationId="register",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", default="user1"),
     *             @OA\Property(property="phone_number", type="string", default="123456781"),
     *             @OA\Property(property="username", type="string", default="useruser1"),
     *             @OA\Property(
     *                 property="roles",
     *                 type="string",
     *                 enum={"superadmin", "store", "convection"},
     *                 default="store"
     *             ),
     *             @OA\Property(property="password", type="string", default="123456781"),
     *             @OA\Property(property="store_id", type="string", default=""),
     *             @OA\Property(property="convection_id", type="string", default=""),
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="User registered successfully",
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
    public function register(Request $request)
    {
        $validator = $this->validateUserRequest($request);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $userData = [
                'store_id' => $request->input('store_id'),
                'convection_id' => $request->input('convection_id'),
                'name' => $request->input('name'),
                'roles' => $request->input('roles'),
                'phone_number' => $request->input('phone_number'),
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ];

            $user = $this->userRepository->create($userData);

            return response()->json(['data' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create User. ' . $e->getMessage()], 422);
        }
    }


    protected function validateUserRequest(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'roles' => ['required', Rule::in(['superadmin', 'store', 'convection'])],
                'phone_number' => 'required|string|min:8|max:15|unique:users,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => ['required', 'string', 'min:8', Password::defaults()],
                'store_id' => function ($attribute, $value, $fail) use ($request) {
                    if ($request->roles !== 'superadmin' && $request->roles !== 'store' && $value !== null) {
                        $fail('The store_id field is not allowed for this role.');
                    }
                    if ($request->roles === 'store' && $value === null) {
                        $fail('The store_id field is required for this role.');
                    }
                    if ($value !== null && !Store::where('id', $value)->exists()) {
                        $fail('The selected store_id is invalid.');
                    }
                },
                'convection_id' => function ($attribute, $value, $fail) use ($request) {
                    if ($request->roles !== 'superadmin' && $request->roles !== 'convection' && $value !== null) {

                        $fail('The convection_id field is not allowed for this role.');
                    }
                    if ($request->roles === 'convection' && $value === null) {
                        $fail('The convection_id field is required for this role.');
                    }
                    if ($value !== null && !Convection::where('id', $value)->exists()) {
                        $fail('The selected convection_id is invalid.');
                    }
                },
            ]
        );

        if ($request->roles === 'superadmin') {
            $validator->after(function ($validator) use ($request) {
                if ($request->has('store_id')) {
                    $validator->errors()->add('store_id', 'The store_id field is not allowed for this role.');
                } else if ($request->has('convection_id')) {
                    $validator->errors()->add('convection_id', 'The convection_id field is not allowed for this role.');
                }
            });
        }

        if ($request->roles !== 'superadmin' && $request->roles !== 'convection') {
            $validator->sometimes('store_id', 'required', function ($input) {
                return $input->roles !== 'superadmin';
            });
        }

        if ($request->roles !== 'superadmin' && $request->roles !== 'store') {
            $validator->sometimes('convection_id', 'required', function ($input) {
                return $input->roles !== 'superadmin' && $input->roles !== 'store';
            });
        }

        return $validator;
    }





    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="User login",
     *     operationId="login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", default="adminadmin1"),
     *             @OA\Property(property="password", type="string", default="adminadmin1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string"),
     *             @OA\Property(property="expires_in", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $user = User::where('username', $credentials['username'])->first();
        if ($user && $user->isBanned()) {
            return response()->json(['error' => 'Anda telah dilarang akses ke aplikasi ini.'], 403);
        }
        if (!$token = $this->userRepository->attemptLogin($credentials)) {
            return response()->json(['error' => 'incorrect input'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @OA\Post(
     *     path="/api/auth/me",
     *     summary="Get the authenticated user",
     *     operationId="me",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response="200",
     *         description="Return the authenticated user",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string"),
     *             @OA\Property(property="roles", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="status", type="active"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function me()
    {
        return response()->json(auth()->user());
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh the access token",
     *     operationId="refresh",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response="200",
     *         description="Access token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string"),
     *             @OA\Property(property="expires_in", type="integer"),
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout and invalidate the token",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response="200",
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}

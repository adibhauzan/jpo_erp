<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Repositories\User\UserRepositoryInterface;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for user authentication"
 * )
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
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="token",
     *         in="query",
     *         required=true,
     *         description="Bearer token",
     *         @OA\Schema(type="string")
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
     *     )
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'roles' => ['required', Rule::in(['superadmin', 'store', 'convection'])],
            'phone_number' => 'required|string|min:8|max:15|unique:users,phone_number|regex:/^([0-9\s\-\+\(\)]*)$/',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => ['required', 'string', 'min:8', Password::defaults()],
        ]
        // , [
        //     'name.required' => 'The name field is required.',
        //     'name.string' => 'The name must be a string.',
        //     'roles.required' => 'The roles field is required.',
        //     'roles.in' => 'The selected role is invalid.',
        //     'phone_number.required' => 'The phone number field is required.',
        //     'phone_number.string' => 'The phone number must be a string.',
        //     'phone_number.min' => 'The phone number must be at least :min characters.',
        //     'phone_number.max' => 'The phone number may not be greater than :max characters.',
        //     'phone_number.regex' => 'The phone number format is invalid.',
        //     'username.required' => 'The username field is required.',
        //     'username.string' => 'The username must be a string.',
        //     'username.max' => 'The username may not be greater than :max characters.',
        //     'username.unique' => 'The username has already been taken.',
        //     'password.required' => 'The password field is required.',
        //     'password.string' => 'The password must be a string.',
        //     'password.min' => 'The password must be at least :min characters.',
        // ]
    );
    
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
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         required=true,
     *         description="Bearer token",
     *         @OA\Schema(type="string")
     *     )
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
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         required=true,
     *         description="Bearer token",
     *         @OA\Schema(type="string")
     *     )
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
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         required=true,
     *         description="Bearer token",
     *         @OA\Schema(type="string")
     *     )
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
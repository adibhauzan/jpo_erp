<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Contact\ContactRepositoryInterface;

/**
 * @OA\Tag(
 *     name="Contact",
 *     description="Endpoints for Contact"
 * )
 */
class ContactController extends Controller
{
    private $contactRepository;

    /**
     * Create a new ContactController instance.
     *
     * @param ContactRepositoryInterface $contactRepository
     * @return void
     */
    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/contact",
     *     summary="Create a new Contact",
     *     operationId="createContact",
     *     tags={"Contact"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", default="contact1"),
     *             @OA\Property(property="address", type="string", default="contact1 bandung"),
     *             @OA\Property(property="phone_number", type="string", default="12345677821"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description= "success create new Contact",
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
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'phone_number' => 'required|string|min:8|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
                'address' => 'required',

            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        try {
            $contactData = [
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'phone_number' => $request->input('phone_number'),
            ];

            $contact = $this->contactRepository->create($contactData);

            return response()->json(['Message' => 'success create new Contact', 'data' => $contact], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create Contact. ' . $e->getMessage()], 422);
        }
    }

    /**
     *
     * Get All Contact
     *
     * @OA\Get(
     *     path="/api/auth/contacts/",
     *     summary="Get all contacts",
     *     operationId="indexcontact",
     *     tags={"Contact"},
     *     @OA\Response(
     *         response=200,
     *         description= "success fetch Contacts",
     *         @OA\JsonContent(
     *             @OA\Property(property="contacts", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to fetch Contacts.",
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
            $contacts = $this->contactRepository->findAll();

            return response()->json(['Message' => 'success fetch Contacts', 'data' => $contacts], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Contacts. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get contact by id.
     *
     * @OA\Get(
     *     path="/api/auth/contact/{id}",
     *     summary="Get Contact by id.",
     *     operationId="showContact",
     *     tags={"Contact"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Contact",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to find Contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $contactId UUID of the contact
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $contactId)
    {
        try {
            $contact = $this->contactRepository->find($contactId);
            return response()->json(['message' => 'Contact retrieved successfully', 'data' => $contact], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Contact. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Update Contact by ID.
     *
     * @OA\Put(
     *     path="/api/auth/contact/u/{id}",
     *     summary="Update Contact by ID",
     *     operationId="updateContact",
     *     tags={"Contact"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Contact to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated contact Name"),
     *             @OA\Property(property="address", type="string", example="Updated contact Address"),
     *             @OA\Property(property="phone_number", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Contact updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="phone_number", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or failed to update Contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $contactId UUID of the contact to update
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $contactId)
    {
        try {
            $contact = $this->contactRepository->find($contactId);

            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'address' => 'nullable|string',
                'phone_number' => 'nullable|string|min:8|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $data = [
                'name' => $request->input('name') ?? $contact->name,
                'address' => $request->input('address') ?? $contact->address,
                'phone_number' => $request->input('phone_number') ?? $contact->phone_number,
            ];

            $this->contactRepository->update($contactId, $data);

            $updatedcontact = $this->contactRepository->find($contactId);

            return response()->json(['message' => 'Contact updated successfully', 'data' => $updatedcontact], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update Contact. ' . $e->getMessage()], 422);
        }
    }

    /**
     * Delete Contact by ID.
     *
     * @OA\Delete(
     *     path="/api/auth/contact/d/{id}",
     *     summary="Delete Contact by ID",
     *     operationId="deleteContact",
     *     tags={"Contact"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Contact to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Contact deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Failed to delete Contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param  string  $contactId UUID of the contact to delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $contactId)
    {
        try {
            $contact = $this->contactRepository->delete($contactId);
            return response()->json(['message' => 'contact deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete contact. ' . $e->getMessage()], 422);
        }
    }
}

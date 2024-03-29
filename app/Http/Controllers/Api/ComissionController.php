<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Commision\CommisionRepositoryInterface;

class ComissionController extends Controller
{
    private $commisionRepository;


    /**
     * Create a new commisionController instance.
     *
     * @param CommisionRepositoryInterface $commisionRepository
     * @return void
     */
    public function __construct(CommisionRepositoryInterface $commisionRepository)
    {
        $this->commisionRepository = $commisionRepository;
    }

    public function index(Request $request)
    {
        try {
            $commisions = $this->commisionRepository->findAll();

            return response()->json(['code' => 200, 'Message' => 'success fetch commisions', 'data' => $commisions], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch commisions. ' . $e->getMessage()], 500);
        }
    }

    public function show(string $commisionId)
    {
        try {
            $commision = $this->commisionRepository->find($commisionId);
            return response()->json(['message' => 'commision retrieved successfully', 'data' => $commision], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve commision. ' . $e->getMessage()], 422);
        }
    }

    public function pay(Request $request, string $commisionId)
    {
        try {

            $commision = $this->commisionRepository->find($commisionId);
            $validator = Validator::make($request->all(), [
                'paid_price' => 'required|integer',
                'nama_bank' => 'required',
                'nama_rekening' => 'required',
                'no_rekening' => 'required|string|min:10|max:17|regex:/^([0-9\s\-\+\(\)]*)$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $paid_price = $request->input('paid_price', $commision->paid_price);
            $nama_bank = $request->input('nama_bank', $commision->nama_bank);
            $nama_rekening = $request->input('nama_rekening', $commision->nama_rekening);
            $no_rekening = $request->input('no_rekening', $commision->no_rekening);

            $this->commisionRepository->pay($commisionId, $paid_price, $nama_bank, $nama_rekening, $no_rekening);

            return response()->json(["code" => 200, "Message" => "Success Pay Bill Price"], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}

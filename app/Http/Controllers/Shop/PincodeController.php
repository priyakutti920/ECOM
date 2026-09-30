<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Pincode\PincodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PincodeController extends Controller
{
    public function __construct(
        protected PincodeService $pincodeService
    ) {}

    /**
     * Check pincode serviceability and estimated delivery ETA.
     * GET /api/pincode/check?pincode=600001
     */
    public function check(Request $request): JsonResponse
    {
        $pincode = (string) $request->query('pincode', '');
        $weight = (float) $request->query('weight', 0.5);
        $isCod = $request->boolean('cod');

        $result = $this->pincodeService->check($pincode, $weight, $isCod);

        $status = ($result['success'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
    }
}

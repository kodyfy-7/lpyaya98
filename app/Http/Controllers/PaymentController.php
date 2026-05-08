<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiatePaymentRequest;
use App\Services\FlutterwavePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly FlutterwavePaymentService $paymentService
    ) {}

    public function initiatePayment(InitiatePaymentRequest $request)
    {
        $user = $request->user()->load('member'); // always from auth, never from input

        Log::info('initiatePayment: request received', [
            'user_id' => $user->id,
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'amount' => $request->input('amount'),
        ]);

        $result = $this->paymentService->initiatePayment($user, $request->validated());

        $statusCode = $result['status_code'];
        unset($result['status_code']); // don't expose internal field in JSON

        return response()->json($result, $statusCode);
    }

    public function flutterwaveWebhook(Request $request)
    {
        $result = $this->paymentService->handleWebhook($request);
        $statusCode = $result['status_code'];
        unset($result['status_code']);

        return response()->json($result, $statusCode);
    }
}

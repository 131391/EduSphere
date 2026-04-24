<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\OnlineTransaction;
use App\Models\PaymentMethod;
use App\Services\PaymentGateways\RazorpayGateway;
use App\Services\School\FeePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    public function initiate(Request $request, $fee_id)
    {
        $fee = Fee::with('student')->findOrFail($fee_id);

        if ($fee->due_amount <= 0) {
            return back()->with('error', 'This fee is already paid.');
        }

        try {
            $gateway = new RazorpayGateway();
            
            // Create a unique internal receipt id for the gateway
            $internalReceiptId = 'RC_ONL_' . time() . '_' . $fee->id;

            $order = $gateway->createOrder(
                amount: $fee->due_amount,
                currency: 'INR',
                receiptId: $internalReceiptId,
                metadata: [
                    'fee_id' => $fee->id,
                    'student_id' => $fee->student_id
                ]
            );

            // Record the pending transaction
            OnlineTransaction::create([
                'school_id' => $fee->school_id,
                'student_id' => $fee->student_id,
                'fee_id' => $fee->id,
                'amount' => $fee->due_amount,
                'gateway_name' => 'razorpay',
                'gateway_order_id' => $order['order_id'],
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $order['order_id'],
                'amount' => $order['amount'],
                'key' => config('services.razorpay.key'),
                'name' => $fee->school->name ?? 'EduSphere School',
                'description' => $fee->feeName->name . ' Payment',
                'student_name' => $fee->student->full_name,
                'email' => $fee->student->email ?? '',
                'contact' => $fee->student->mobile_no ?? ''
            ]);

        } catch (Exception $e) {
            Log::error("Payment Initiation Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not initiate payment. Please try again later.'], 500);
        }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
            'fee_id' => 'required|exists:fees,id'
        ]);

        $gateway = new RazorpayGateway();

        $isValid = $gateway->verifySignature([
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature
        ]);

        if (!$isValid) {
            Log::warning("Payment signature verification failed for Order ID: " . $request->razorpay_order_id);
            return response()->json(['success' => false, 'message' => 'Payment verification failed.']);
        }

        try {
            DB::beginTransaction();

            $transaction = OnlineTransaction::where('gateway_order_id', $request->razorpay_order_id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                DB::rollBack();
                return response()->json(['success' => true, 'message' => 'Payment already processed.']);
            }

            $fee = Fee::findOrFail($transaction->fee_id);

            // Fetch or create an 'Online' PaymentMethod for the school
            $paymentMethod = PaymentMethod::firstOrCreate(
                ['school_id' => $fee->school_id, 'name' => 'Online Gateway'],
                ['is_active' => true, 'requires_reference' => true]
            );

            // Use our existing atomic Service
            $paymentService = new FeePaymentService();
            $receiptNo = $paymentService->recordPayment(
                schoolId: $fee->school_id,
                studentId: $fee->student_id,
                paymentMethodId: $paymentMethod->id,
                payments: [
                    [
                        'fee_id' => $fee->id,
                        'amount' => $transaction->amount
                    ]
                ],
                paymentDate: now()->format('Y-m-d'),
                referenceNumber: $request->razorpay_payment_id,
                remarks: "Online payment via Razorpay",
                creatorId: null // or parent user id
            );

            // Mark transaction as success
            $transaction->update([
                'status' => 'success',
                'gateway_transaction_id' => $request->razorpay_payment_id,
                'payload' => $request->all()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment successful!',
                'receipt_no' => $receiptNo
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Payment Verification/Processing Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while saving the payment.']);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\PaymentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PaymentController extends Controller
{
    protected $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Get all payments
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['status', 'payment_method', 'order_id']);
            $payments = $this->paymentRepository->getAllPayments($filters);

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $payments
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new payment for an order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'nullable|numeric|min:0',
        ], [
            'order_id.required' => 'Order ID wajib diisi',
            'order_id.exists' => 'Order tidak ditemukan',
            'amount.numeric' => 'Jumlah pembayaran harus berupa angka',
            'amount.min' => 'Jumlah pembayaran tidak boleh negatif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->paymentRepository->initiatePayment(
                $request->order_id,
                $request->all()
            );

            // Check if result is array (from PaymentRepository)
            if (is_array($result)) {
                if ($result['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => $result['message'],
                        'invoice_url' => $result['url'] ?? null,
                    ], 201);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'],
                    ], 400);
                }
            }

            // Fallback for Payment object
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dibuat',
                'data' => $result->load('order')
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment detail by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $payment = $this->paymentRepository->getPaymentById($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail pembayaran berhasil diambil',
                'data' => $payment
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update payment status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:PENDING,PAID,EXPIRED,FAILED,CANCELLED,REFUNDED',
        ], [
            'status.required' => 'Status pembayaran wajib diisi',
            'status.in' => 'Status pembayaran tidak valid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payment = $this->paymentRepository->updatePaymentStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diupdate',
                'data' => $payment->load('order')
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund for a payment
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function refund(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ], [
            'amount.required' => 'Jumlah refund wajib diisi',
            'amount.numeric' => 'Jumlah refund harus berupa angka',
            'amount.min' => 'Jumlah refund tidak boleh negatif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payment = $this->paymentRepository->refundPayment($id, $request->amount);

            return response()->json([
                'success' => true,
                'message' => 'Refund berhasil diproses',
                'data' => $payment->load('order')
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses refund',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle Xendit webhook callback
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function xenditCallback(Request $request)
    {
        try {
            // Validate Xendit callback token (implement this based on your Xendit settings)
            // $this->validateXenditCallback($request);

            $payment = $this->paymentRepository->handleXenditCallback($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Callback berhasil diproses',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses callback',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get payment by external ID
     *
     * @param string $externalId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByExternalId($externalId)
    {
        try {
            $payment = $this->paymentRepository->getPaymentByExternalId($externalId);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail pembayaran berhasil diambil',
                'data' => $payment->load('order')
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate Xendit callback (implement based on your needs)
     *
     * @param Request $request
     * @throws Exception
     */
    private function validateXenditCallback(Request $request)
    {
        // Implement Xendit callback validation
        // Check callback token, signature, etc.
        // Throw exception if validation fails
    }

    /**
     * Show checkout page
     * GET /checkout/{externalId}
     *
     * @param string $externalId
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function showCheckout($externalId)
    {
        try {
            $payment = $this->paymentRepository->getPaymentByExternalId($externalId);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment tidak ditemukan',
                ], 404);
            }

            // Check if payment is expired
            if ($payment->status === 'EXPIRED' || $payment->status === 'PAID') {
                return view('checkout.expired', compact('payment'));
            }

            // Return checkout view with payment data
            return view('checkout.index', compact('payment'));
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

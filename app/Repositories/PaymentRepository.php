<?php

namespace App\Repositories;

use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Payment;
use App\Models\Orders;
use Exception;

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * Initiate a new payment for an order
     *
     * @param int $orderId
     * @param array $paymentData
     * @return Payment
     * @throws Exception
     */

    public function __construct()
    {
        Configuration::setXenditKey('xnd_development_4i8RpzsT9GMq1O7pLXFKTOeYSwGXXemOH95NzKRMlHW0retLm4XgZH151AnWd2N');
    }

    public function initiatePayment(int $orderId, array $paymentData)
    {
        try {
            $order = Orders::with('payment')->findOrFail($orderId);

            if ($order->payment && in_array($order->payment->status, ['PAID', 'SETTLED'])) {
                return ['success' => false, 'message' => 'Order sudah dibayar.'];
            }

            $amount = (int) ($paymentData['amount'] ?? $order->total_amount);
            $externalId = 'INV-ORD-' . $orderId . '-' . time();

            $api = new InvoiceApi();
            $req = new CreateInvoiceRequest([
                'external_id'         => $externalId,
                'amount'              => $amount,
                'description'         => "Pembayaran order #{$orderId}",
                'success_redirect_url' => url("/payments/success/{$orderId}"),
                'failure_redirect_url' => url("/payments/failed/{$orderId}"),
            ]);

            $invoice = $api->createInvoice($req);

            Payment::create([
                'order_id'    => $orderId,
                'external_id' => $invoice['external_id'],
                'invoice_url' => $invoice['invoice_url'],
                'amount'      => $invoice['amount'],
                'status'      => $invoice['status'],
                'expires_at'  => $invoice['expiry_date'] ?? null,
            ]);

            return ['success' => true, 'message' => 'Invoice berhasil dibuat.', 'url' => $invoice['invoice_url']];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Gagal membuat invoice: ' . $e->getMessage()];
        }
    }


    /**
     * Get payment by ID
     *
     * @param int $paymentId
     * @return Payment
     */
    public function getPaymentById(int $paymentId)
    {
        return Payment::with('order')->findOrFail($paymentId);
    }

    /**
     * Update payment status
     *
     * @param int $paymentId
     * @param string $status
     * @return Payment
     */
    public function updatePaymentStatus(int $paymentId, string $status)
    {
        $payment = Payment::findOrFail($paymentId);

        switch ($status) {
            case 'PAID':
                $payment->markAsPaid();
                break;
            case 'EXPIRED':
                $payment->markAsExpired();
                break;
            case 'FAILED':
                $payment->markAsFailed();
                break;
            case 'CANCELLED':
                $payment->markAsCancelled();
                break;
            default:
                $payment->update(['status' => $status]);
        }

        return $payment->fresh();
    }

    /**
     * Process refund for a payment
     *
     * @param int $paymentId
     * @param float $amount
     * @return Payment
     * @throws Exception
     */
    public function refundPayment(int $paymentId, float $amount)
    {
        $payment = Payment::findOrFail($paymentId);

        if (!$payment->isPaid()) {
            throw new Exception('Hanya payment yang sudah dibayar yang bisa di-refund');
        }

        if ($amount > $payment->amount) {
            throw new Exception('Jumlah refund tidak boleh melebihi jumlah pembayaran');
        }

        $payment->update([
            'status' => 'REFUNDED',
            'notes' => ($payment->notes ?? '') . "\nRefund: " . number_format($amount, 2),
        ]);

        // Update order status
        if ($payment->order) {
            $payment->order->update([
                'is_paid' => false,
                'status' => 'refunded',
            ]);
        }

        return $payment->fresh();
    }

    /**
     * Get all payments with optional filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPayments(array $filters = [])
    {
        $query = Payment::with('order');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }


        if (isset($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Get payment by external ID
     *
     * @param string $externalId
     * @return Payment|null
     */
    public function getPaymentByExternalId(string $externalId)
    {
        return Payment::where('external_id', $externalId)->first();
    }

    /**
     * Get payments by order ID
     *
     * @param int $orderId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPaymentsByOrderId(int $orderId)
    {
        return Payment::where('order_id', $orderId)->get();
    }

    /**
     * Update payment with Xendit response
     *
     * @param int $paymentId
     * @param array $xenditResponse
     * @return Payment
     */
    public function updateXenditResponse(int $paymentId, array $xenditResponse)
    {
        $payment = Payment::findOrFail($paymentId);

        $payment->update([
            'xendit_invoice_id' => $xenditResponse['id'] ?? null,
            'invoice_url' => $xenditResponse['invoice_url'] ?? null,
            'xendit_response' => $xenditResponse,
        ]);

        return $payment->fresh();
    }

    /**
     * Handle Xendit callback
     *
     * @param array $callbackData
     * @return Payment
     * @throws Exception
     */
    public function handleXenditCallback(array $callbackData)
    {
        $payment = $this->getPaymentByExternalId($callbackData['external_id']);

        if (!$payment) {
            throw new Exception('Payment tidak ditemukan');
        }

        $payment->update([
            'callback_data' => $callbackData,
        ]);

        // Update status based on callback
        if (isset($callbackData['status'])) {
            $status = strtoupper($callbackData['status']);
            $this->updatePaymentStatus($payment->id, $status);
        }

        return $payment->fresh();
    }
}

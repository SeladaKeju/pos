<?php

namespace App\Interfaces;

interface PaymentRepositoryInterface
{
    public function initiatePayment(int $orderId, array $paymentData);
    public function getPaymentById(int $paymentId);
    public function updatePaymentStatus(int $paymentId, string $status);
    public function refundPayment(int $paymentId, float $amount);

    public function getAllPayments(array $filters = []);
    public function handleXenditCallback(array $callbackData);
    public function getPaymentsByOrderId(int $orderId);
    public function getPaymentByExternalId(string $externalId);
}
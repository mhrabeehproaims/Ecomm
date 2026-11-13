<?php

namespace App\Services\Storefront;

interface PaymentGateway
{
    public function createOrder(array $data): array;

    public function order(string $id): array;

    public function payment(string $id): array;

    public function orderPayments(string $id): array;

    public function findOrder(string $receipt): ?array;

    public function createRefund(string $payment, array $data): array;

    public function refund(string $id): array;
}

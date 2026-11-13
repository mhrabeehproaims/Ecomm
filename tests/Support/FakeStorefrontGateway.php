<?php

namespace Tests\Support;

use App\Services\Storefront\PaymentGateway;

final class FakeStorefrontGateway implements PaymentGateway
{
    public array $orders = [];

    public array $payments = [];

    public array $refunds = [];

    public int $creations = 0;

    public int $refundCreations = 0;

    public bool $loseOrderResponse = false;

    public bool $loseRefundResponse = false;

    public function createOrder(array $data): array
    {
        $this->creations++;
        $order = ['id' => 'order_Test'.$this->creations, ...$data, 'status' => 'created', 'amount_paid' => 0, 'amount_due' => $data['amount']];
        $this->orders[$order['id']] = $order;
        if ($this->loseOrderResponse) {
            throw new \RuntimeException('Simulated response loss after provider creation');
        }

        return $order;
    }

    public function order(string $id): array
    {
        return $this->orders[$id];
    }

    public function payment(string $id): array
    {
        return $this->payments[$id];
    }

    public function orderPayments(string $id): array
    {
        return array_values(array_filter($this->payments, fn ($p) => $p['order_id'] === $id));
    }

    public function findOrder(string $receipt): ?array
    {
        foreach ($this->orders as $order) {
            if ($order['receipt'] === $receipt) {
                return $order;
            }
        }

        return null;
    }

    public function createRefund(string $payment, array $data): array
    {
        $this->refundCreations++;
        $row = ['id' => 'rfnd_Test'.$this->refundCreations, 'payment_id' => $payment, 'status' => 'pending', 'currency' => 'INR', ...$data];
        $this->refunds[$row['id']] = $row;
        if ($this->loseRefundResponse) {
            throw new \RuntimeException('Simulated lost refund response');
        }

        return $row;
    }

    public function refund(string $id): array
    {
        return $this->refunds[$id];
    }

    public function paymentState(string $providerOrder, string $status, string $payment = 'pay_Test1'): array
    {
        $o = &$this->orders[$providerOrder];
        if ($status === 'captured') {
            $o['status'] = 'paid';
            $o['amount_paid'] = $o['amount'];
            $o['amount_due'] = 0;
        }

        return $this->payments[$payment] = ['id' => $payment, 'order_id' => $providerOrder, 'status' => $status, 'captured' => $status === 'captured',
            'amount' => $o['amount'], 'currency' => 'INR', 'error_code' => $status === 'failed' ? 'BAD_REQUEST_ERROR' : null];
    }
}

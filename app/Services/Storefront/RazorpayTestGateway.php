<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\Http;

final class RazorpayTestGateway implements PaymentGateway
{
    private function request(string $method, string $path, array $data = []): array
    {
        CheckoutAccess::assertGateway();

        // Deliberately no retries on provider mutations: an HTTP timeout is ambiguous.
        return Http::withBasicAuth(config('storefront.razorpay_key'), config('storefront.razorpay_secret'))
            ->acceptJson()->connectTimeout(5)->timeout(15)->send($method, 'https://api.razorpay.com/v1/'.$path,
                $method === 'GET' ? ['query' => $data] : ['json' => $data])->throw()->json();
    }

    private function id(string $id): string
    {
        abort_unless(preg_match('/^(order|pay|rfnd)_[a-zA-Z0-9]+$/D', $id), 422, 'Invalid provider identifier.');

        return rawurlencode($id);
    }

    public function createOrder(array $data): array
    {
        return $this->request('POST', 'orders', $data);
    }

    public function order(string $id): array
    {
        return $this->request('GET', 'orders/'.$this->id($id));
    }

    public function payment(string $id): array
    {
        return $this->request('GET', 'payments/'.$this->id($id));
    }

    public function orderPayments(string $id): array
    {
        return $this->request('GET', 'orders/'.$this->id($id).'/payments')['items'] ?? [];
    }

    public function findOrder(string $receipt): ?array
    {
        $items = $this->request('GET', 'orders', ['receipt' => $receipt, 'count' => 100])['items'] ?? [];
        $matches = array_values(array_filter($items, fn ($item) => ($item['receipt'] ?? '') === $receipt));
        abort_if(count($matches) > 1, 409, 'Multiple provider orders require manual reconciliation.');

        return $matches[0] ?? null;
    }

    public function createRefund(string $payment, array $data): array
    {
        return $this->request('POST', 'payments/'.$this->id($payment).'/refund', $data);
    }

    public function refund(string $id): array
    {
        return $this->request('GET', 'refunds/'.$this->id($id));
    }

    public function paymentRefunds(string $payment): array
    {
        $all = [];
        for ($skip = 0; $skip < 10000; $skip += 100) {
            $page = $this->request('GET', 'payments/'.$this->id($payment).'/refunds', ['count' => 100, 'skip' => $skip]);
            abort_unless(is_array($page['items'] ?? null), 503, 'Refund collection could not be verified.');
            $all = [...$all, ...$page['items']];
            if (count($page['items']) < 100) {
                return $all;
            }
        }
        abort(409, 'Refund collection exceeds the reconciliation batch limit.');
    }
}

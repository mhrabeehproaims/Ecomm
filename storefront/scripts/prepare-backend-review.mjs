// Produces a review patch only. It never edits application source or applies the patch.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const folder = path.join(root, 'storefront/artifacts/backend-review');
fs.mkdirSync(folder, { recursive: true });
const changes = new Map();
function change(file, edit) { const before = fs.readFileSync(path.join(root, file), 'utf8').replace(/\r\n/g, '\n'); const after = edit(before); if (after === before) throw Error(`No proposal changes for ${file}`); changes.set(file, { before, after }); }
function replace(text, from, to) { if (!text.includes(from)) throw Error(`Expected source context missing: ${from.slice(0, 70)}`); return text.replace(from, to); }

change('app/Http/Controllers/Api/AuthController.php', text => replace(text, "        $user->update(['last_seen_at' => now()]);", "        if ((int) $user->status !== 1) {\n            return response()->json(['message' => 'Account is inactive'], 403);\n        }\n\n        $user->update(['last_seen_at' => now()]);"));
change('app/Http/Controllers/Api/DeliveryAddressController.php', text => {
  text = replace(text, "    public function update(DeliverAddressRequest $request, string $id)\n    {", "    public function update(DeliverAddressRequest $request, string $id)\n    {\n        if (!DeliveryAddress::where('customer_id', Auth::user()->customer_id)->where('address_id', $id)->exists()) {\n            return response()->json(['message' => 'Address not found'], 404);\n        }");
  text = replace(text, "DeliveryAddress::where('address_id', $id)->update($data);", "DeliveryAddress::where('customer_id', Auth::user()->customer_id)->where('address_id', $id)->update($data);");
  for (const [field, fallback] of [['email', 'null'], ['address_line2', 'null'], ['country', "'India'"], ['is_default', 'false']]) text = text.replaceAll(`$request->validated()['${field}']`, `($request->validated()['${field}'] ?? ${fallback})`);
  return text;
});
change('app/Http/Controllers/Api/CartController.php', text => {
  text = replace(text, '        $cartItem->price    = $product->default_price_value;', "        if ((int) $product->status !== 1 || $cartItem->quantity > $product->stock) {\n            return response()->json(['message' => 'Insufficient available stock'], 422);\n        }\n        $cartItem->price    = $product->default_price_value;");
  return replace(text, '        $count = $request->count ?? 1;', "        $request->validate(['count' => 'required|integer|min:1']);\n        $count = (int) $request->count;");
});
change('app/Http/Controllers/Api/PaymentController.php', text => {
  text = replace(text, "            $payment->update([\n                'razorpay_payment_id'", "            if ((int) $payment->user_id !== (int) Auth::user()->customer_id) {\n                return response()->json(['message' => 'Payment not found'], 404);\n            }\n            $providerPayment = $api->payment->fetch($request->razorpay_payment_id);\n            if ($providerPayment['order_id'] !== $payment->razorpay_order_id\n                || $providerPayment['status'] !== 'captured'\n                || $providerPayment['currency'] !== 'INR'\n                || (int) $providerPayment['amount'] !== (int) round($payment->amount * 100)) {\n                return response()->json(['message' => 'Payment capture has not been verified'], 409);\n            }\n\n            $payment->update([\n                'razorpay_payment_id'");
  const start = text.indexOf('    public function failed');
  const before = text.slice(0, start);
  const tail = replace(text.slice(start), "        $payment->update([", "        if ((int) $payment->user_id !== (int) Auth::user()->customer_id) {\n            return response()->json(['message' => 'Payment not found'], 404);\n        }\n        if ($payment->status === 'paid') {\n            return response()->json(['message' => 'A paid payment cannot be marked failed'], 409);\n        }\n\n        $payment->update([");
  return before + tail;
});
change('app/Http/Controllers/Api/SalesReturnController.php', text => {
  text = replace(text, 'use App\\Models\\OrderItem;', 'use App\\Models\\OrderItem;\nuse App\\Models\\Order;');
  text = replace(text, "'items.*.quantity' => 'required|numeric|min:1'", "'items.*.quantity' => 'required|integer|min:1'");
  return replace(text, '        $return = SalesReturn::create([', "        $order = Order::where('order_id', $data['order_id'])->where('customer_id', $customer->customer_id)->first();\n        if (!$order || $order->status !== 'delivered') {\n            return response()->json(['message' => 'Delivered order not found'], 404);\n        }\n        foreach ($data['items'] as &$line) {\n            $sold = OrderItem::where('order_id', $order->order_id)->where('product_id', $line['product_id'])->first();\n            if (!$sold) return response()->json(['message' => 'Order item not found'], 404);\n            $requested = SalesReturnItems::where('order_item_id', $sold->order_item_id)\n                ->whereHas('salesReturn', fn ($query) => $query->where('status', '!=', 'rejected'))->sum('quantity');\n            if ($line['quantity'] + $requested > $sold->quantity) {\n                return response()->json(['message' => 'Return quantity exceeds the remaining purchased quantity'], 422);\n            }\n            $line['price'] = $sold->price;\n        }\n        unset($line);\n\n        $return = SalesReturn::create([");
});
change('app/Http/Resources/OrderHistoryResource.php', text => replace(text, '$item->returned?->exists() ? true : false', '$item->returned !== null'));

let patch = '';
const sources = {};
for (const [file, { before, after }] of changes) {
  const a = path.join(folder, 'before.php'), b = path.join(folder, 'proposed.php');
  fs.writeFileSync(a, before); fs.writeFileSync(b, after);
  const lint = spawnSync('php', ['-l', b], { encoding: 'utf8' });
  if (lint.status !== 0) throw Error(lint.stdout + lint.stderr);
  const diff = spawnSync('git', ['diff', '--no-index', '--no-renames', '--', a, b], { encoding: 'utf8' });
  if (diff.status !== 1) throw Error(diff.stderr || 'Expected a review diff');
  patch += diff.stdout.split('\n').map(line => line.startsWith('diff --git ') ? `diff --git a/${file} b/${file}` : line.startsWith('--- ') ? `--- a/${file}` : line.startsWith('+++ ') ? `+++ b/${file}` : line).join('\n');
  sources[file] = createHash('sha256').update(fs.readFileSync(path.join(root, file))).digest('hex');
}
fs.mkdirSync(path.join(root, 'docs/approvals'), { recursive: true });
fs.writeFileSync(path.join(root, 'docs/approvals/customer-api-hardening.patch'), patch);
fs.writeFileSync(path.join(folder, 'source-hashes.json'), JSON.stringify(sources, null, 2));
console.log('Prepared six-file backend review patch; source files unchanged. Proposed PHP syntax checks passed.');

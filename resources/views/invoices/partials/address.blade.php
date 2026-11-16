@php $person = $document['customer']; $address = $document['address']; @endphp
<strong>{{ trim(($person['first_name'] ?? '').' '.($person['last_name'] ?? '')) ?: 'Recorded recipient name unavailable' }}</strong><br>
{{ $address['address_line1'] ?? '' }}<br>
@if(!empty($address['address_line2'])){{ $address['address_line2'] }}<br>@endif
{{ $address['city'] ?? '' }}, {{ $address['state'] ?? '' }} {{ $address['postal_code'] ?? '' }}<br>
{{ $address['country'] ?? '' }}<br>
Phone: {{ $address['phone'] ?? $person['phone'] ?? 'Not recorded' }}

@extends('public.layout')
@section('content')
<section class="wrap section">
    <h1>Your orders</h1>
    @forelse ($orders as $order)
        <article class="card" style="margin-bottom:.7rem;">
            <h3>{{ $order->number }}</h3>
            <p>{{ $order->status }} · due {{ $order->due_on?->toDateString() }} · {{ number_format($order->total, 2) }}</p>
        </article>
    @empty
        <p>No orders are linked to this account yet.</p>
    @endforelse
</section>
@endsection

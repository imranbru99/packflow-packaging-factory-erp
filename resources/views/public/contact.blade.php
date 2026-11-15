@extends('public.layout')
@section('content')
<section class="wrap section split">
    <div>
        <h1>Tell us the pack.</h1>
        <p class="lede">Size, board or film, colours, and quantity. Sales will turn it into a quotation on the same day.</p>
        <p>{{ $company->address }}, {{ $company->city }}<br>{{ $company->phone }}</p>
    </div>
    <form class="stack card" method="post" action="{{ route('site.quote') }}">
        @csrf
        @if (session('status'))<p>{{ session('status') }}</p>@endif
        <input name="company" placeholder="Company" value="{{ old('company') }}" required>
        <input name="contact_name" placeholder="Your name" value="{{ old('contact_name') }}" required>
        <input name="email" type="email" placeholder="Email" value="{{ old('email') }}" required>
        <input name="phone" placeholder="Phone" value="{{ old('phone') }}">
        <select name="pack_type">
            <option value="corrugated">Corrugated</option>
            <option value="flexible">Flexible</option>
            <option value="label">Label</option>
            <option value="carton">Mono carton</option>
        </select>
        <input name="quantity" type="number" placeholder="Quantity" value="{{ old('quantity') }}">
        <textarea name="specification" rows="5" placeholder="400 x 300 x 200, B flute, 3 colours">{{ old('specification') }}</textarea>
        <button class="btn" type="submit">Send to sales</button>
        @foreach ($errors->all() as $error)<small>{{ $error }}</small>@endforeach
    </form>
</section>
@endsection

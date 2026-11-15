@extends('public.layout')
@section('content')
<section class="wrap section">
    <h1>Work</h1>
    <div class="grid">
        @foreach ($studies as $study)
            <a class="card" href="{{ route('portfolio.show', $study) }}" style="text-decoration:none;">
                <div class="kicker">{{ $study->pack_type }}</div>
                <h3>{{ $study->title }}</h3>
                <p class="muted">{{ $study->client }} — {{ $study->summary }}</p>
            </a>
        @endforeach
    </div>
</section>
@endsection

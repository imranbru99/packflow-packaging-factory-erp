@extends('public.layout')
@section('content')
<section class="wrap section">
    <div class="kicker">{{ $study->client }}</div>
    <h1>{{ $study->title }}</h1>
    <p class="lede">{{ $study->summary }}</p>
    <div>{!! nl2br(e($study->body)) !!}</div>
</section>
@endsection

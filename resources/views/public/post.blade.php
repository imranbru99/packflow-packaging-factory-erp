@extends('public.layout')
@section('content')
<section class="wrap section">
    <h1>{{ app()->getLocale()==='bn' && $post->title_bn ? $post->title_bn : $post->title }}</h1>
    <div class="lede">{!! nl2br(e(app()->getLocale()==='bn' && $post->body_bn ? $post->body_bn : $post->body)) !!}</div>
</section>
@endsection

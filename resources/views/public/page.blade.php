@extends('public.layout')
@section('content')
<section class="wrap section">
    <h1>{{ app()->getLocale()==='bn' && $page->title_bn ? $page->title_bn : $page->title }}</h1>
    <div class="lede">{!! nl2br(e(app()->getLocale()==='bn' && $page->body_bn ? $page->body_bn : $page->body)) !!}</div>
</section>
@endsection

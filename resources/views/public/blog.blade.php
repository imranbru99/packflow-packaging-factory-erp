@extends('public.layout')
@section('content')
<section class="wrap section">
    <h1>Journal</h1>
    @foreach ($posts as $post)
        <article class="card" style="margin-bottom:.8rem;">
            <h2><a href="{{ route('blog.show', $post) }}" style="text-decoration:none;">{{ $post->title }}</a></h2>
            <p class="muted">{{ $post->excerpt }}</p>
        </article>
    @endforeach
    {{ $posts->links() }}
</section>
@endsection

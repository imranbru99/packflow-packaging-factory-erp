@extends('public.layout')
@section('content')
<section class="wrap hero">
    <div>
        <div class="kicker">{{ __('packflow.home.kicker') }}</div>
        <h1>{{ __('packflow.home.title') }}</h1>
        <p class="lede">{{ __('packflow.home.lede') }}</p>
        <p>
            <a class="btn" href="{{ route('site.contact') }}">{{ __('packflow.nav.quote') }}</a>
            <a class="btn alt" href="{{ route('catalog.index') }}">{{ __('packflow.nav.catalog') }}</a>
        </p>
    </div>
    <aside class="hero-card">
        <div>
            <div class="kicker" style="color:#e7c3a1">Floor route</div>
            <h2 style="color:#f3efe6;font-size:2rem;">Enquiry to invoice, without the paper chase.</h2>
        </div>
        <ol>
            <li>Quote from size, flute, film, and quantity</li>
            <li>Reserve reels and open the job card</li>
            <li>Print, laminate, slit, glue, inspect</li>
            <li>Dispatch and a bilingual invoice</li>
        </ol>
    </aside>
</section>
<section class="wrap stats">
    <div class="stat"><b>4</b><span class="muted">pack families</span></div>
    <div class="stat"><b>11</b><span class="muted">work centres</span></div>
    <div class="stat"><b>FIFO</b><span class="muted">reel tracking</span></div>
    <div class="stat"><b>EN / BN</b><span class="muted">floor and paperwork</span></div>
</section>
<section class="wrap section">
    <h2>{{ __('packflow.home.services') }}</h2>
    <div class="grid">
        @foreach ($categories as $category)
            <a class="card" href="{{ route('catalog.index', ['type' => $category->type]) }}" style="text-decoration:none;">
                <div class="kicker">{{ $category->type }}</div>
                <h3>{{ app()->getLocale() === 'bn' && $category->name_bn ? $category->name_bn : $category->name }}</h3>
                <p class="muted">{{ $category->summary }}</p>
            </a>
        @endforeach
    </div>
</section>
<section class="wrap section">
    <h2>{{ __('packflow.home.packs') }}</h2>
    <div class="grid">
        @foreach ($products as $product)
            <a class="card" href="{{ route('catalog.show', $product) }}" style="text-decoration:none;">
                <div class="kicker">{{ $product->sku }}</div>
                <h3>{{ app()->getLocale() === 'bn' && $product->name_bn ? $product->name_bn : $product->name }}</h3>
                <p class="muted">{{ $product->flute ? $product->flute.' flute' : $product->film_structure }}</p>
            </a>
        @endforeach
    </div>
</section>
<section class="wrap section">
    <h2>{{ __('packflow.home.voices') }}</h2>
    <div class="grid">
        @foreach ($testimonials as $quote)
            <figure class="card">
                <blockquote class="quote">“{{ app()->getLocale() === 'bn' && $quote->quote_bn ? $quote->quote_bn : $quote->quote }}”</blockquote>
                <figcaption class="muted">{{ $quote->author }}, {{ $quote->company }}</figcaption>
            </figure>
        @endforeach
    </div>
</section>
@endsection

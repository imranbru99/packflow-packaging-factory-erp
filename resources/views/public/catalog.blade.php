@extends('public.layout')
@section('content')
<section class="wrap section">
    <h1>Catalog</h1>
    <div class="filters">
        <a href="{{ route('catalog.index') }}" @class(['on' => !request('type')])>All</a>
        @foreach ($categories as $category)
            <a href="{{ route('catalog.index', ['type' => $category->type]) }}" @class(['on' => request('type') === $category->type])>{{ $category->name }}</a>
        @endforeach
    </div>
    <div class="grid">
        @foreach ($products as $product)
            <a class="card" href="{{ route('catalog.show', $product) }}" style="text-decoration:none;">
                <div class="kicker">{{ $product->type }}</div>
                <h3>{{ app()->getLocale()==='bn' && $product->name_bn ? $product->name_bn : $product->name }}</h3>
                <p class="muted">{{ $product->sku }}</p>
            </a>
        @endforeach
    </div>
    <div style="margin-top:1rem;">{{ $products->links() }}</div>
</section>
@endsection

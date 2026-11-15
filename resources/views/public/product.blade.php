@extends('public.layout')
@section('content')
<section class="wrap section">
    <div class="kicker">{{ $product->sku }} · {{ $product->type }}</div>
    <h1>{{ app()->getLocale()==='bn' && $product->name_bn ? $product->name_bn : $product->name }}</h1>
    <p class="lede">{{ app()->getLocale()==='bn' && $product->description_bn ? $product->description_bn : $product->description }}</p>
    <div class="grid">
        <div class="card"><b>Size</b><p>{{ $product->length_mm }} × {{ $product->width_mm }} × {{ $product->height_mm }} mm</p></div>
        <div class="card"><b>Structure</b><p>{{ $product->film_structure ?: (($product->flute ? $product->flute.' flute, ' : '').$product->board_gsm.' GSM') }}</p></div>
        <div class="card"><b>Print</b><p>{{ $product->ink_colors }} colours</p></div>
    </div>
    @if ($product->specifications->isNotEmpty())
        <h2 style="margin-top:2rem;">Specification</h2>
        @foreach ($product->specifications as $spec)
            <p>{{ $spec->key }}: {{ $spec->value }} {{ $spec->unit }}</p>
        @endforeach
    @endif
    <script type="application/ld+json">
        {!! json_encode(['@context'=>'https://schema.org','@type'=>'Product','name'=>$product->name,'sku'=>$product->sku,'description'=>$product->description], JSON_UNESCAPED_UNICODE) !!}
    </script>
</section>
@endsection

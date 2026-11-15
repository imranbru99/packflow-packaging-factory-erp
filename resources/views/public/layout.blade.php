<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? ($company->name.' — '.$company->tagline) }}</title>
    <meta name="description" content="{{ $description ?? $company->tagline }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600|source-sans-3:400,600|noto-sans-bengali:400,600" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/packflow.css') }}">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $company->name,
            'url' => url('/'),
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => $company->city, 'addressCountry' => $company->country],
        ], JSON_UNESCAPED_UNICODE) !!}
    </script>
</head>
<body>
    <header class="wrap nav">
        <a class="brand" href="{{ route('home') }}">
            <div class="mark">প</div>
            <div>
                <strong>{{ $company->name }}</strong>
                <span>{{ app()->getLocale() === 'bn' ? $company->name_bn : 'Corrugated · Flexible · Labels · Cartons' }}</span>
            </div>
        </a>
        <nav class="nav-links">
            <a href="{{ route('catalog.index') }}">{{ __('packflow.nav.catalog') }}</a>
            <a href="{{ route('site.capabilities') }}">{{ __('packflow.nav.capabilities') }}</a>
            <a href="{{ route('portfolio.index') }}">{{ __('packflow.nav.work') }}</a>
            <a href="{{ route('blog.index') }}">{{ __('packflow.nav.journal') }}</a>
            <a href="{{ route('site.contact') }}">{{ __('packflow.nav.contact') }}</a>
            <a href="{{ route('locale', app()->getLocale() === 'bn' ? 'en' : 'bn') }}">{{ app()->getLocale() === 'bn' ? 'EN' : 'বাং' }}</a>
            <a class="btn" href="{{ route('site.contact') }}">{{ __('packflow.nav.quote') }}</a>
        </nav>
    </header>
    <main>@yield('content')</main>
    <footer class="wrap">
        <div>
            <strong>{{ $company->name }}</strong>
            <p class="muted">{{ $company->address }}<br>{{ $company->city }}, {{ $company->country }}</p>
        </div>
        <div>
            <p>{{ $company->phone }}<br>{{ $company->email }}</p>
        </div>
        <div>
            <a href="{{ route('sitemap') }}">Sitemap</a><br>
            <a href="{{ route('login') }}">Factory login</a>
        </div>
    </footer>
</body>
</html>

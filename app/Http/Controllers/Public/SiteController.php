<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductCategory;
use App\Modules\Crm\Models\Lead;
use App\Modules\Settings\Models\CompanySetting;
use App\Modules\Website\Models\CaseStudy;
use App\Modules\Website\Models\Page;
use App\Modules\Website\Models\Post;
use App\Modules\Website\Models\QuoteRequest;
use App\Modules\Website\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'categories' => ProductCategory::query()->orderBy('name')->get(),
            'products' => Product::query()->where('is_public', true)->latest()->take(6)->get(),
            'testimonials' => Testimonial::query()->where('is_public', true)->take(3)->get(),
        ]);
    }

    public function catalog(Request $request): View
    {
        $products = Product::query()->where('is_public', true)
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderBy('name')
            ->paginate(12);

        return view('public.catalog', [
            'products' => $products,
            'categories' => ProductCategory::query()->orderBy('name')->get(),
            'title' => 'Catalog — '.CompanySetting::current()->name,
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_public, 404);
        $product->load('specifications', 'category');

        return view('public.product', [
            'product' => $product,
            'title' => $product->name,
            'description' => $product->description,
        ]);
    }

    public function page(string $slug): View
    {
        $page = Page::query()->where('slug', $slug)->firstOrFail();

        return view('public.page', ['page' => $page, 'title' => $page->title]);
    }

    public function portfolio(): View
    {
        return view('public.portfolio', [
            'studies' => CaseStudy::query()->where('is_public', true)->latest()->get(),
            'title' => 'Work',
        ]);
    }

    public function study(CaseStudy $caseStudy): View
    {
        abort_unless($caseStudy->is_public, 404);

        return view('public.study', ['study' => $caseStudy, 'title' => $caseStudy->title]);
    }

    public function blog(): View
    {
        return view('public.blog', [
            'posts' => Post::query()->where('is_published', true)->latest('published_at')->paginate(10),
            'title' => 'Journal',
        ]);
    }

    public function post(Post $post): View
    {
        abort_unless($post->is_published, 404);

        return view('public.post', ['post' => $post, 'title' => $post->title]);
    }

    public function contact(): View
    {
        return view('public.contact', ['title' => 'Contact']);
    }

    public function sitemap()
    {
        $urls = collect([
            route('home'),
            route('catalog.index'),
            route('site.about'),
            route('site.capabilities'),
            route('site.services'),
            route('portfolio.index'),
            route('blog.index'),
            route('site.contact'),
        ])->merge(Product::query()->where('is_public', true)->get()->map(fn (Product $product) => route('catalog.show', $product)))
            ->merge(Post::query()->where('is_published', true)->get()->map(fn (Post $post) => route('blog.show', $post)));

        return response()->view('public.sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function quote(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company' => ['required', 'string', 'max:160'],
            'contact_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'pack_type' => ['nullable', 'string', 'max:40'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'specification' => ['nullable', 'string', 'max:5000'],
        ]);

        $lead = Lead::query()->create([
            'company' => $data['company'],
            'contact_name' => $data['contact_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'source' => 'website',
            'status' => 'new',
            'product_interest' => $data['pack_type'] ?? null,
            'notes' => $data['specification'] ?? null,
        ]);

        QuoteRequest::query()->create($data + ['lead_id' => $lead->id, 'status' => 'new']);

        return back()->with('status', 'Received. A salesperson will price this against the plant calendar.');
    }
}

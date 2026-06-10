<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Page;
use App\Models\BlogPost;
use App\Models\Order;
use App\Services\SeoService;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    protected SeoService $seoService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Storefront Homepage.
     */
    public function home()
    {
        $page = Page::where('slug', 'ana-sayfa')
            ->with(['pageBlocks' => fn ($q) => $q->where('is_active', true)])
            ->first();
        $categories = Category::where('is_active', true)->orderBy('order', 'asc')->get();
        $featuredProducts = Product::where('is_featured', true)
            ->where('stock_status', true)
            ->with('images')
            ->take(8)
            ->get();
        $handpickedProducts = Product::where('stock_status', true)
            ->with('images')
            ->take(20)
            ->get();
        $latestBlogPosts = BlogPost::where('is_active', true)->latest()->take(3)->get();

        return view('frontend.pages.home', compact('page', 'categories', 'featuredProducts', 'latestBlogPosts', 'handpickedProducts'));
    }

    /**
     * Category Detail Listing.
     */
    public function category($slug, Request $request)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        // Sorting and filters
        $query = $category->products()->where('stock_status', true)->with('images');

        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }
        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        $sort = $request->input('sort', 'default');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'newest') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();

        return view('frontend.pages.category', [
            'category' => $category,
            'products' => $products,
            'seoModel' => $category
        ]);
    }

    /**
     * Product Detail Page.
     */
    public function product($slug)
    {
        $product = Product::where('slug', $slug)->with(['images', 'options.values', 'extraGifts' => function($q) {
            $q->where('stock_status', true)->with('mainImage');
        }])->firstOrFail();
        
        $extraGifts = $product->extraGifts;
        if ($extraGifts->isEmpty()) {
            $extraGifts = Product::where('stock_status', true)
                ->whereHas('categories', function($q) {
                    $q->where('slug', 'ekstra-hediyeler');
                })
                ->with('mainImage')
                ->get();
        }

        $relatedProducts = Product::where('id', '!=', $product->id)
            ->where('stock_status', true)
            ->whereHas('categories', function ($q) use ($product) {
                $q->whereIn('categories.id', $product->categories->pluck('id'));
            })
            ->with('images')
            ->take(4)
            ->get();

        $deliveryZones = \App\Models\DeliveryZone::where('is_active', true)->with('neighborhoods')->get();
        
        $centralDistricts = ['Kayapınar', 'Bağlar', 'Yenişehir', 'Sur'];
        $deliveryZones = $deliveryZones->sortBy(function($zone) use ($centralDistricts) {
            $index = array_search($zone->district, $centralDistricts);
            return $index !== false ? $index : 999;
        });

        return view('frontend.pages.product', [
            'product' => $product,
            'extraGifts' => $extraGifts,
            'relatedProducts' => $relatedProducts,
            'seoModel' => $product,
            'deliveryZones' => $deliveryZones
        ]);
    }

    /**
     * Search Results.
     */
    public function search(Request $request)
    {
        $q = $request->input('q', '');
        
        if (empty($q)) {
            return redirect()->route('home');
        }

        $products = Product::where('stock_status', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            })
            ->with('images')
            ->paginate(12)
            ->withQueryString();

        return view('frontend.pages.category', [
            'category' => (object)[
                'name' => "\"{$q}\" Arama Sonuçları",
                'description' => "Arama sorgunuzla eşleşen çiçeklerimiz listeleniyor.",
                'image' => null,
                'banner' => null
            ],
            'products' => $products,
            'seoTitle' => "\"{$q}\" Arama Sonuçları",
            'seoDescription' => "{$q} araması ile ilgili ürünler listeleniyor."
        ]);
    }

    /**
     * Order Tracking Page.
     */
    public function tracking(Request $request)
    {
        $orderNumber = $request->input('order_number');
        $order = null;

        if ($orderNumber) {
            $order = Order::where('order_number', trim($orderNumber))
                ->with(['items.product.images', 'statusHistories'])
                ->first();
        }

        return view('frontend.pages.tracking', compact('order', 'orderNumber'));
    }

    /**
     * Static Page Rendering.
     */
    public function page($slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)
            ->with(['pageBlocks' => fn ($q) => $q->where('is_active', true)])
            ->firstOrFail();

        return view('frontend.pages.static', [
            'page' => $page,
            'seoModel' => $page
        ]);
    }

    /**
     * Blog Listing.
     */
    public function blogList()
    {
        $posts = BlogPost::where('is_active', true)->latest()->paginate(9);

        return view('frontend.pages.blog_list', [
            'posts' => $posts,
            'seoTitle' => 'Çiçek Bakımı Rehberi ve Blog',
            'seoDescription' => 'Çiçek bakımı, özel gün çiçekleri ve hediye tavsiyeleri hakkında en son blog yazılarımız.'
        ]);
    }

    /**
     * Blog Detail Page.
     */
    public function blogDetail($slug)
    {
        $post = BlogPost::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('frontend.pages.blog_detail', [
            'post' => $post,
            'seoModel' => $post
        ]);
    }

    /**
     * Contact Page.
     */
    public function contact()
    {
        return view('frontend.pages.contact', [
            'seoTitle' => 'İletişim | Lav Çiçekçilik',
            'seoDescription' => 'Diyarbakır Kayapınar mağazamızın iletişim bilgileri, telefon numaraları, WhatsApp destek hattı ve yol tarifi.'
        ]);
    }

    /**
     * Dynamic Sitemap.
     */
    public function sitemap()
    {
        $sitemapContent = $this->seoService->generateSitemap();
        return response($sitemapContent, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Dynamic Robots.txt.
     */
    public function robots()
    {
        $sitemapUrl = url('/sitemap.xml');
        $content = "User-agent: *\nAllow: /\n\nSitemap: {$sitemapUrl}\n";
        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * AJAX Endpoint to fetch iyzico installments for a specific price.
     */
    public function installments(Request $request)
    {
        $price = (float)$request->input('price', 0);
        
        if ($price <= 0) {
            return response()->json(['success' => false, 'error' => 'Geçersiz fiyat.'], 400);
        }

        $iyzicoService = app(\App\Services\IyzicoPaymentService::class);
        $result = $iyzicoService->getInstallments($price);

        if ($result && isset($result['status']) && $result['status'] === 'success') {
            return response()->json([
                'success' => true,
                'data' => $result['installmentDetails'] ?? []
            ]);
        }

        // Fallback mock installments if iyzico request fails (useful for local development)
        $mockDetails = [
            [
                'cardFamily' => 'Axess',
                'installmentPrices' => [
                    ['installmentNumber' => 1, 'installmentPrice' => $price, 'totalPrice' => $price],
                    ['installmentNumber' => 3, 'installmentPrice' => ($price * 1.04) / 3, 'totalPrice' => $price * 1.04],
                    ['installmentNumber' => 6, 'installmentPrice' => ($price * 1.09) / 6, 'totalPrice' => $price * 1.09],
                    ['installmentNumber' => 9, 'installmentPrice' => ($price * 1.14) / 9, 'totalPrice' => $price * 1.14],
                ]
            ],
            [
                'cardFamily' => 'Bonus',
                'installmentPrices' => [
                    ['installmentNumber' => 1, 'installmentPrice' => $price, 'totalPrice' => $price],
                    ['installmentNumber' => 3, 'installmentPrice' => ($price * 1.04) / 3, 'totalPrice' => $price * 1.04],
                    ['installmentNumber' => 6, 'installmentPrice' => ($price * 1.09) / 6, 'totalPrice' => $price * 1.09],
                    ['installmentNumber' => 9, 'installmentPrice' => ($price * 1.14) / 9, 'totalPrice' => $price * 1.14],
                ]
            ],
            [
                'cardFamily' => 'Maximum',
                'installmentPrices' => [
                    ['installmentNumber' => 1, 'installmentPrice' => $price, 'totalPrice' => $price],
                    ['installmentNumber' => 3, 'installmentPrice' => ($price * 1.04) / 3, 'totalPrice' => $price * 1.04],
                    ['installmentNumber' => 6, 'installmentPrice' => ($price * 1.09) / 6, 'totalPrice' => $price * 1.09],
                    ['installmentNumber' => 9, 'installmentPrice' => ($price * 1.14) / 9, 'totalPrice' => $price * 1.14],
                ]
            ],
            [
                'cardFamily' => 'World',
                'installmentPrices' => [
                    ['installmentNumber' => 1, 'installmentPrice' => $price, 'totalPrice' => $price],
                    ['installmentNumber' => 3, 'installmentPrice' => ($price * 1.04) / 3, 'totalPrice' => $price * 1.04],
                    ['installmentNumber' => 6, 'installmentPrice' => ($price * 1.09) / 6, 'totalPrice' => $price * 1.09],
                    ['installmentNumber' => 9, 'installmentPrice' => ($price * 1.14) / 9, 'totalPrice' => $price * 1.14],
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $mockDetails,
            'is_mock' => true
        ]);
    }

    /**
     * AJAX Infinite scroll for handpicked products.
     */
    public function handpickedProducts(Request $request)
    {
        $products = Product::where('stock_status', true)
            ->with('images')
            ->paginate(20);

        if ($request->ajax()) {
            return view('frontend.partials.product_cards', compact('products'))->render();
        }

        abort(404);
    }
}

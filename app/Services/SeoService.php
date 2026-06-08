<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Product;
use App\Models\Category;
use App\Models\Page;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * Get defaults or model-based SEO meta tags.
     */
    public function getMetaTags($model = null, ?string $customTitle = null, ?string $customDescription = null): array
    {
        $defaultTitle = Setting::where('key', 'meta_title')->value('value') ?? 'Lav Çiçekçilik';
        $defaultDesc = Setting::where('key', 'meta_description')->value('value') ?? '';

        $title = $customTitle;
        $description = $customDescription;

        if ($model) {
            $title = $model->meta_title ?? $model->name ?? $model->title ?? $title;
            $description = $model->meta_description ?? $model->short_description ?? Str::limit(strip_tags($model->description ?? $model->content ?? ''), 150) ?? $description;
        }

        $title = $title ? "{$title} | Lav Çiçekçilik" : $defaultTitle;
        $description = $description ?? $defaultDesc;
        $canonical = Request::url();

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'og_title' => $title,
            'og_description' => $description,
            'og_url' => $canonical,
            'og_type' => $model instanceof BlogPost ? 'article' : 'website',
            'og_image' => $model && isset($model->image) ? asset($model->image) : ($model && $model instanceof Product && $model->images->first() ? asset($model->images->first()->image_path) : asset('/assets/images/logo.png')),
        ];
    }

    /**
     * Generate JSON-LD Schema markup.
     */
    public function getSchemaMarkup($model = null): string
    {
        $siteName = Setting::where('key', 'site_name')->value('value') ?? 'Lav Çiçekçilik';
        $sitePhone = Setting::where('key', 'site_phone')->value('value') ?? '';
        $siteEmail = Setting::where('key', 'site_email')->value('value') ?? '';
        $siteAddress = Setting::where('key', 'site_address')->value('value') ?? '';

        $schema = [];

        // 1. Default Organization and LocalBusiness schema
        $orgSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FlowerShop',
            'name' => $siteName,
            'url' => url('/'),
            'logo' => asset('/assets/images/logo.png'),
            'telephone' => $sitePhone,
            'email' => $siteEmail,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $siteAddress,
                'addressLocality' => 'Kayapınar',
                'addressRegion' => 'Diyarbakır',
                'addressCountry' => 'TR'
            ],
            'priceRange' => '₺₺'
        ];

        if (!$model) {
            $schema[] = $orgSchema;
        }

        // 2. Product Schema
        if ($model instanceof Product) {
            $imageUrls = [];
            foreach ($model->images as $img) {
                $imageUrls[] = asset($img->image_path);
            }

            $schema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $model->name,
                'image' => $imageUrls,
                'description' => $model->short_description ?? strip_tags($model->description),
                'sku' => $model->sku,
                'offers' => [
                    '@type' => 'Offer',
                    'url' => Request::url(),
                    'priceCurrency' => 'TRY',
                    'price' => (string)$model->final_price,
                    'priceValidUntil' => date('Y-12-31'),
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'availability' => $model->stock_status ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'seller' => [
                        '@type' => 'Organization',
                        'name' => $siteName
                    ]
                ]
            ];
        }

        // 3. Blog/Article Schema
        if ($model instanceof BlogPost) {
            $schema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $model->title,
                'image' => [
                    asset($model->image ?? '/assets/images/blog_placeholder.png')
                ],
                'datePublished' => $model->created_at?->toIso8601String(),
                'dateModified' => $model->updated_at?->toIso8601String(),
                'author' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                    'url' => url('/')
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('/assets/images/logo.png')
                    ]
                ],
                'description' => $model->summary
            ];
        }

        if (empty($schema)) {
            return '';
        }

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
    }

    /**
     * Generate sitemap.xml.
     */
    public function generateSitemap(): string
    {
        $urls = [];

        // Static routes
        $urls[] = ['loc' => url('/'), 'lastmod' => date('Y-m-d'), 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => url('/blog'), 'lastmod' => date('Y-m-d'), 'changefreq' => 'weekly', 'priority' => '0.8'];
        $urls[] = ['loc' => url('/iletisim'), 'lastmod' => date('Y-m-d'), 'changefreq' => 'monthly', 'priority' => '0.5'];

        // Categories
        $categories = Category::where('is_active', true)->get();
        foreach ($categories as $cat) {
            $urls[] = [
                'loc' => url("/kategori/{$cat->slug}"),
                'lastmod' => $cat->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }

        // Products
        $products = Product::where('stock_status', true)->get();
        foreach ($products as $prod) {
            $urls[] = [
                'loc' => url("/urun/{$prod->slug}"),
                'lastmod' => $prod->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.9'
            ];
        }

        // Pages
        $pages = Page::where('is_active', true)->where('slug', '!=', 'ana-sayfa')->get();
        foreach ($pages as $p) {
            $urls[] = [
                'loc' => url("/sayfa/{$p->slug}"),
                'lastmod' => $p->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6'
            ];
        }

        // Blog Posts
        $posts = BlogPost::where('is_active', true)->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => url("/blog/{$post->slug}"),
                'lastmod' => $post->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        }

        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}

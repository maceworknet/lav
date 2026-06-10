<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    @php
        $seoService = app(\App\Services\SeoService::class);
        $seo = $seoService->getMetaTags($seoModel ?? null, $seoTitle ?? null, $seoDescription ?? null);
        
        $siteName = \App\Models\Setting::where('key', 'site_name')->value('value') ?? 'Lav Çiçekçilik';
        $phone = \App\Models\Setting::where('key', 'site_phone')->value('value') ?? '';
        $whatsapp = \App\Models\Setting::where('key', 'site_whatsapp')->value('value') ?? '';
        $email = \App\Models\Setting::where('key', 'site_email')->value('value') ?? '';
        $address = \App\Models\Setting::where('key', 'site_address')->value('value') ?? '';
        $hours = \App\Models\Setting::where('key', 'working_hours')->value('value') ?? '';
        
        $siteLogo = \App\Models\Setting::where('key', 'site_logo')->value('value');
        $headerSearchActive = \App\Models\Setting::where('key', 'header_search_active')->value('value') ?? '1';
        $headerAccountActive = \App\Models\Setting::where('key', 'header_account_active')->value('value') ?? '1';
        $headerFavoritesActive = \App\Models\Setting::where('key', 'header_favorites_active')->value('value') ?? '1';
        $headerCartActive = \App\Models\Setting::where('key', 'header_cart_active')->value('value') ?? '1';
        
        // Cart count helper
        $cart = null;
        if (session()->has('guest_token')) {
            $cart = \App\Models\Cart::where('guest_token', session('guest_token'))->first();
        } elseif (auth('customer')->check()) {
            $cart = \App\Models\Cart::where('customer_id', auth('customer')->id())->first();
        }
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    @endphp

    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seo['og_type'] }}">
    <meta property="og:url" content="{{ $seo['og_url'] }}">
    <meta property="og:title" content="{{ $seo['og_title'] }}">
    <meta property="og:description" content="{{ $seo['og_description'] }}">
    <meta property="og:image" content="{{ $seo['og_image'] }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $seo['og_url'] }}">
    <meta property="twitter:title" content="{{ $seo['og_title'] }}">
    <meta property="twitter:description" content="{{ $seo['og_description'] }}">
    <meta property="twitter:image" content="{{ $seo['og_image'] }}">

    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Schema Markup -->
    {!! $seoService->getSchemaMarkup($seoModel ?? null) !!}

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fcf8f8;
        }
        .font-serif {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="text-slate-800 flex flex-col min-h-screen">

    <!-- 1. Top Announcement Bar -->
    <div class="bg-rose-600 text-white text-xs py-2 px-4 text-center font-medium tracking-wide">
        Diyarbakır İçi Tüm Siparişlerde Aynı Gün Teslimat ve Canlı Kurye Takibi!
    </div>

    <!-- 2. Header / Navigation -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-rose-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="/" class="flex items-center gap-2">
                        @if($siteLogo)
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-12 w-auto max-w-[200px] object-contain">
                        @else
                            <span class="font-serif text-2xl sm:text-3xl font-extrabold tracking-tight transition">
                                <span class="text-amber-500 hover:text-amber-600">Lav</span> <span class="text-rose-600 hover:text-rose-700 font-light">Çiçekçilik</span>
                            </span>
                        @endif
                    </a>
                </div>

                <!-- Navigation Links & Search Overlay -->
                @php
                    $headerMenu = \App\Models\Menu::where('slug', 'header-menu')->first();
                    $menuItems = $headerMenu ? $headerMenu->menuItems()->where('is_active', true)->orderBy('order', 'asc')->get() : [];
                @endphp
                <div class="hidden md:flex flex-grow justify-center px-8 relative" id="header-nav-container">
                    <!-- Menu Area -->
                    <nav class="flex space-x-8 items-center transition-opacity duration-300" id="header-navigation-menu">
                        @foreach ($menuItems as $item)
                            <a href="{{ $item->url }}" target="{{ $item->target }}" class="text-sm font-semibold text-slate-600 hover:text-rose-600 transition tracking-wide whitespace-nowrap">
                                {{ $item->title }}
                            </a>
                        @endforeach
                    </nav>
                    
                    <!-- Search Overlay Area -->
                    <div class="absolute inset-0 flex items-center justify-center hidden opacity-0 translate-y-2 transition-all duration-300" id="header-search-bar">
                        <form action="{{ route('search') }}" method="GET" class="w-full max-w-xl flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-full px-4 py-1.5 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                            </svg>
                            <input type="text" name="q" id="header-search-input" placeholder="Çiçek ara..." class="w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium">
                            <button type="button" id="close-search-btn" class="text-slate-400 hover:text-rose-600 transition p-1" title="Kapat">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="flex items-center gap-4 shrink-0">

                    <!-- Search Icon (Desktop) -->
                    @if($headerSearchActive == '1')
                        <button id="open-search-btn" class="p-2 text-slate-600 hover:text-rose-600 transition focus:outline-none hidden md:block" title="Arama">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                            </svg>
                        </button>
                    @endif
                    
                    <!-- Account Dropdown Menu (Desktop) -->
                    @if($headerAccountActive == '1')
                        <div class="relative group py-2 hidden md:block">
                            <a href="{{ auth('customer')->check() ? route('customer.account') : route('customer.login') }}" class="p-2 text-slate-600 hover:text-rose-600 transition flex items-center gap-1.5 focus:outline-none" title="Hesabım">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </a>
                            
                            <div class="absolute right-0 top-full mt-1 w-56 bg-white border border-rose-50 rounded-2xl shadow-xl py-3 px-2 hidden group-hover:block transition-all duration-300 z-50">
                                @if(auth('customer')->check())
                                    <div class="px-3 py-2 border-b border-rose-50/50 mb-1">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Hoş Geldiniz</p>
                                        <p class="text-xs text-slate-800 font-black truncate">{{ auth('customer')->user()->full_name }}</p>
                                    </div>
                                    <a href="{{ route('customer.account', ['tab' => 'overview']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition group/item">
                                        <svg class="w-4 h-4 text-slate-400 group-hover/item:text-rose-600 transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                        </svg>
                                        Genel Bakış
                                    </a>
                                    <a href="{{ route('customer.account', ['tab' => 'orders']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition group/item">
                                        <svg class="w-4 h-4 text-slate-400 group-hover/item:text-rose-600 transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                        Siparişlerim
                                    </a>
                                    <a href="{{ route('customer.account', ['tab' => 'addresses']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition group/item">
                                        <svg class="w-4 h-4 text-slate-400 group-hover/item:text-rose-600 transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                        Adreslerim
                                    </a>
                                    <a href="{{ route('customer.account', ['tab' => 'profile']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition group/item">
                                        <svg class="w-4 h-4 text-slate-400 group-hover/item:text-rose-600 transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        Profil ve Şifre
                                    </a>
                                    <div class="border-t border-rose-50/50 mt-1 pt-1">
                                        <form action="{{ route('customer.logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition text-left group/btn">
                                                <svg class="w-4 h-4 text-rose-500 transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                                </svg>
                                                Çıkış Yap
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="px-2 py-1 space-y-1.5">
                                        <a href="{{ route('customer.login') }}" class="flex items-center justify-center px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                                            Giriş Yap
                                        </a>
                                        <a href="{{ route('customer.register') }}" class="flex items-center justify-center px-3 py-2 border border-rose-100 hover:bg-rose-50 text-rose-600 rounded-xl text-xs font-bold transition gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0zM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.647-6.374-1.765z" /></svg>
                                            Üye Ol
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Favorites Icon -->
                    @if($headerFavoritesActive == '1')
                        @php
                            $favCount = 0;
                            if (session()->has('guest_token')) {
                                $favCount = \App\Models\Favorite::where('guest_token', session('guest_token'))->count();
                            } elseif (auth('customer')->check()) {
                                $favCount = \App\Models\Favorite::where('customer_id', auth('customer')->id())->count();
                            }
                        @endphp
                        <a href="{{ route('favorites.index') }}" class="p-2 text-slate-600 hover:text-rose-600 transition relative" title="Favoriler" id="header-favorites-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                            <span id="favorites-count-badge" class="{{ $favCount > 0 ? '' : 'hidden' }} absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-rose-600 rounded-full">
                                {{ $favCount }}
                            </span>
                        </a>
                    @endif

                    <!-- Cart Icon -->
                    @if($headerCartActive == '1')
                        <a href="/sepet" id="header-cart-btn" class="relative p-2 text-slate-600 hover:text-rose-600 transition" title="Sepetim">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            @if ($cartCount > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-rose-600 rounded-full">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-600 hover:text-rose-600 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-rose-50 px-4 pt-2 pb-6 space-y-3 shadow-inner">
            @foreach ($menuItems as $item)
                <a href="{{ $item->url }}" target="{{ $item->target }}" class="block text-base font-semibold text-slate-700 hover:text-rose-600 py-2 border-b border-slate-50">
                    {{ $item->title }}
                </a>
            @endforeach
            @if(auth('customer')->check())
                <a href="{{ route('customer.account') }}" class="block text-center bg-slate-900 text-white font-bold py-3 rounded-lg hover:bg-slate-800 transition mt-2">
                    Hesabım
                </a>
                <form action="{{ route('customer.logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full text-center bg-rose-50 text-rose-600 font-bold py-3 rounded-lg hover:bg-rose-100 transition">
                        Çıkış Yap
                    </button>
                </form>
            @else
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <a href="{{ route('customer.login') }}" class="block text-center bg-slate-100 text-slate-700 font-bold py-3 rounded-lg hover:bg-slate-200 transition">
                        Giriş Yap
                    </a>
                    <a href="{{ route('customer.register') }}" class="block text-center bg-rose-600 text-white font-bold py-3 rounded-lg hover:bg-rose-700 transition">
                        Üye Ol
                    </a>
                </div>
            @endif
        </div>
    </header>

    <!-- 3. Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- 4. Footer -->
    <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 pt-16 pb-8 mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                
                <!-- Col 1: About -->
                <div class="space-y-4">
                    <span class="font-serif text-2xl font-extrabold">
                        <span class="text-amber-400">Lav</span> <span class="text-rose-500 font-light">Çiçekçilik</span>
                    </span>
                    <p class="text-sm text-slate-400 leading-relaxed pt-2">
                        Diyarbakır genelinde taze çiçek buketleri, saksı aranjmanları ve özel tasarımlarımızla sevdiklerinize en güzel hisleri taşımak için buradayız.
                    </p>
                </div>

                <!-- Col 2: Useful Links -->
                <div>
                    <h3 class="text-white font-bold text-base mb-4 tracking-wider">Kurumsal</h3>
                    @php
                        $footerMenu = \App\Models\Menu::where('slug', 'footer-menu')->first();
                        $footerItems = $footerMenu ? $footerMenu->menuItems()->where('is_active', true)->orderBy('order', 'asc')->get() : [];
                    @endphp
                    <ul class="space-y-3 text-sm">
                        @foreach ($footerItems as $item)
                            <li>
                                <a href="{{ $item->url }}" class="hover:text-rose-500 transition">{{ $item->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Col 3: Contact Info -->
                <div class="space-y-3">
                    <h3 class="text-white font-bold text-base mb-4 tracking-wider">İletişim</h3>
                    <p class="text-sm text-slate-400 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-rose-500 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                        {{ $phone }}
                    </p>
                    <p class="text-sm text-slate-400 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-rose-500 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        {{ $email }}
                    </p>
                    <p class="text-sm text-slate-400 flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span>{{ $address }}</span>
                    </p>
                </div>

                <!-- Col 4: Hızlı Linkler -->
                <div class="space-y-4">
                    <h3 class="text-white font-bold text-base tracking-wider">Hızlı Linkler</h3>
                    <ul class="space-y-3 text-sm">
                        <li>
                            <a href="{{ route('page', 'hakkimizda') }}" class="hover:text-rose-500 transition">Hakkımızda</a>
                        </li>
                        <li>
                            <a href="{{ route('blog.list') }}" class="hover:text-rose-500 transition">Blog</a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}" class="hover:text-rose-500 transition">İletişim</a>
                        </li>
                        <li>
                            <a href="{{ route('tracking') }}" class="hover:text-rose-500 transition">Sipariş Takip</a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Divider -->
            <div class="border-t border-slate-800 my-12"></div>

            <!-- Bottom area -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center text-center md:text-left text-xs text-slate-500">
                <!-- Copyright -->
                <div>
                    <p>&copy; {{ date('Y') }} {{ $siteName }}. Tüm Hakları Saklıdır.</p>
                </div>
                <!-- iyzico badge in the middle -->
                <div class="flex justify-center">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#121824] border border-slate-800 rounded-lg">
                        <span class="text-xs font-extrabold text-[#00b0ff] tracking-wider">iyzico</span>
                        <span class="text-[10px] text-slate-400 border-l border-slate-700 pl-2">256-bit Güvenli Altyapı</span>
                    </div>
                </div>
                <!-- Macework logo at the end -->
                <div class="flex justify-center md:justify-end">
                    <a href="https://macework.net/" target="_blank" rel="noopener noreferrer" class="footer-macework-signature text-slate-500 hover:text-slate-300 transition-colors duration-200" title="Macework Reklam Ajansı" aria-label="Macework">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 186.4 48" height="20" style="display:block;width:auto;" aria-hidden="true">
                            <g><g>
                                <path fill="currentColor" d="M31.6,35h-5.6v-15.1c0-1.6,0-3.4.2-5.4h-.1c-.3,1.5-.6,2.7-.8,3.3l-5.9,17.1h-4.6l-6-17c-.2-.5-.4-1.6-.8-3.5h-.2c.2,2.5.2,4.7.2,6.5v13.9H2.9V9.8h8.3l5.2,14.9c.4,1.2.7,2.4.9,3.6h.1c.3-1.4.7-2.6,1-3.6l5.2-14.9h8.1v25.2Z"></path>
                                <path fill="currentColor" d="M51.8,35h-5.3v-2.6h0c-1.2,2-3,3-5.4,3s-3.1-.5-4.1-1.5-1.5-2.3-1.5-4c0-3.5,2.1-5.5,6.2-6l4.9-.7c0-2-1.1-3-3.2-3s-4.2.6-6.1,1.9v-4.2c.8-.4,1.8-.8,3.2-1.1s2.6-.5,3.7-.5c5.1,0,7.7,2.6,7.7,7.7v10.8ZM46.5,27.7v-1.2l-3.3.4c-1.8.2-2.7,1-2.7,2.4s.2,1.2.7,1.6,1,.6,1.8.6,1.9-.4,2.5-1.1,1-1.6,1-2.7Z"></path>
                                <path fill="currentColor" d="M69.8,34.3c-1.3.7-3.1,1.1-5.4,1.1s-5-.8-6.7-2.5-2.6-3.8-2.6-6.5.9-5.5,2.8-7.2,4.3-2.6,7.4-2.6,3.7.3,4.6.8v4.7c-1.2-.9-2.5-1.3-3.9-1.3s-2.9.5-3.8,1.4-1.4,2.2-1.4,3.9.4,2.8,1.3,3.8,2.1,1.4,3.7,1.4,2.7-.4,4.1-1.3v4.5Z"></path>
                                <path fill="currentColor" d="M89.6,27.6h-11.7c.2,2.6,1.8,3.9,4.9,3.9s3.7-.5,5.2-1.4v4c-1.7.9-3.8,1.3-6.5,1.3s-5.2-.8-6.8-2.4-2.4-3.9-2.4-6.7.9-5.4,2.6-7.1,3.9-2.6,6.4-2.6,4.7.8,6.1,2.3,2.2,3.7,2.2,6.3v2.3ZM84.5,24.2c0-2.6-1-3.9-3.1-3.9s-1.7.4-2.3,1.1-1,1.7-1.2,2.8h6.6Z"></path>
                                <path fill="currentColor" d="M119,17l-5.2,18h-5.8l-2.7-10.5c-.2-.7-.3-1.5-.3-2.3h-.1c0,.9-.2,1.7-.4,2.2l-2.8,10.6h-5.8l-5.1-18h5.7l2.5,11.7c.1.6.2,1.2.3,2h.1c0-.8.2-1.5.3-2.1l3.1-11.7h5.3l2.8,11.7c0,.3.2,1,.2,2h.1c0-.7.2-1.4.3-2l2.3-11.7h5.2Z"></path>
                                <path fill="currentColor" d="M130,35.4c-3,0-5.4-.8-7.1-2.5s-2.6-4-2.6-6.8.9-5.3,2.7-7,4.2-2.5,7.2-2.5,5.3.8,7,2.5,2.5,3.9,2.5,6.7-.9,5.3-2.6,7.1-4.2,2.6-7.2,2.6ZM130.2,20.8c-1.3,0-2.3.5-3.1,1.4s-1.1,2.2-1.1,3.8c0,3.5,1.4,5.2,4.2,5.2s4-1.8,4-5.3-1.3-5-4-5Z"></path>
                                <path fill="currentColor" d="M155.2,22c-.7-.4-1.4-.5-2.3-.5s-2.2.4-2.8,1.3-1,2.1-1,3.6v8.6h-5.6v-18h5.6v3.3h0c.9-2.4,2.5-3.7,4.7-3.7s1,0,1.4.2v5.1Z"></path>
                                <path fill="currentColor" d="M175.8,35h-6.7l-5.7-8.9h0v8.9h-5.6V8.4h5.6v17h0l5.4-8.3h6.6l-6.5,8.5,6.9,9.5Z"></path>
                            </g></g>
                            <circle fill="#e11d48" cx="180.3" cy="31.1" r="3.9"></circle>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    @if ($whatsapp)
        @php
            $notifService = app(\App\Services\NotificationService::class);
            $whatsappUrl = $notifService->generateWhatsAppLink($whatsapp, 'Merhaba, online mağazanızdan destek almak istiyorum.');
        @endphp
        <a href="{{ $whatsappUrl }}" target="_blank" class="fixed bottom-6 right-6 bg-emerald-500 hover:bg-emerald-600 text-white p-4 rounded-full shadow-2xl z-40 transition transform hover:scale-110 flex items-center justify-center" aria-label="WhatsApp Destek Hattı">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.006.133-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.693.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.455 5.703 1.455h.004c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
            </svg>
        </a>
    @endif

    <script>
        // Mobile menu toggle
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        if (btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        }

        // Search Overlay toggle logic
        const openSearchBtn = document.getElementById('open-search-btn');
        const closeSearchBtn = document.getElementById('close-search-btn');
        const navMenu = document.getElementById('header-navigation-menu');
        const searchBar = document.getElementById('header-search-bar');
        const searchInput = document.getElementById('header-search-input');

        function openSearch() {
            navMenu.style.opacity = '0';
            setTimeout(() => {
                navMenu.classList.add('hidden');
                searchBar.classList.remove('hidden');
                setTimeout(() => {
                    searchBar.classList.remove('opacity-0', 'translate-y-2');
                    searchInput.focus();
                }, 50);
            }, 200);
        }

        function closeSearch() {
            searchBar.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => {
                searchBar.classList.add('hidden');
                navMenu.classList.remove('hidden');
                setTimeout(() => {
                    navMenu.style.opacity = '1';
                }, 50);
            }, 200);
        }

        if (openSearchBtn && closeSearchBtn && navMenu && searchBar) {
            openSearchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (searchBar.classList.contains('hidden')) {
                    openSearch();
                } else {
                    closeSearch();
                }
            });

            closeSearchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                closeSearch();
            });
        }

        // Favorites AJAX Toggle logic
        document.addEventListener('click', function (e) {
            const toggleBtn = e.target.closest('.favorite-toggle-btn');
            if (!toggleBtn) return;

            e.preventDefault();
            const productId = toggleBtn.getAttribute('data-product-id');
            if (!productId) return;

            toggleBtn.disabled = true;

            fetch('/favoriler/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Sistem hatası');
                }
                return response.json();
            })
            .then(data => {
                toggleBtn.disabled = false;
                if (data.success) {
                    const allProductBtns = document.querySelectorAll(`.favorite-toggle-btn[data-product-id="${productId}"]`);
                    
                    allProductBtns.forEach(btn => {
                        if (data.status === 'added') {
                            btn.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-rose-600">
                                    <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                </svg>
                            `;
                            btn.setAttribute('title', 'Favorilerden Çıkar');
                        } else {
                            btn.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                            `;
                            btn.setAttribute('title', 'Favorilere Ekle');
                        }
                    });

                    const badge = document.getElementById('favorites-count-badge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }

                    const card = toggleBtn.closest('.product-card-container');
                    if (card && data.status === 'removed' && window.location.pathname.includes('/favoriler')) {
                        card.remove();
                        const remaining = document.querySelectorAll('.product-card-container');
                        if (remaining.length === 0) {
                            window.location.reload();
                        }
                    }
                }
            })
            .catch(error => {
                toggleBtn.disabled = false;
                console.error('Favorites toggle error:', error);
            });
        });

        // Sidebar Cart Toggle and Operations
        window.openSidebarCart = function() {
            const overlay = document.getElementById('sidebar-cart-overlay');
            const drawer = document.getElementById('sidebar-cart-drawer');
            
            overlay.classList.remove('pointer-events-none', 'opacity-0');
            overlay.classList.add('opacity-100');
            drawer.classList.remove('translate-x-full');
            
            fetchCartData();
        };

        window.closeSidebarCart = function() {
            const overlay = document.getElementById('sidebar-cart-overlay');
            const drawer = document.getElementById('sidebar-cart-drawer');
            
            overlay.classList.add('pointer-events-none', 'opacity-0');
            overlay.classList.remove('opacity-100');
            drawer.classList.add('translate-x-full');
        };

        function fetchCartData() {
            fetch('/sepet/json')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderSidebarCart(data);
                    }
                })
                .catch(err => console.error("Sidebar cart fetch error:", err));
        }

        function renderSidebarCart(data) {
            document.getElementById('sidebar-cart-badge').innerText = data.cart_count;
            document.getElementById('sidebar-cart-subtotal').innerText = data.subtotal.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
            
            // Header cart badge update
            const headerCartBadge = document.querySelector('#header-cart-btn span');
            if (headerCartBadge) {
                if (data.cart_count > 0) {
                    headerCartBadge.innerText = data.cart_count;
                    headerCartBadge.classList.remove('hidden');
                } else {
                    headerCartBadge.classList.add('hidden');
                }
            } else if (data.cart_count > 0) {
                const btn = document.getElementById('header-cart-btn');
                if (btn) {
                    const badgeSpan = document.createElement('span');
                    badgeSpan.className = 'absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-rose-600 rounded-full';
                    badgeSpan.innerText = data.cart_count;
                    btn.appendChild(badgeSpan);
                }
            }

            const shippingBar = document.getElementById('sidebar-cart-shipping-bar');
            const shippingText = document.getElementById('sidebar-cart-shipping-text');
            const shippingProgress = document.getElementById('sidebar-shipping-progress');
            
            if (data.free_shipping_remaining > 0) {
                shippingText.innerHTML = `Kargo bedava avantajı için <span class="font-bold text-rose-600">${data.free_shipping_remaining.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ₺</span> değerinde daha ürün ekleyin!`;
                const percent = Math.min(100, (data.subtotal / data.free_shipping_threshold) * 100);
                shippingProgress.style.width = percent + '%';
                shippingProgress.className = "bg-rose-500 h-full rounded-full transition-all duration-300";
            } else {
                shippingText.innerHTML = `<span class="font-bold text-emerald-600">Tebrikler! Kargo ücretiniz bizden. 🎉</span>`;
                shippingProgress.style.width = '100%';
                shippingProgress.className = "bg-emerald-500 h-full rounded-full transition-all duration-300";
            }

            const itemsContainer = document.getElementById('sidebar-cart-items');
            const footer = document.getElementById('sidebar-cart-footer');
            const emptyState = document.getElementById('sidebar-cart-empty');
            
            itemsContainer.innerHTML = '';
            
            if (data.items.length === 0) {
                itemsContainer.classList.add('hidden');
                shippingBar.classList.add('hidden');
                footer.classList.add('hidden');
                emptyState.classList.remove('hidden');
            } else {
                itemsContainer.classList.remove('hidden');
                shippingBar.classList.remove('hidden');
                footer.classList.remove('hidden');
                emptyState.classList.add('hidden');
                
                data.items.forEach(item => {
                    const itemHtml = `
                        <div class="flex items-center gap-4 bg-white border border-slate-100 p-3.5 rounded-2xl hover:border-rose-100 hover:shadow-sm transition duration-300">
                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-50 border border-slate-100 shrink-0">
                                <img src="${item.image_url ? item.image_url : '/assets/images/rose101.webp'}" alt="${item.name}" class="w-full h-full object-cover">
                            </div>
                            
                            <div class="flex-grow min-w-0">
                                <h5 class="text-xs font-bold text-slate-800 truncate">${item.name}</h5>
                                ${item.options_label ? `<p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">${item.options_label}</p>` : ''}
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs font-extrabold text-rose-600">${item.price.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ₺</span>
                                    
                                    <div class="flex items-center border border-slate-200 rounded-full bg-slate-50/50 p-0.5 scale-90">
                                        <button type="button" onclick="updateSidebarItemQty(${item.id}, ${item.quantity - 1})" class="w-6 h-6 flex items-center justify-center text-slate-500 font-bold hover:text-rose-600 hover:bg-white rounded-full transition duration-200">-</button>
                                        <span class="w-6 text-center font-black text-xs">${item.quantity}</span>
                                        <button type="button" onclick="updateSidebarItemQty(${item.id}, ${item.quantity + 1})" class="w-6 h-6 flex items-center justify-center text-slate-500 font-bold hover:text-rose-600 hover:bg-white rounded-full transition duration-200">+</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" onclick="deleteSidebarItem(${item.id})" class="text-slate-400 hover:text-rose-600 p-1.5 transition shrink-0" title="Sil">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    `;
                    itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
                });
            }

            const suggestionsSection = document.getElementById('sidebar-cart-suggestions-section');
            const suggestionsContainer = document.getElementById('sidebar-cart-suggestions');
            suggestionsContainer.innerHTML = '';
            
            if (data.suggestions.length === 0 || data.items.length === 0) {
                suggestionsSection.classList.add('hidden');
            } else {
                suggestionsSection.classList.remove('hidden');
                
                data.suggestions.forEach(prod => {
                    const suggHtml = `
                        <div class="flex items-center gap-3 bg-slate-50/55 border border-slate-100 p-2.5 rounded-xl hover:border-rose-100 transition duration-300">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-white border border-slate-100 shrink-0">
                                <img src="${prod.image_url ? prod.image_url : '/assets/images/rose101.webp'}" alt="${prod.name}" class="w-full h-full object-cover">
                            </div>
                            
                            <div class="flex-grow min-w-0">
                                <h6 class="text-[11px] font-bold text-slate-800 truncate">${prod.name}</h6>
                                <p class="text-[10px] font-extrabold text-rose-600 mt-0.5">${prod.price.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ₺</p>
                            </div>
                            
                            <button type="button" onclick="addSuggestionToCart(${prod.id})" class="px-3 py-1 bg-white hover:bg-rose-600 border border-rose-100 hover:border-rose-600 text-rose-600 hover:text-white text-[10px] font-black rounded-lg transition duration-200 uppercase shrink-0">
                                Ekle
                            </button>
                        </div>
                    `;
                    suggestionsContainer.insertAdjacentHTML('beforeend', suggHtml);
                });
            }
        }

        window.updateSidebarItemQty = function(itemId, qty) {
            if (qty <= 0) {
                deleteSidebarItem(itemId);
                return;
            }
            
            fetch(`/sepet/guncelle/${itemId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ quantity: qty })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderSidebarCart(data);
                }
            })
            .catch(err => console.error("Sidebar qty update error:", err));
        };

        window.deleteSidebarItem = function(itemId) {
            if (!confirm('Bu ürünü sepetinizden kaldırmak istediğinize emin misiniz?')) {
                return;
            }
            
            fetch(`/sepet/sil/${itemId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderSidebarCart(data);
                }
            })
            .catch(err => console.error("Sidebar item delete error:", err));
        };

        window.addSuggestionToCart = function(productId) {
            fetch('/sepet/ekle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderSidebarCart(data);
                }
            })
            .catch(err => console.error("Sidebar add suggestion error:", err));
        };

        const cartBtn = document.getElementById('header-cart-btn');
        if (cartBtn) {
            cartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openSidebarCart();
            });
        }

        const overlay = document.getElementById('sidebar-cart-overlay');
        if (overlay) {
            overlay.addEventListener('click', closeSidebarCart);
        }
    </script>

    <!-- Sidebar Cart Drawer Overlay -->
    <div id="sidebar-cart-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 transition-opacity duration-300 opacity-0 pointer-events-none"></div>

    <!-- Sidebar Cart Drawer Panel -->
    <div id="sidebar-cart-drawer" class="fixed right-0 top-0 bottom-0 w-full sm:w-[450px] bg-white z-50 shadow-2xl transition-transform duration-300 translate-x-full flex flex-col">
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-serif text-xl font-extrabold text-slate-800">Sepetiniz</span>
                <span id="sidebar-cart-badge" class="bg-rose-100 text-rose-600 text-xs font-bold px-2 py-0.5 rounded-full">0</span>
            </div>
            <button type="button" onclick="closeSidebarCart()" class="text-slate-400 hover:text-rose-600 transition p-1" title="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-grow overflow-y-auto p-5 space-y-6">
            <!-- Free Shipping Progress -->
            <div id="sidebar-cart-shipping-bar" class="bg-slate-50/80 border border-slate-100 p-4 rounded-2xl space-y-2">
                <div id="sidebar-cart-shipping-text" class="text-xs font-semibold text-slate-600">
                    Kargo bedava avantajı için <span class="font-bold text-rose-600" id="sidebar-shipping-remaining">0.00 ₺</span> değerinde daha ürün ekleyin!
                </div>
                <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                    <div id="sidebar-shipping-progress" class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <!-- Items List -->
            <div id="sidebar-cart-items" class="space-y-4">
                <!-- Dynamic cart items loaded via JS -->
            </div>

            <!-- Empty Cart State -->
            <div id="sidebar-cart-empty" class="hidden py-12 flex flex-col items-center justify-center text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Sepetiniz Boş</h3>
                    <p class="text-xs text-slate-400 mt-1">Harika çiçek tasarımlarımızı incelemeye başlayabilirsiniz!</p>
                </div>
                <a href="/" onclick="closeSidebarCart()" class="inline-flex items-center justify-center px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                    Alışverişe Başla
                </a>
            </div>

            <!-- Suggestion Box ("Before you go") -->
            <div id="sidebar-cart-suggestions-section" class="space-y-3">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider">Bunları da Eklemek İster misiniz?</h4>
                <div id="sidebar-cart-suggestions" class="space-y-3">
                    <!-- Dynamic suggestions loaded via JS -->
                </div>
            </div>
        </div>

        <!-- Footer Summary Area -->
        <div id="sidebar-cart-footer" class="p-5 border-t border-slate-100 bg-slate-50/50 space-y-4">
            <div class="flex items-center justify-between text-sm">
                <span class="font-bold text-slate-600">Ara Toplam:</span>
                <span id="sidebar-cart-subtotal" class="font-black text-slate-900 text-lg">0.00 ₺</span>
            </div>
            
            <a href="/sepet" class="w-full flex items-center justify-center py-3.5 border border-slate-200 hover:border-rose-500 hover:text-rose-600 text-slate-700 font-bold rounded-xl text-xs tracking-wider uppercase transition">
                SEPETE GİT
            </a>
            
            <a href="/odeme" class="w-full flex items-center justify-center gap-1.5 py-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs tracking-wider uppercase transition shadow-md shadow-rose-600/10">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                ÖDEME ADIMINA GEÇ
            </a>

            <!-- Payment Logos -->
            <div class="flex items-center justify-center gap-3 opacity-50 pt-2 grayscale">
                <span class="text-[9px] font-bold tracking-wider px-1.5 py-0.5 border border-slate-400 rounded">VISA</span>
                <span class="text-[9px] font-bold tracking-wider px-1.5 py-0.5 border border-slate-400 rounded">MASTERCARD</span>
                <span class="text-[9px] font-bold tracking-wider px-1.5 py-0.5 border border-slate-400 rounded">AMEX</span>
                <span class="text-[9px] font-bold tracking-wider px-1.5 py-0.5 border border-slate-400 rounded">TROY</span>
            </div>
            <div class="text-[10px] text-slate-400 font-bold text-center">
                Çiçekleriniz taze olarak teslim edilecektir!
            </div>
        </div>
    </div>

    @include('frontend.partials.push-notification-prompt')
</body>
</html>

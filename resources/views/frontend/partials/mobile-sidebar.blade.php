{{-- Soldan açılan mobil sidebar menü (panelden yönetilir: Menüler > Mobil Sidebar Menü) --}}
@php
    $sbSettings = \App\Models\Setting::pluck('value', 'key')->toArray();
    $sbQuickActions = filter_var($sbSettings['mobile_sidebar_quick_actions_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $sbContact = filter_var($sbSettings['mobile_sidebar_contact_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

    $sidebarMenu = \App\Models\Menu::where('slug', 'mobile-sidebar-menu')->first()
        ?? \App\Models\Menu::where('slug', 'header-menu')->first();
    $sidebarItems = $sidebarMenu
        ? $sidebarMenu->menuItems()->where('is_active', true)->orderBy('order', 'asc')->get()
        : collect();

    $sbWhatsappUrl = $whatsapp ? 'https://api.whatsapp.com/send?phone=' . preg_replace('/[^0-9]/', '', $whatsapp) : null;
    $sbPhoneUrl = $phone ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : null;
@endphp

<!-- Karartma -->
<div id="mobile-sidebar-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"></div>

<!-- Panel -->
<aside id="mobile-sidebar" class="fixed top-0 left-0 bottom-0 w-[300px] max-w-[85vw] bg-white z-[61] flex-col md:hidden overflow-y-auto overscroll-contain" style="display: none; margin-left: -320px; transition: margin-left .25s ease;">

    <!-- Üst: logo + kapat -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 shrink-0">
        <a href="/" class="flex items-center">
            @if($siteLogo)
                <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto max-w-[170px] object-contain">
            @else
                <span class="font-serif text-xl font-extrabold tracking-tight">
                    <span class="text-amber-500">Lav</span> <span class="text-rose-600 font-light">Çiçekçilik</span>
                </span>
            @endif
        </a>
        <button type="button" id="mobile-sidebar-close" class="p-2 text-slate-400 hover:text-rose-600 transition" aria-label="Menüyü Kapat">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    </div>

    @if($sbQuickActions)
        <!-- Hızlı işlemler -->
        <div class="px-4 py-4 border-b border-slate-100 shrink-0">
            @if(auth('customer')->check())
                <div class="grid grid-cols-4 gap-2">
                    <a href="{{ route('customer.account') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl border border-slate-100 bg-white shadow-sm hover:border-rose-200 transition">
                        @include('frontend.partials.menu-icon', ['icon' => 'user', 'class' => 'w-5 h-5 text-slate-600'])
                        <span class="text-[10px] font-bold text-slate-600">Hesabım</span>
                    </a>
                    <a href="{{ route('cart.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl border border-slate-100 bg-white shadow-sm hover:border-rose-200 transition">
                        @include('frontend.partials.menu-icon', ['icon' => 'cart', 'class' => 'w-5 h-5 text-slate-600'])
                        <span class="text-[10px] font-bold text-slate-600">Sepet</span>
                    </a>
                    <a href="{{ route('tracking') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl border border-slate-100 bg-white shadow-sm hover:border-rose-200 transition">
                        @include('frontend.partials.menu-icon', ['icon' => 'package', 'class' => 'w-5 h-5 text-slate-600'])
                        <span class="text-[10px] font-bold text-slate-600">Takip</span>
                    </a>
                    <a href="{{ route('checkout.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl border border-slate-100 bg-white shadow-sm hover:border-rose-200 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5 text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-5.625 3h18.75a1.875 1.875 0 0 0 1.875-1.875V5.25A1.875 1.875 0 0 0 22.5 3.375H3.75A1.875 1.875 0 0 0 1.875 5.25v13.5A1.875 1.875 0 0 0 3.75 20.625Z" /></svg>
                        <span class="text-[10px] font-bold text-slate-600">Ödeme</span>
                    </a>
                </div>
            @else
                <div class="flex gap-2">
                    <a href="{{ route('customer.login') }}" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                        @include('frontend.partials.menu-icon', ['icon' => 'user', 'class' => 'w-4 h-4'])
                        Giriş Yap / Kayıt Ol
                    </a>
                    <a href="{{ route('tracking') }}" class="flex items-center justify-center gap-1.5 px-4 py-2.5 border border-slate-200 text-slate-600 hover:border-rose-300 hover:text-rose-600 rounded-xl text-xs font-bold transition">
                        @include('frontend.partials.menu-icon', ['icon' => 'package', 'class' => 'w-4 h-4'])
                        Takip
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- Menü öğeleri -->
    <nav class="flex-grow px-2 py-3">
        @foreach($sidebarItems as $item)
            <a href="{{ $item->url }}" target="{{ $item->target }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-semibold text-slate-700 hover:bg-rose-50 hover:text-rose-600 transition border-b border-slate-50 last:border-0">
                @if($item->icon)
                    @include('frontend.partials.menu-icon', ['icon' => $item->icon, 'class' => 'w-5 h-5 text-slate-400'])
                @endif
                {{ $item->title }}
            </a>
        @endforeach
    </nav>

    @if($sbContact)
        <!-- İletişim -->
        <div class="px-4 py-4 border-t border-slate-100 space-y-3 shrink-0 pb-6">
            @if($sbPhoneUrl)
                <a href="{{ $sbPhoneUrl }}" class="flex items-center gap-3 text-sm font-semibold text-slate-600 hover:text-rose-600 transition">
                    @include('frontend.partials.menu-icon', ['icon' => 'phone', 'class' => 'w-5 h-5 text-emerald-600'])
                    {{ $phone }}
                </a>
            @endif
            @if($sbWhatsappUrl)
                <a href="{{ $sbWhatsappUrl }}" target="_blank" rel="noopener" class="flex items-center gap-3 text-sm font-semibold text-slate-600 hover:text-rose-600 transition">
                    @include('frontend.partials.menu-icon', ['icon' => 'whatsapp', 'class' => 'w-5 h-5 text-emerald-600'])
                    WhatsApp
                </a>
            @endif
            @if($email)
                <a href="mailto:{{ $email }}" class="flex items-center gap-3 text-sm font-semibold text-slate-600 hover:text-rose-600 transition break-all">
                    @include('frontend.partials.menu-icon', ['icon' => 'mail', 'class' => 'w-5 h-5 text-emerald-600 shrink-0'])
                    {{ $email }}
                </a>
            @endif
        </div>
    @endif
</aside>

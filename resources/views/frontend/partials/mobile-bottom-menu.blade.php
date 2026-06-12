{{-- Mobilde alta yapışık menü (panelden yönetilir: Menüler > Mobil Alt Menü) --}}
@php
    $bmSettings = \App\Models\Setting::pluck('value', 'key')->toArray();
    $bmActive = filter_var($bmSettings['mobile_bottom_menu_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

    $bottomMenu = \App\Models\Menu::where('slug', 'mobile-bottom-menu')->first();
    $bottomItems = ($bmActive && $bottomMenu)
        ? $bottomMenu->menuItems()->where('is_active', true)->orderBy('order', 'asc')->get()
        : collect();

    // WhatsApp/Telefon öğelerinde URL "#" ise iletişim ayarlarından otomatik çöz
    $bmResolveUrl = function ($item) use ($bmSettings) {
        $url = trim($item->url ?? '');
        if ($url !== '' && $url !== '#') {
            return $url;
        }
        if ($item->icon === 'whatsapp' && !empty($bmSettings['site_whatsapp'])) {
            return 'https://api.whatsapp.com/send?phone=' . preg_replace('/[^0-9]/', '', $bmSettings['site_whatsapp']);
        }
        if ($item->icon === 'phone' && !empty($bmSettings['site_phone'])) {
            return 'tel:' . preg_replace('/[^0-9+]/', '', $bmSettings['site_phone']);
        }
        return $url === '' ? '#' : $url;
    };
@endphp

@if($bottomItems->isNotEmpty())
<!-- Sabit menünün içeriği örtmemesi için boşluk -->
<div class="h-14 md:hidden" aria-hidden="true"></div>
<nav id="mobile-bottom-menu" class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-slate-200" style="padding-bottom: env(safe-area-inset-bottom);">
    <div class="grid" style="grid-template-columns: repeat({{ $bottomItems->count() }}, minmax(0, 1fr));">
        @foreach($bottomItems as $item)
            @php
                $resolvedUrl = $bmResolveUrl($item);
                $isCurrent = $item->url === '/' && request()->is('/');
            @endphp
            <a href="{{ $resolvedUrl }}" target="{{ $item->target }}"
               class="flex flex-col items-center justify-center gap-0.5 py-2 {{ $isCurrent ? 'text-rose-600' : 'text-slate-500' }} hover:text-rose-600 transition">
                @include('frontend.partials.menu-icon', ['icon' => $item->icon ?: 'flower', 'class' => 'w-5 h-5'])
                <span class="text-[10px] font-semibold leading-none">{{ $item->title }}</span>
            </a>
        @endforeach
    </div>
</nav>
@endif

{{-- Sağ kenara yapışık Sipariş Takip sekmesi ve sorgulama kartı (panelden açılıp kapanabilir) --}}
@php
    $otwActive = filter_var(\App\Models\Setting::where('key', 'order_tracking_widget_active')->value('value') ?? '1', FILTER_VALIDATE_BOOLEAN);
@endphp

@if($otwActive)
<style>
    /* Sipariş takip sekmesi yalnızca masaüstünde gösterilir */
    @media (max-width: 767px) {
        #otw-tab, #otw-card, #otw-overlay { display: none !important; }
    }
</style>
<!-- Dikey sekme -->
<button type="button" id="otw-tab" aria-label="Sipariş Takip"
    class="fixed right-0 top-1/2 -translate-y-1/2 z-40 bg-rose-600 hover:bg-rose-700 text-white shadow-lg transition-colors"
    style="writing-mode: vertical-rl; transform: translateY(-50%) rotate(180deg); padding: 1rem .55rem; font-size: .75rem; font-weight: 700; letter-spacing: .08em; border-radius: 0px 10px 10px 0px;">
    <span class="inline-flex items-center gap-2">
        Sipariş Takip
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: .9rem; height: .9rem; transform: rotate(90deg);">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
        </svg>
    </span>
</button>

<!-- Karartma -->
<div id="otw-overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[55]" style="display: none;"></div>

<!-- Sorgulama kartı -->
<div id="otw-card" class="fixed right-3 top-1/2 -translate-y-1/2 z-[56] w-[330px] max-w-[92vw] bg-white rounded-2xl overflow-hidden border border-rose-100" style="display: none; box-shadow: 0 18px 40px -12px rgb(0 0 0 / .3);">
    <!-- Başlık çubuğu -->
    <div class="bg-rose-600 text-white flex items-center justify-between px-4 py-3">
        <span class="text-sm font-bold tracking-wide">Sipariş Takip</span>
        <button type="button" id="otw-close" class="p-1 hover:opacity-75 transition" aria-label="Kapat">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- İçerik -->
    <form action="{{ route('tracking') }}" method="GET" class="p-5 space-y-4">
        <div class="flex items-center justify-center gap-2 text-rose-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125V11.25M3 14.25h15m0 0V8.25m0 0h-.882c-.832 0-1.63-.329-2.225-.916L12.75 5.03a2.25 2.25 0 0 0-1.59-.657H5.25a2.25 2.25 0 0 0-2.25 2.25v7.425" />
            </svg>
            <span class="text-sm font-bold">Siparişiniz nerede?</span>
        </div>
        <p class="text-xs text-slate-500 text-center leading-relaxed">
            Sipariş numaranızı girerek çiçeğinizin hazırlanma ve teslimat durumunu anlık görüntüleyebilirsiniz.
        </p>
        <div>
            <input
                type="text"
                name="order_number"
                required
                placeholder="Örn: LAV-20260612-AB12C"
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition"
            >
        </div>
        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm py-2.5 rounded-xl transition shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
            </svg>
            Sorgula
        </button>
        <p class="text-[10px] text-slate-400 text-center">
            Sipariş numaranız onay mailinizde ve sipariş başarı sayfasında yer alır.
        </p>
    </form>
</div>

<script>
    (function () {
        const tab = document.getElementById('otw-tab');
        const card = document.getElementById('otw-card');
        const overlay = document.getElementById('otw-overlay');
        const closeBtn = document.getElementById('otw-close');

        if (!tab || !card || !overlay) return;

        function openCard() {
            card.style.display = 'block';
            overlay.style.display = 'block';
            tab.style.display = 'none';
            const input = card.querySelector('input[name=order_number]');
            if (input) input.focus();
        }
        function closeCard() {
            card.style.display = 'none';
            overlay.style.display = 'none';
            tab.style.display = '';
        }

        tab.addEventListener('click', openCard);
        if (closeBtn) closeBtn.addEventListener('click', closeCard);
        overlay.addEventListener('click', closeCard);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeCard();
        });
    })();
</script>
@endif

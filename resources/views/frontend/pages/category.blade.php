@extends('frontend.layouts.app')

@section('content')
    <div class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Category Header -->
            <div class="border-b border-rose-100 pb-10 mb-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-2xl">
                        <!-- Breadcrumbs -->
                        <nav class="flex text-xs text-slate-400 font-semibold mb-4 uppercase tracking-wider gap-2">
                            <a href="/" class="hover:text-rose-600 transition">Ana Sayfa</a>
                            <span>/</span>
                            <span class="text-slate-600">{{ $category->name }}</span>
                        </nav>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif leading-tight">
                            {{ $category->name }}
                        </h1>
                        @if($category->description)
                            <p class="mt-3 text-slate-500 text-sm leading-relaxed">{{ $category->description }}</p>
                        @endif
                    </div>
                    
                    <!-- Sorting & Filter Controls -->
                    <div class="flex items-center gap-4 self-end md:self-auto">
                        <form method="GET" action="" class="flex items-center gap-3">
                            <!-- Keep search query if searching -->
                            @if(request()->has('q'))
                                <input type="hidden" name="q" value="{{ request('q') }}">
                            @endif
                            
                            <!-- Price Range Filters -->
                            <div class="hidden lg:flex items-center gap-2">
                                <input type="number" name="price_min" placeholder="Min ₺" value="{{ request('price_min') }}" class="w-20 px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-rose-500">
                                <span class="text-slate-300">-</span>
                                <input type="number" name="price_max" placeholder="Max ₺" value="{{ request('price_max') }}" class="w-20 px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-rose-500">
                            </div>
                            
                            <!-- Custom Sort Dropdown -->
                            <div class="relative inline-block text-left" id="sort-dropdown-container">
                                <button type="button" id="sort-dropdown-btn" class="flex items-center justify-between gap-3 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:border-rose-400 hover:text-rose-600 transition duration-300 focus:outline-none focus:ring-1 focus:ring-rose-500 cursor-pointer min-w-[160px]">
                                    <span id="sort-selected-label">Önerilen Sıralama</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 group-hover:text-rose-600 transition">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                
                                <div id="sort-dropdown-menu" class="hidden absolute right-0 z-30 mt-2 w-48 bg-white border border-slate-100 rounded-xl shadow-lg focus:outline-none py-1.5 transition-all duration-200 origin-top-right">
                                    <div class="py-1">
                                        <button type="button" class="sort-option block w-full text-left px-4 py-2 text-xs font-medium transition duration-150 hover:bg-rose-50 hover:text-rose-600 cursor-pointer text-slate-600" data-value="default">Önerilen Sıralama</button>
                                        <button type="button" class="sort-option block w-full text-left px-4 py-2 text-xs font-medium transition duration-150 hover:bg-rose-50 hover:text-rose-600 cursor-pointer text-slate-600" data-value="price_asc">Fiyata Göre (Artan)</button>
                                        <button type="button" class="sort-option block w-full text-left px-4 py-2 text-xs font-medium transition duration-150 hover:bg-rose-50 hover:text-rose-600 cursor-pointer text-slate-600" data-value="price_desc">Fiyata Göre (Azalan)</button>
                                        <button type="button" class="sort-option block w-full text-left px-4 py-2 text-xs font-medium transition duration-150 hover:bg-rose-50 hover:text-rose-600 cursor-pointer text-slate-600" data-value="newest">En Yeniler</button>
                                    </div>
                                </div>
                                
                                <select name="sort" id="real-sort-select" class="hidden">
                                    <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Önerilen Sıralama</option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Fiyata Göre (Artan)</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Fiyata Göre (Azalan)</option>
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>En Yeniler</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="lg:hidden px-3 py-2 bg-rose-50 text-rose-600 rounded-xl text-xs font-bold hover:bg-rose-100 transition">
                                Filtrele
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-8">
                    @foreach($products as $product)
                        <div class="group relative bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-rose-600 transition duration-300 flex flex-col justify-between product-card-container">
                            <div>
                                <!-- Product Image -->
                                <div class="relative aspect-square overflow-hidden bg-rose-50/50">
                                    @if($product->discount_price)
                                        <span class="absolute top-4 left-4 bg-rose-600 text-white text-[11px] font-bold px-3 py-1 rounded-full z-10 shadow-sm uppercase tracking-wide">İndirim</span>
                                    @endif

                                    <!-- Favorite Toggle Button -->
                                    <button type="button" class="favorite-toggle-btn absolute top-4 right-4 z-20 p-2 rounded-full bg-white/80 hover:bg-white text-slate-500 hover:text-rose-600 shadow-md transition duration-300 focus:outline-none" data-product-id="{{ $product->id }}" title="{{ $product->isFavoritedByCurrentUser() ? 'Favorilerden Çıkar' : 'Favorilere Ekle' }}">
                                        @if($product->isFavoritedByCurrentUser())
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-rose-600">
                                                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                            </svg>
                                        @endif
                                    </button>

                                    <a href="{{ route('product', $product->slug) }}">
                                        @if($product->mainImage && $product->mainImage->url)
                                            <img src="{{ $product->mainImage->url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                        @else
                                            <div class="absolute inset-0 bg-gradient-to-br from-rose-100 to-rose-200 flex items-center justify-center group-hover:scale-105 transition duration-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="w-12 h-12 text-rose-500/20">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
                                                </svg>
                                            </div>
                                        @endif
                                    </a>
                                </div>

                                <!-- Detail -->
                                <div class="p-6">
                                    <a href="{{ route('product', $product->slug) }}" class="block">
                                        <h3 class="font-serif text-lg font-bold text-slate-800 hover:text-rose-600 transition truncate">{{ $product->name }}</h3>
                                    </a>
                                    <p class="mt-2 text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ $product->short_description }}</p>
                                </div>
                            </div>

                            <div class="p-6 pt-0 border-t border-slate-50 flex items-center justify-between gap-4 mt-auto">
                                <div class="flex flex-col">
                                    @if($product->discount_price)
                                        <span class="text-xs text-slate-400 line-through">₺{{ number_format($product->price, 2) }}</span>
                                        <span class="text-lg font-black text-rose-600">₺{{ number_format($product->discount_price, 2) }}</span>
                                    @else
                                        <span class="text-lg font-black text-slate-800">₺{{ number_format($product->price, 2) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('product', $product->slug) }}" class="p-3 bg-slate-900 text-white rounded-xl hover:bg-rose-600 shadow-sm hover:shadow-rose-100 transition duration-300">
                                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19.3 21" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                         <path d="M13.4,9v-4.5c0-2.1-1.7-3.8-3.8-3.8s-3.8,1.7-3.8,3.8v4.5M17.2,7l1.3,12c0,.7-.5,1.2-1.1,1.2H1.9c-.6,0-1.1-.5-1.1-1.1,0,0,0,0,0-.1l1.3-12c0-.6.5-1,1.1-1h13c.6,0,1.1.4,1.1,1ZM6.3,9c0,.2-.2.4-.4.4s-.4-.2-.4-.4.2-.4.4-.4.4.2.4.4ZM13.8,9c0,.2-.2.4-.4.4s-.4-.2-.4-.4.2-.4.4-.4.4.2.4.4Z"/>
                                         <path d="M13.3,13.2c0-1-.8-1.8-1.9-1.8s-1.4.4-1.7,1.1c-.3-.6-.9-1.1-1.7-1.1-1,0-1.9.8-1.9,1.8,0,2.9,3.6,4.8,3.6,4.8,0,0,3.6-1.9,3.6-4.8Z"/>
                                     </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Area -->
                <div class="mt-16 flex justify-center">
                    {{ $products->links() }}
                </div>
            @else
                <!-- Empty Results -->
                <div class="py-24 text-center max-w-md mx-auto">
                    <div class="inline-flex p-6 bg-rose-50 text-rose-600 rounded-full mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0 0 12.016 15a4.486 4.486 0 0 0-3.198 1.318M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 font-serif">Aradığınız Çiçek Bulunamadı</h2>
                    <p class="text-slate-500 text-sm mt-3">Belirtilen kategoride veya kriterlerde aktif ürünümüz bulunmamaktadır. Lütfen diğer kategorileri inceleyin.</p>
                    <a href="/" class="mt-8 inline-block bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm px-8 py-3 rounded-full transition shadow-lg hover:shadow-rose-100">
                        Ana Sayfaya Dön
                    </a>
                </div>
            @endif

            @if(!empty($category->seo_description))
                <!-- Category SEO Description Accordion -->
                <div class="mt-20 border-t border-slate-100 pt-10">
                    <div class="bg-slate-50/50 rounded-2xl border border-slate-100 p-6 sm:p-8 transition-all duration-300">
                        <button type="button" id="seo-accordion-toggle" class="flex items-center justify-between w-full text-left font-serif text-lg sm:text-xl font-bold text-slate-800 hover:text-rose-600 transition focus:outline-none cursor-pointer">
                            <span>{{ $category->name }} Hakkında Detaylı Bilgi</span>
                            <svg id="seo-accordion-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-slate-400 transition-transform duration-300">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        
                        <div id="seo-accordion-content" class="grid grid-rows-[0fr] transition-all duration-300 ease-in-out">
                            <div class="overflow-hidden">
                                <div class="category-seo-description mt-5 space-y-4">
                                    {!! $category->seo_description !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const toggleBtn = document.getElementById('seo-accordion-toggle');
                        const content = document.getElementById('seo-accordion-content');
                        const icon = document.getElementById('seo-accordion-icon');
                        
                        if (!toggleBtn || !content || !icon) return;
                        
                        toggleBtn.addEventListener('click', () => {
                            const isOpen = content.classList.contains('grid-rows-[1fr]');
                            if (isOpen) {
                                content.classList.remove('grid-rows-[1fr]');
                                content.classList.add('grid-rows-[0fr]');
                                icon.classList.remove('rotate-180');
                            } else {
                                content.classList.remove('grid-rows-[0fr]');
                                content.classList.add('grid-rows-[1fr]');
                                icon.classList.add('rotate-180');
                            }
                        });
                    });
                </script>
            @endif

        </div>
    </div>

    <!-- Custom Sort Dropdown Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('sort-dropdown-btn');
            const menu = document.getElementById('sort-dropdown-menu');
            const label = document.getElementById('sort-selected-label');
            const realSelect = document.getElementById('real-sort-select');
            
            if (!btn || !menu || !realSelect) return;
            
            // Sync initial state and style the active item
            const currentVal = realSelect.value;
            const activeOption = menu.querySelector(`[data-value="${currentVal}"]`);
            if (activeOption) {
                label.textContent = activeOption.textContent;
                activeOption.classList.remove('text-slate-600');
                activeOption.classList.add('text-rose-600', 'bg-rose-50/50', 'font-semibold');
            }
            
            // Toggle menu on button click
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });
            
            // Close menu when clicking anywhere else
            document.addEventListener('click', () => {
                menu.classList.add('hidden');
            });
            
            // Handle item selection
            const options = menu.querySelectorAll('.sort-option');
            options.forEach(opt => {
                opt.addEventListener('click', () => {
                    const val = opt.getAttribute('data-value');
                    realSelect.value = val;
                    menu.classList.add('hidden');
                    realSelect.form.submit();
                });
            });
        });
    </script>
@endsection

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

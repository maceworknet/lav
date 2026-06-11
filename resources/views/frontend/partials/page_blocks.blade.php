{{--
    Sayfa bloklarini render eden ortak partial.
    Ana sayfa dahil tum sayfalar bu partial ile panelden yonetilen bloklari gosterir.
    Bloklarin ihtiyac duydugu koleksiyonlar controller tarafindan saglanmadiysa burada yuklenir.
--}}
@php
    $pageBlockTypes = ($page ?? null) ? $page->pageBlocks->pluck('type') : collect();

    if (!isset($categories) && $pageBlockTypes->intersect(['category_slider', 'category_grid'])->isNotEmpty()) {
        $categories = \App\Models\Category::where('is_active', true)->orderBy('order', 'asc')->get();
    }
    if (!isset($featuredProducts) && $pageBlockTypes->contains('product_carousel')) {
        $featuredProducts = \App\Models\Product::where('is_featured', true)->where('stock_status', true)->with('images')->take(8)->get();
    }
    if (!isset($handpickedProducts) && $pageBlockTypes->contains('handpicked_products')) {
        $handpickedProducts = \App\Models\Product::where('stock_status', true)->with('images')->take(20)->get();
    }
    if (!isset($latestBlogPosts) && $pageBlockTypes->contains('blog_posts')) {
        $latestBlogPosts = \App\Models\BlogPost::where('is_active', true)->latest()->take(3)->get();
    }

    // Mobilde kategori şeridini en üste taşıma ayarı (panelden yönetilir)
    $mobileCategoriesFirst = filter_var(\App\Models\Setting::where('key', 'mobile_categories_first')->value('value') ?? '1', FILTER_VALIDATE_BOOLEAN);
@endphp
<style>
    @media (max-width: 767px) {
        .page-blocks-wrapper { display: flex; flex-direction: column; }
        .page-blocks-wrapper > .mobile-order-first { order: -1; }
    }
</style>
<div class="page-blocks-wrapper">
    @if($page && $page->pageBlocks->count() > 0)
        @foreach($page->pageBlocks as $block)
            @php
                $content = $block->content;
            @endphp

            @if($block->type === 'hero_slider')
                <!-- Hero Slider -->
                @php
                    $desktopHeight = $content['desktop_height'] ?? '500px';
                    $mobileHeight = $content['mobile_height'] ?? '350px';
                    $slides = $content['slides'] ?? [];
                    $sliderId = 'hero-slider-' . $block->id;
                @endphp
                
                <div id="{{ $sliderId }}" class="relative overflow-hidden w-full group" style="--slider-h-desktop: {{ $desktopHeight }}; --slider-h-mobile: {{ $mobileHeight }};">
                    <!-- Slides Container -->
                    <div class="slides-container flex transition-transform duration-500 ease-out h-[var(--slider-h-mobile)] md:h-[var(--slider-h-desktop)]">
                        @foreach($slides as $index => $slide)
                            @php
                                $desktopImg = $slide['desktop_image'] ?? '';
                                $mobileImg = $slide['mobile_image'] ?? $desktopImg;
                                
                                $desktopUrl = str_starts_with($desktopImg, 'http') || str_starts_with($desktopImg, '/') || str_starts_with($desktopImg, 'assets/') 
                                    ? asset($desktopImg) 
                                    : asset('storage/' . $desktopImg);
                                    
                                $mobileUrl = str_starts_with($mobileImg, 'http') || str_starts_with($mobileImg, '/') || str_starts_with($mobileImg, 'assets/') 
                                    ? asset($mobileImg) 
                                    : asset('storage/' . $mobileImg);
                            @endphp
                            
                            @php
                                $slideUrl = $slide['button_url'] ?? '';
                            @endphp

                            @if(!empty($slideUrl))
                                <a href="{{ $slideUrl }}" class="slide-item w-full flex-shrink-0 relative overflow-hidden h-full block">
                            @else
                                <div class="slide-item w-full flex-shrink-0 relative overflow-hidden h-full">
                            @endif
                                    <!-- Background Image (Desktop & Mobile responsive) -->
                                    <picture class="absolute inset-0 w-full h-full">
                                        <source media="(max-width: 768px)" srcset="{{ $mobileUrl }}">
                                        <img src="{{ $desktopUrl }}" alt="Slide Image" class="w-full h-full object-cover">
                                    </picture>
                            @if(!empty($slideUrl))
                                </a>
                            @else
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Navigation Arrows -->
                    @if(count($slides) > 1)
                        <button type="button" class="slider-prev absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/70 hover:bg-white text-slate-800 flex items-center justify-center shadow-lg transition-opacity duration-300 opacity-0 group-hover:opacity-100 z-30 focus:outline-none" aria-label="Önceki Slayt">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <button type="button" class="slider-next absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/70 hover:bg-white text-slate-800 flex items-center justify-center shadow-lg transition-opacity duration-300 opacity-0 group-hover:opacity-100 z-30 focus:outline-none" aria-label="Sonraki Slayt">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12 8.25 19.5" />
                            </svg>
                        </button>
                    @endif

                    <!-- Indicator Dots -->
                    @if(count($slides) > 1)
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2.5 z-30">
                            @foreach($slides as $index => $slide)
                                <button type="button" class="slider-dot w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-all duration-300 focus:outline-none" data-slide-index="{{ $index }}" aria-label="Slayt {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Slider JS Initialization -->
                @if(count($slides) > 1)
                    <script>
                        (function() {
                            const slider = document.getElementById('{{ $sliderId }}');
                            if (!slider) return;

                            const container = slider.querySelector('.slides-container');
                            const slides = slider.querySelectorAll('.slide-item');
                            const prevBtn = slider.querySelector('.slider-prev');
                            const nextBtn = slider.querySelector('.slider-next');
                            const dots = slider.querySelectorAll('.slider-dot');
                            
                            let currentIndex = 0;
                            const totalSlides = slides.length;
                            let autoPlayInterval;

                            function updateSlider() {
                                container.style.transform = `translateX(-${currentIndex * 100}%)`;
                                
                                // Update active dots
                                dots.forEach((dot, idx) => {
                                    if (idx === currentIndex) {
                                        dot.classList.remove('bg-white/50', 'w-3');
                                        dot.classList.add('bg-white', 'w-6');
                                    } else {
                                        dot.classList.remove('bg-white', 'w-6');
                                        dot.classList.add('bg-white/50', 'w-3');
                                    }
                                });
                            }

                            function nextSlide() {
                                currentIndex = (currentIndex + 1) % totalSlides;
                                updateSlider();
                            }

                            function prevSlide() {
                                currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                                updateSlider();
                            }

                            if (prevBtn && nextBtn) {
                                prevBtn.addEventListener('click', () => {
                                    prevSlide();
                                    resetAutoPlay();
                                });
                                nextBtn.addEventListener('click', () => {
                                    nextSlide();
                                    resetAutoPlay();
                                });
                            }

                            dots.forEach(dot => {
                                dot.addEventListener('click', () => {
                                    currentIndex = parseInt(dot.getAttribute('data-slide-index'));
                                    updateSlider();
                                    resetAutoPlay();
                                });
                            });

                            function startAutoPlay() {
                                autoPlayInterval = setInterval(nextSlide, 5000);
                            }

                            function resetAutoPlay() {
                                clearInterval(autoPlayInterval);
                                startAutoPlay();
                            }

                            // Initialize
                            updateSlider();
                            startAutoPlay();
                        })();
                    </script>
                 @endif
             @endif

             @if($block->type === 'category_slider')
                 <!-- Category Slider Component (Premium Rounded Carousel) -->
                 @php
                     $catTitle = array_key_exists('cat_slider_title', $content) ? $content['cat_slider_title'] : ($content['title'] ?? null);
                     $catSubtitle = array_key_exists('cat_slider_subtitle', $content) ? $content['cat_slider_subtitle'] : ($content['subtitle'] ?? null);
                 @endphp
                 <div class="py-6 md:py-12 bg-white relative {{ $mobileCategoriesFirst ? 'mobile-order-first' : '' }}">
                     <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative group/cat-carousel">
                         @if(!empty($catTitle) || !empty($catSubtitle))
                             <div class="text-center max-w-3xl mx-auto mb-10">
                                 @if(!empty($catTitle))
                                     <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif">
                                         {{ $catTitle }}
                                     </h2>
                                 @endif
                                 @if(!empty($catSubtitle))
                                     <p class="mt-2 text-slate-500 text-sm">
                                         {{ $catSubtitle }}
                                     </p>
                                 @endif
                             </div>
                         @endif
                         
                         <!-- Viewport + Navigation Container -->
                         <div class="relative">
                             <!-- Viewport -->
                             <div class="overflow-hidden relative px-4 sm:px-6" id="cat-carousel-viewport">
                                 <div id="cat-carousel-track" class="flex transition-transform duration-500 ease-out gap-6 py-2">
                                     @php
                                         $pastelBgs = [
                                             '#FDE2E4', // Soft rose/pink
                                             '#E0F4F7', // Soft sky/cyan
                                             '#F1E5F9', // Soft lavender
                                             '#FCEADE', // Soft peach/beige
                                             '#ECE4FF', // Soft pinkish/purple
                                             '#FFF0F5', // Soft cream/rose
                                             '#E2E2F9', // Soft blue/violet
                                             '#E8F8F5', // Soft mint/teal
                                         ];
                                     @endphp
                                     @foreach($categories as $index => $category)
                                         @php
                                             $bgStyle = 'background-color: ' . $pastelBgs[$index % count($pastelBgs)] . ';';
                                             $imgUrl = $category->image ? asset('storage/' . $category->image) : null;
                                         @endphp
                                         <div class="cat-carousel-item flex-shrink-0 flex flex-col items-center group cursor-pointer" style="width: calc((100% - (var(--visible-items, 4) - 1) * var(--cat-gap, 12px)) / var(--visible-items, 4));">
                                             <a href="{{ route('category', $category->slug) }}" class="w-full flex flex-col items-center">
                                                 <!-- Rounded square image container -->
                                                 <div class="w-full aspect-square rounded-2xl flex items-center justify-center relative shadow-sm transition duration-300 group-hover:scale-102 group-hover:shadow-md" style="{{ $bgStyle }}">
                                                     @if($imgUrl)
                                                         <div class="absolute inset-0 flex items-center justify-center p-4">
                                                             <img src="{{ $imgUrl }}" alt="{{ $category->name }}" class="max-w-full max-h-full object-contain transition duration-300 transform group-hover:scale-105 select-none">
                                                         </div>
                                                     @else
                                                         <!-- Fallback svg if no featured image is uploaded -->
                                                         <div class="text-rose-600/30 transition duration-300 transform group-hover:scale-105">
                                                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="w-12 h-12">
                                                                 <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
                                                             </svg>
                                                         </div>
                                                     @endif
                                                 </div>
                                                 <!-- Category Name -->
                                                 <span class="mt-3 block text-center text-xs font-medium text-slate-600 transition duration-300 group-hover:text-rose-600 truncate max-w-full px-1">
                                                     {{ $category->name }}
                                                 </span>
                                             </a>
                                         </div>
                                     @endforeach
                                 </div>
                             </div>

                             <!-- Left & Right Arrow Buttons -->
                             <button type="button" id="cat-prev-btn" class="absolute left-0 -translate-y-1/2 -translate-x-1 sm:-translate-x-3 w-6 h-6 sm:w-10 sm:h-10 rounded-full bg-white hover:bg-slate-50 text-slate-800 flex items-center justify-center shadow-md sm:shadow-lg border border-slate-100 transition-all duration-300 z-30 focus:outline-none hover:scale-105" aria-label="Önceki Kategoriler">
                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.8" stroke="currentColor" class="w-3 h-3 sm:w-5 sm:h-5 text-rose-600">
                                     <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                 </svg>
                             </button>
                             <button type="button" id="cat-next-btn" class="absolute right-0 -translate-y-1/2 translate-x-1 sm:translate-x-3 w-6 h-6 sm:w-10 sm:h-10 rounded-full bg-white hover:bg-slate-50 text-slate-800 flex items-center justify-center shadow-md sm:shadow-lg border border-slate-100 transition-all duration-300 z-30 focus:outline-none hover:scale-105" aria-label="Sonraki Kategoriler">
                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.8" stroke="currentColor" class="w-3 h-3 sm:w-5 sm:h-5 text-rose-600">
                                     <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12 8.25 19.5" />
                                 </svg>
                             </button>
                         </div>
                     </div>
                 </div>

                 <style>
                     .cat-carousel-item {
                         transition: width 0.3s ease;
                     }
                     #cat-carousel-viewport {
                         scroll-behavior: smooth;
                         user-select: none;
                         -webkit-user-select: none;
                     }
                     #cat-carousel-viewport img {
                         -webkit-user-drag: none;
                         user-drag: none;
                         pointer-events: none;
                     }
                     :root {
                         --visible-items: 4;   /* Mobilde 4 kart görünür */
                         --cat-gap: 12px;
                     }
                     #cat-carousel-track { gap: var(--cat-gap) !important; }
                     @media (min-width: 640px) {
                         :root {
                             --visible-items: 4;
                             --cat-gap: 24px;
                         }
                     }
                     @media (min-width: 768px) {
                         :root {
                             --visible-items: 6;
                         }
                     }
                     @media (min-width: 1024px) {
                         :root {
                             --visible-items: 8;
                         }
                     }
                 </style>

                 <script>
                     (function() {
                         document.addEventListener('DOMContentLoaded', () => {
                             const viewport = document.getElementById('cat-carousel-viewport');
                             const track = document.getElementById('cat-carousel-track');
                             const prevBtn = document.getElementById('cat-prev-btn');
                             const nextBtn = document.getElementById('cat-next-btn');

                             if (!viewport || !track) return;

                             let currentIndex = 0;
                             let autoPlayInterval;
                             const items = track.children;
                             const totalItems = items.length;

                             if (totalItems === 0) return;

                             function getVisibleItems() {
                                 if (window.innerWidth >= 1024) return 8;
                                 if (window.innerWidth >= 768) return 6;
                                 return 4; // Mobilde 4 kart görünür
                             }

                             function getItemGap() {
                                 return window.innerWidth >= 640 ? 24 : 12;
                             }

                             function updateCarousel() {
                                 const visibleItems = getVisibleItems();
                                 track.style.setProperty('--visible-items', visibleItems);
                                 const maxIndex = Math.max(0, totalItems - visibleItems);
                                 
                                 if (maxIndex === 0) {
                                     if (prevBtn) prevBtn.style.display = 'none';
                                     if (nextBtn) nextBtn.style.display = 'none';
                                     track.style.transform = 'none';
                                     return;
                                 } else {
                                     if (prevBtn) prevBtn.style.display = 'flex';
                                     if (nextBtn) nextBtn.style.display = 'flex';
                                 }

                                 if (currentIndex > maxIndex) {
                                     currentIndex = 0;
                                 } else if (currentIndex < 0) {
                                     currentIndex = maxIndex;
                                 }

                                 if (items.length > 0) {
                                     const itemWidth = items[0].getBoundingClientRect().width;
                                     const gap = getItemGap();
                                     const translateValue = currentIndex * (itemWidth + gap);
                                     track.style.transform = `translateX(-${translateValue}px)`;
                                 }

                                 alignArrows();
                             }

                             // Okları kategori görselinin dikey ortasına hizala
                             function alignArrows() {
                                 const firstImage = track.querySelector('.cat-carousel-item .aspect-square');
                                 if (!firstImage) return;
                                 const trackTop = track.parentElement.getBoundingClientRect().top;
                                 const imgRect = firstImage.getBoundingClientRect();
                                 const centerY = (imgRect.top - trackTop) + (imgRect.height / 2);
                                 if (prevBtn) prevBtn.style.top = centerY + 'px';
                                 if (nextBtn) nextBtn.style.top = centerY + 'px';
                             }

                             function getCurrentTranslate() {
                                 const itemWidth = items.length ? items[0].getBoundingClientRect().width : 0;
                                 return currentIndex * (itemWidth + getItemGap());
                             }

                             // Sürükleyerek kaydırma (masaüstü mouse + mobil dokunmatik)
                             let dragStartX = null;
                             let dragStartTranslate = 0;
                             let dragMoved = false;

                             viewport.style.touchAction = 'pan-y';
                             viewport.style.cursor = 'grab';

                             viewport.addEventListener('pointerdown', (e) => {
                                 dragStartX = e.clientX;
                                 dragStartTranslate = getCurrentTranslate();
                                 dragMoved = false;
                                 track.style.transition = 'none';
                                 viewport.style.cursor = 'grabbing';
                                 clearInterval(autoPlayInterval);
                             });

                             window.addEventListener('pointermove', (e) => {
                                 if (dragStartX === null) return;
                                 const delta = e.clientX - dragStartX;
                                 if (Math.abs(delta) > 5) dragMoved = true;
                                 const itemWidth = items[0].getBoundingClientRect().width;
                                 const gap = getItemGap();
                                 const maxTranslate = Math.max(0, totalItems - getVisibleItems()) * (itemWidth + gap);
                                 let next = dragStartTranslate - delta;
                                 next = Math.max(-40, Math.min(maxTranslate + 40, next)); // hafif esneme payı
                                 track.style.transform = `translateX(-${next}px)`;
                             });

                             window.addEventListener('pointerup', (e) => {
                                 if (dragStartX === null) return;
                                 const delta = e.clientX - dragStartX;
                                 dragStartX = null;
                                 track.style.transition = '';
                                 viewport.style.cursor = 'grab';

                                 const itemWidth = items[0].getBoundingClientRect().width;
                                 const gap = getItemGap();
                                 const step = itemWidth + gap;
                                 const maxIndex = Math.max(0, totalItems - getVisibleItems());

                                 // Bırakılan konuma en yakın karta yapış
                                 const target = (dragStartTranslate - delta) / step;
                                 currentIndex = Math.max(0, Math.min(maxIndex, Math.round(target)));
                                 updateCarousel();
                                 resetAutoPlay();
                             });

                             // Sürükleme sonrası yanlışlıkla linke tıklamayı engelle
                             track.addEventListener('click', (e) => {
                                 if (dragMoved) {
                                     e.preventDefault();
                                     e.stopPropagation();
                                     dragMoved = false;
                                 }
                             }, true);

                             // Görseller yüklenince ok hizasını tazele
                             window.addEventListener('load', alignArrows);

                             function nextSlide() {
                                 const visibleItems = getVisibleItems();
                                 const maxIndex = Math.max(0, totalItems - visibleItems);
                                 if (maxIndex === 0) return;
                                 if (currentIndex >= maxIndex) {
                                     currentIndex = 0;
                                 } else {
                                     currentIndex++;
                                 }
                                 updateCarousel();
                             }

                             function prevSlide() {
                                 const visibleItems = getVisibleItems();
                                 const maxIndex = Math.max(0, totalItems - visibleItems);
                                 if (maxIndex === 0) return;
                                 if (currentIndex <= 0) {
                                     currentIndex = maxIndex;
                                 } else {
                                     currentIndex--;
                                 }
                                 updateCarousel();
                             }

                             // Event Listeners for Buttons
                             if (prevBtn) {
                                 prevBtn.addEventListener('click', (e) => {
                                     e.preventDefault();
                                     prevSlide();
                                     resetAutoPlay();
                                 });
                             }

                             if (nextBtn) {
                                 nextBtn.addEventListener('click', (e) => {
                                     e.preventDefault();
                                     nextSlide();
                                     resetAutoPlay();
                                 });
                             }

                             // Keyboard navigation: left/right arrow keys when hovering
                             let isHovered = false;
                             const containerElement = viewport.parentElement;
                             if (containerElement) {
                                 containerElement.addEventListener('mouseenter', () => {
                                     isHovered = true;
                                 });
                                 containerElement.addEventListener('mouseleave', () => {
                                     isHovered = false;
                                 });
                             }

                             document.addEventListener('keydown', (e) => {
                                 if (!isHovered) return;
                                 if (e.key === 'ArrowRight') {
                                     e.preventDefault();
                                     nextSlide();
                                     resetAutoPlay();
                                 } else if (e.key === 'ArrowLeft') {
                                     e.preventDefault();
                                     prevSlide();
                                     resetAutoPlay();
                                 }
                             });

                             // Auto Play Mechanism
                             function startAutoPlay() {
                                 autoPlayInterval = setInterval(nextSlide, 3500); // Auto slide every 3.5 seconds
                             }

                             function resetAutoPlay() {
                                 clearInterval(autoPlayInterval);
                                 startAutoPlay();
                             }

                             // Window Resize adjustments
                             window.addEventListener('resize', () => {
                                 updateCarousel();
                             });

                             // Initialize
                             updateCarousel();
                             startAutoPlay();
                         });
                     })();
                 </script>
             @endif

             @if($block->type === 'image_grid')
                 <!-- Banner Grid (Görsel Izgarası) -->
                 @php
                     $columns = $content['columns'] ?? '2';
                     $items = $content['banner_items'] ?? $content['items'] ?? [];
                     
                     $gridClass = 'grid-cols-1 md:grid-cols-2';
                     if ($columns === '1') {
                         $gridClass = 'grid-cols-1';
                     } elseif ($columns === '3') {
                         $gridClass = 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
                     } elseif ($columns === '4') {
                         $gridClass = 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4';
                     }
                 @endphp

                 @if(count($items) > 0)
                     <div class="py-12 bg-white">
                         <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                             <div class="grid {{ $gridClass }} gap-6 sm:gap-8">
                                 @foreach($items as $item)
                                     @php
                                         $img = $item['image'] ?? '';
                                         $imgUrl = str_starts_with($img, 'http') || str_starts_with($img, '/') || str_starts_with($img, 'assets/') 
                                             ? asset($img) 
                                             : asset('storage/' . $img);
                                         $linkUrl = $item['banner_link'] ?? $item['link'] ?? '';
                                         $itemSubtitle = $item['banner_subtitle'] ?? $item['subtitle'] ?? '';
                                         $itemTitle = $item['banner_title'] ?? $item['title'] ?? '';
                                         $itemDescription = $item['banner_description'] ?? $item['description'] ?? '';
                                         $itemButtonText = $item['banner_button_text'] ?? $item['button_text'] ?? '';
                                     @endphp

                                     @if(!empty($linkUrl))
                                         <a href="{{ $linkUrl }}" class="group block w-full relative aspect-[1.6/1] sm:aspect-[2/1] md:aspect-[2.2/1] rounded-3xl overflow-hidden border-2 border-transparent bg-slate-50 hover:border-rose-600 transition-all duration-300">
                                     @else
                                         <div class="w-full relative aspect-[1.6/1] sm:aspect-[2/1] md:aspect-[2.2/1] rounded-3xl overflow-hidden border-2 border-transparent bg-slate-50 hover:border-rose-600 transition-all duration-300">
                                     @endif
                                             <!-- Background Image -->
                                             <img src="{{ $imgUrl }}" alt="Banner Image" class="w-full h-full object-cover absolute inset-0 z-0">
                                             
                                             <!-- Content Overlay -->
                                             @if(!empty($itemTitle) || !empty($itemSubtitle) || !empty($itemDescription) || !empty($itemButtonText))
                                                  <div class="absolute inset-0 z-10 flex flex-col justify-center p-5 sm:p-8 md:p-12 max-w-[85%] sm:max-w-[75%] md:max-w-[65%] pointer-events-none">
                                                      @if(!empty($itemSubtitle))
                                                          <span class="text-rose-600 font-bold text-[10px] sm:text-xs md:text-sm uppercase tracking-widest mb-1 sm:mb-1.5 font-sans select-none">
                                                              {{ $itemSubtitle }}
                                                          </span>
                                                      @endif
                                                      
                                                      @if(!empty($itemTitle))
                                                          <h3 class="text-slate-900 text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-sans tracking-tight leading-tight select-none">
                                                              @php
                                                                  $titleParts = explode(' ', $itemTitle, 2);
                                                              @endphp
                                                              @if(count($titleParts) > 1)
                                                                  <span class="font-light block">{{ $titleParts[0] }}</span>
                                                                  <span class="font-extrabold block">{{ $titleParts[1] }}</span>
                                                              @else
                                                                  <span class="font-extrabold block">{{ $itemTitle }}</span>
                                                              @endif
                                                          </h3>
                                                      @endif
                                                      
                                                      @if(!empty($itemDescription))
                                                          <p class="text-slate-500 text-[10px] sm:text-xs md:text-sm lg:text-base mt-2 sm:mt-3 font-normal leading-relaxed font-sans select-none">
                                                              {{ $itemDescription }}
                                                          </p>
                                                      @endif
                                                      
                                                      @if(!empty($itemButtonText))
                                                          <div class="mt-4 sm:mt-5">
                                                              <span class="inline-flex items-center justify-center px-4 py-2 sm:px-5 sm:py-2.5 bg-rose-700 text-white text-[10px] sm:text-xs font-bold rounded-lg uppercase tracking-wider transition duration-300 group-hover:bg-rose-800 shadow-sm select-none">
                                                                  {{ $itemButtonText }}
                                                              </span>
                                                          </div>
                                                      @endif
                                                  </div>
                                              @endif
                                     @if(!empty($linkUrl))
                                         </a>
                                     @else
                                         </div>
                                     @endif
                                 @endforeach
                             </div>
                         </div>
                     </div>
                 @endif
             @endif

             @if($block->type === 'handpicked_products')
                 <!-- Handpicked Products (Sizin İçin Seçtiklerimiz) with Infinite Scroll -->
                 <div class="py-16 bg-white border-t border-slate-100">
                     <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                          <div class="text-center max-w-3xl mx-auto mb-12">
                              <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif">
                                  {{ $content['handpicked_title'] ?? $content['title'] ?? 'Sizin İçin Seçtiklerimiz' }}
                              </h2>
                              <p class="mt-3 text-slate-500">
                                  Özenle seçilmiş, en taze çiçek aranjmanlarımız arasından dilediğinizi seçin.
                              </p>
                          </div>

                         <!-- 4-column Product Grid -->
                         <div id="handpicked-products-grid" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-8">
                             @include('frontend.partials.product_cards', ['products' => $handpickedProducts])
                         </div>

                         <!-- Infinite Scroll Loader -->
                         <div id="infinite-scroll-loader" class="py-12 flex justify-center items-center">
                             <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-rose-600"></div>
                         </div>
                     </div>
                 </div>

                 <script>
                     (function() {
                         document.addEventListener('DOMContentLoaded', () => {
                             const grid = document.getElementById('handpicked-products-grid');
                             const loader = document.getElementById('infinite-scroll-loader');
                             
                             if (!grid || !loader) return;
                             
                             let page = 2;
                             let hasMore = true;
                             let loading = false;
                             
                             const observer = new IntersectionObserver((entries) => {
                                 if (entries[0].isIntersecting && hasMore && !loading) {
                                     loadMoreProducts();
                                 }
                             }, {
                                 rootMargin: '200px'
                             });
                             
                             observer.observe(loader);
                             
                             async function loadMoreProducts() {
                                 loading = true;
                                 loader.style.display = 'flex';
                                 
                                 try {
                                     const response = await fetch(`/handpicked-products?page=${page}`, {
                                         headers: {
                                             'X-Requested-With': 'XMLHttpRequest'
                                         }
                                     });
                                     
                                     if (!response.ok) throw new Error('Ağ hatası oluştu.');
                                     
                                     const html = await response.text();
                                     
                                     if (html.trim() === '') {
                                         hasMore = false;
                                         loader.style.display = 'none';
                                         observer.unobserve(loader);
                                     } else {
                                         grid.insertAdjacentHTML('beforeend', html);
                                         page++;
                                         loading = false;
                                         
                                         // Dynamic event rebinding for favorites
                                         if (window.initializeFavorites) {
                                             window.initializeFavorites();
                                         }
                                     }
                                 } catch (error) {
                                     console.error('Ürünler yüklenirken hata oluştu:', error);
                                     loading = false;
                                     loader.style.display = 'none';
                                 }
                             }
                         });
                     })();
                 </script>
             @endif

            @if($block->type === 'category_grid')
                <!-- Category Grid -->
                <div id="categories" class="py-24 bg-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center max-w-3xl mx-auto">
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif">
                                {{ $content['cat_grid_title'] ?? $content['title'] ?? 'Kategorilere Göre Keşfedin' }}
                            </h2>
                            <p class="mt-4 text-lg text-slate-500">
                                {{ $content['cat_grid_subtitle'] ?? $content['subtitle'] ?? 'Taze çiçekler arasından aradığınız konsepti bulun.' }}
                            </p>
                        </div>

                        <div class="mt-16 grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                            @foreach($categories as $category)
                                <a href="{{ route('category', $category->slug) }}" class="group block relative overflow-hidden rounded-2xl aspect-[4/5] bg-rose-50 shadow-sm border border-rose-100 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                                    <!-- Inner mockup card color gradient -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent z-10"></div>
                                    
                                    <div class="absolute inset-0 bg-gradient-to-br from-rose-200 to-rose-300 flex items-center justify-center group-hover:scale-105 transition duration-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-rose-600/30">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
                                        </svg>
                                    </div>

                                    <div class="absolute bottom-6 left-6 right-6 z-20">
                                        <h3 class="text-white font-serif text-lg sm:text-xl font-bold tracking-wide group-hover:text-rose-200 transition">
                                            {{ $category->name }}
                                        </h3>
                                        <span class="mt-2 inline-flex items-center text-xs font-semibold text-rose-300 group-hover:underline">
                                            Keşfet
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 ml-1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($block->type === 'product_carousel')
                <!-- Product Grid/Carousel -->
                <div class="py-24 bg-rose-50/50 border-y border-rose-100/50">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex items-end justify-between mb-12">
                            <div>
                                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif">
                                    {{ $content['carousel_title'] ?? $content['title'] ?? 'Çok Satan Ürünler' }}
                                </h2>
                                <p class="mt-3 text-slate-500">
                                    {{ $content['carousel_subtitle'] ?? $content['subtitle'] ?? 'Müşterilerimizin en beğendiği taze aranjmanlar' }}
                                </p>
                            </div>
                            <a href="{{ route('category', 'guller') }}" class="hidden sm:inline-flex items-center text-sm font-bold text-rose-600 hover:text-rose-700 transition">
                                Tümünü Gör
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 ml-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-8">
                            @include('frontend.partials.product_cards', ['products' => $featuredProducts])
                        </div>
                    </div>
                </div>
            @endif

            @if($block->type === 'trust_badges')
                <!-- Trust Badges -->
                <div class="py-20 bg-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                            @foreach($content['trust_items'] ?? $content['items'] ?? [] as $badge)
                                <div class="flex items-start gap-5">
                                    <div class="p-4 bg-rose-50 text-rose-600 rounded-2xl shrink-0">
                                        @if(($badge['icon'] ?? '') === 'truck')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125V11.25M3 14.25h15m0 0V8.25m0 0h-.882c-.832 0-1.63-.329-2.225-.916L12.75 5.03a2.25 2.25 0 0 0-1.59-.657H5.25a2.25 2.25 0 0 0-2.25 2.25v7.425" />
                                            </svg>
                                        @elseif(($badge['icon'] ?? '') === 'camera')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-800">{{ $badge['badge_title'] ?? $badge['title'] ?? '' }}</h3>
                                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ $badge['badge_description'] ?? $badge['description'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($block->type === 'seo_text')
                <!-- SEO Text & Features Block -->
                @php
                    $seoContent = $content['seo_content'] ?? $content['content'] ?? '';
                    $rightTitle = $content['right_title'] ?? 'Diyarbakır Çiçek Siparişi';
                    $rightDesc = $content['right_description'] ?? '';
                    $features = $content['features'] ?? [];
                @endphp
                <style>
                    .acelya-seo-section {
                        padding-top: 3rem;
                        padding-bottom: 3rem;
                        background-color: transparent;
                    }
                    .acelya-seo-card {
                        background-color: #ffffff;
                        border: 1px solid #f1f5f9;
                        border-radius: 2rem;
                        padding: 2.5rem;
                        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
                    }
                    @media (min-width: 1024px) {
                        .acelya-seo-card {
                            padding: 3.5rem;
                        }
                    }
                    .acelya-seo-left-col {
                        border: none;
                    }
                    @media (min-width: 1024px) {
                        .acelya-seo-left-col {
                            border-right: 1px solid #e2e8f0;
                            padding-right: 3rem;
                        }
                    }
                    .acelya-seo-rich {
                        font-size: 0.875rem;
                        color: #475569;
                        line-height: 1.75;
                    }
                    .acelya-seo-rich h2 {
                        color: #1e293b;
                        font-family: 'Poppins', sans-serif;
                        font-weight: 700;
                        font-size: 1.75rem;
                        line-height: 1.25;
                        margin-bottom: 1.25rem;
                        margin-top: 0px;
                    }
                    .acelya-seo-rich h3 {
                        color: #1e293b;
                        font-family: 'Poppins', sans-serif;
                        font-weight: 700;
                        font-size: 1.125rem;
                        margin-top: 1.5rem;
                        margin-bottom: 0.5rem;
                    }
                    .acelya-seo-rich p {
                        margin-bottom: 1.25rem;
                        text-align: justify;
                    }
                    .acelya-seo-rich a {
                        color: var(--color-rose-600, #ea580c);
                        font-weight: 700;
                        text-decoration: none;
                    }
                    .acelya-seo-rich a:hover {
                        text-decoration: underline;
                    }
                    .acelya-seo-right-title {
                        color: #1e293b;
                        font-family: 'Poppins', sans-serif;
                        font-weight: 700;
                        font-size: 1.75rem;
                        line-height: 1.25;
                        margin-bottom: 0.75rem;
                    }
                    .acelya-seo-right-desc {
                        color: #64748b;
                        font-size: 0.875rem;
                        line-height: 1.625rem;
                        margin-bottom: 2rem;
                    }
                    .acelya-seo-features-list {
                        display: flex;
                        flex-direction: column;
                        gap: 1.5rem;
                    }
                    .acelya-seo-feature-item {
                        display: flex;
                        align-items: start;
                        gap: 1.25rem;
                    }
                    .acelya-seo-feature-icon-box {
                        width: 3rem;
                        height: 3rem;
                        border-radius: 0.75rem;
                        background-color: var(--color-rose-600, #ea580c);
                        color: #ffffff;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                        box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.15), 0 2px 4px -2px rgba(234, 88, 12, 0.15);
                    }
                    .acelya-seo-feature-text {
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        min-height: 3rem;
                    }
                    .acelya-seo-feature-title {
                        font-weight: 700;
                        color: #1e293b;
                        font-size: 0.9375rem;
                        line-height: 1.25;
                    }
                    .acelya-seo-feature-desc {
                        font-size: 0.8125rem;
                        color: #94a3b8;
                        font-weight: 600;
                        line-height: 1.25;
                        margin-top: 0.125rem;
                    }
                </style>
                <div class="acelya-seo-section">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="acelya-seo-card">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-stretch">
                                <!-- Left Column: SEO Content -->
                                <div class="lg:col-span-7 acelya-seo-left-col">
                                    <div class="acelya-seo-rich">
                                        {!! $seoContent !!}
                                    </div>
                                </div>
                                
                                <!-- Right Column: Features -->
                                <div class="lg:col-span-5 lg:pl-4 flex flex-col justify-between">
                                    <div>
                                        <h2 class="acelya-seo-right-title">
                                            {{ $rightTitle }}
                                        </h2>
                                        @if(!empty($rightDesc))
                                            <p class="acelya-seo-right-desc">
                                                {{ $rightDesc }}
                                            </p>
                                        @endif
                                        
                                        <div class="acelya-seo-features-list">
                                            @foreach($features as $feature)
                                                <div class="acelya-seo-feature-item">
                                                    <div class="acelya-seo-feature-icon-box">
                                                        @if(($feature['feature_icon'] ?? $feature['icon'] ?? '') === 'truck')
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125V11.25M3 14.25h15m0 0V8.25m0 0h-.882c-.832 0-1.63-.329-2.225-.916L12.75 5.03a2.25 2.25 0 0 0-1.59-.657H5.25a2.25 2.25 0 0 0-2.25 2.25v7.425" />
                                                            </svg>
                                                        @elseif(($feature['feature_icon'] ?? $feature['icon'] ?? '') === 'smile')
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                                                            </svg>
                                                        @elseif(($feature['feature_icon'] ?? $feature['icon'] ?? '') === 'credit-card')
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-5.625 3h18.75a1.875 1.875 0 0 0 1.875-1.875V5.25A1.875 1.875 0 0 0 22.5 3.375H3.75A1.875 1.875 0 0 0 1.875 5.25v13.5A1.875 1.875 0 0 0 3.75 20.625Z" />
                                                            </svg>
                                                        @elseif(($feature['feature_icon'] ?? $feature['icon'] ?? '') === 'headphones')
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H18V10.75a2.25 2.25 0 0 1 2.25-2.25H21Zm-18 0V15A2.25 2.25 0 0 0 5.25 17.25H6V10.75A2.25 2.25 0 0 0 3.75 8.5H3Z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4.97 0-9 4.03-9 9v2.25h18V12c0-4.97-4.03-9-9-9Z" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <div class="acelya-seo-feature-text">
                                                        <span class="acelya-seo-feature-title">{{ $feature['feature_title'] ?? $feature['title'] ?? '' }}</span>
                                                        <span class="acelya-seo-feature-desc">{{ $feature['feature_description'] ?? $feature['description'] ?? '' }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($block->type === 'blog_posts')
                <!-- Blog Section on Home -->
                @php
                    $blogTitle = $content['blog_title'] ?? $content['title'] ?? 'Çiçek Bakımı ve Öneriler';
                    $blogSubtitle = $content['blog_subtitle'] ?? $content['subtitle'] ?? 'Çiçeklerinizin her zaman canlı kalması için derlediğimiz pratik rehberler.';
                    $blogLimit = intval($content['blog_limit'] ?? $content['limit'] ?? 3);
                    $posts = $latestBlogPosts->take($blogLimit);
                @endphp
                @if($posts->count() > 0)
                    <div class="py-24 bg-slate-50 border-t border-slate-100">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <div class="text-center max-w-3xl mx-auto mb-16">
                                <h2 class="text-3xl font-extrabold text-slate-900 font-serif">{{ $blogTitle }}</h2>
                                <p class="mt-4 text-slate-500">{{ $blogSubtitle }}</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                @foreach($posts as $post)
                                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-lg transition">
                                        <div class="relative aspect-video bg-rose-50 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-rose-200">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                                            </svg>
                                        </div>
                                        <div class="p-6 flex-grow flex flex-col justify-between">
                                            <div>
                                                <h3 class="font-serif text-lg font-bold text-slate-800 hover:text-rose-600 transition">
                                                    <a href="{{ route('blog.detail', $post->slug) }}">{{ $post->title }}</a>
                                                </h3>
                                                <p class="mt-3 text-xs text-slate-400 leading-relaxed">{{ $post->summary }}</p>
                                            </div>
                                            <div class="mt-6 pt-4 border-t border-slate-50 flex justify-between items-center text-xs text-slate-400">
                                                <span>{{ $post->created_at->format('d M Y') }}</span>
                                                <a href="{{ route('blog.detail', $post->slug) }}" class="font-bold text-rose-600 hover:text-rose-700 transition">Devamını Oku</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

        @endforeach
@endif
</div>

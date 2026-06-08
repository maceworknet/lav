@extends('frontend.layouts.app')

@section('content')
    <div class="py-16 bg-rose-50/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-rose-600 font-semibold text-xs tracking-widest uppercase">LAV REHBER</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif mt-2">Çiçek Bakımı ve Öneriler</h1>
                <p class="mt-4 text-slate-500 text-sm sm:text-base">Çiçeklerinizin her zaman canlı, taze ve sağlıklı kalması için hazırladığımız özel içerikler.</p>
            </div>

            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-xl transition duration-300">
                            
                            <!-- Header Image Placeholder -->
                            <div class="relative aspect-video bg-rose-50 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 text-rose-200">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                                </svg>
                            </div>

                            <!-- Body details -->
                            <div class="p-6 flex-grow flex flex-col justify-between">
                                <div>
                                    <h3 class="font-serif text-lg font-bold text-slate-800 hover:text-rose-600 transition">
                                        <a href="{{ route('blog.detail', $post->slug) }}">{{ $post->title }}</a>
                                    </h3>
                                    <p class="mt-3 text-xs text-slate-400 leading-relaxed line-clamp-3">{{ $post->summary }}</p>
                                </div>
                                <div class="mt-6 pt-4 border-t border-slate-50 flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                    <span>{{ $post->created_at->timezone('Europe/Istanbul')->format('d M Y') }}</span>
                                    <a href="{{ route('blog.detail', $post->slug) }}" class="font-black text-rose-600 hover:text-rose-700 transition">Devamını Oku</a>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-16 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="py-24 text-center max-w-md mx-auto bg-white rounded-3xl border border-slate-100 shadow-sm p-12">
                    <p class="text-slate-500">Henüz yayınlanmış bir rehber yazısı bulunmamaktadır.</p>
                </div>
            @endif

        </div>
    </div>
@endsection

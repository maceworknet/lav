@extends('frontend.layouts.app')

@section('content')
    <div class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4">
            
            <!-- Breadcrumbs -->
            <nav class="flex text-xs text-slate-400 font-semibold mb-6 uppercase tracking-wider gap-2">
                <a href="/" class="hover:text-rose-600 transition">Ana Sayfa</a>
                <span>/</span>
                <a href="/blog" class="hover:text-rose-600 transition">Blog</a>
                <span>/</span>
                <span class="text-slate-600 truncate max-w-[200px]">{{ $post->title }}</span>
            </nav>

            <div class="space-y-4 mb-10">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 font-serif leading-tight">
                    {{ $post->title }}
                </h1>
                <div class="flex items-center gap-3 text-xs text-slate-400 font-bold uppercase tracking-wider">
                    <span>Yayınlanma: {{ $post->created_at->timezone('Europe/Istanbul')->format('d.m.Y') }}</span>
                    @if($post->category)
                        <span>•</span>
                        <span class="text-rose-600">{{ $post->category->name }}</span>
                    @endif
                </div>
            </div>

            <!-- Content Area -->
            <div class="prose prose-rose max-w-none text-slate-600 leading-relaxed space-y-6 text-sm sm:text-base">
                @if($post->summary)
                    <p class="text-lg text-slate-800 font-medium italic border-l-4 border-rose-500 pl-4 mb-8">
                        {{ $post->summary }}
                    </p>
                @endif

                {!! nl2br(e($post->content)) !!}
            </div>

            <!-- Back to Blog button -->
            <div class="mt-16 border-t border-slate-100 pt-8 text-center">
                <a href="/blog" class="inline-flex items-center bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-sm px-8 py-3 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Tüm Blog Yazılarına Dön
                </a>
            </div>

        </div>
    </div>
@endsection

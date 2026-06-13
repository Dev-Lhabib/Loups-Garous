@extends('errors::minimal')

@section('title', __('errors.404.title'))

@section('icon')
    <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-accent-purple/30 to-accent-purple/5 border-2 border-accent-purple/30 flex items-center justify-center">
        <span class="text-3xl">🌙</span>
    </div>
@endsection

@section('message', __('errors.404.message'))

@section('action')
    <a href="{{ route('home') }}"
       class="inline-block py-3 px-6 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 hover:scale-[1.02] active:scale-95 shadow-lg text-sm">
        {{ __('errors.404.action') }}
    </a>
@endsection

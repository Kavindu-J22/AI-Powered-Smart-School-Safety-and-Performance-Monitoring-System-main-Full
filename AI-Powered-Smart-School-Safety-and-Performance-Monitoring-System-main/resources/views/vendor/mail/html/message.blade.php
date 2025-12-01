@extends('admin.layouts.app')
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            <span style="color: white;">{{ config('app.name') }}</span>
        </x-mail::header>
    </x-slot:header>
    {{-- Body --}}
    <div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; padding: 20px;">
        <div class="image-outer" style="text-align: center">
            <img src="{{ url(asset('assets/img/favicon.ico')) }}" class="center" alt="Logo" style="width: 100px;">
        </div>
        <br>
        {{ $slot }}
    </div>

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>

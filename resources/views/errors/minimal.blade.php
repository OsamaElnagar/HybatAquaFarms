<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ config('app.name', 'Hybat Aqua Farms') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">
    <div class="min-h-screen flex flex-col justify-center items-center px-6 py-12">
        <div class="max-w-md w-full text-center">
            <div class="text-7xl font-light text-slate-400 dark:text-slate-500 mb-6 tracking-tighter">
                @yield('code')
            </div>

            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white mb-3">
                @yield('message')
            </h1>

            @hasSection('description')
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                    @yield('description')
                </p>
            @endif

            <div class="flex justify-center gap-4 mt-8">
                <button type="button"
                    onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='{{ url('/') }}'; }"
                    class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-900 focus:z-10 focus:ring-2 focus:ring-slate-300 dark:focus:ring-slate-500 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-600 dark:hover:text-white dark:hover:bg-slate-700 transition-colors">
                    {{ __('رجوع للخلف') }}
                </button>
                <a href="{{ url('/admin') }}"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-colors">
                    {{ __('الرئيسية') }}
                </a>
            </div>
        </div>
    </div>
</body>

</html>
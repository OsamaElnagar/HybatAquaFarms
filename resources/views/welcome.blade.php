<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Hybat Aqua Farms</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gradient-to-br from-blue-50 to-teal-100 dark:from-slate-900 dark:to-slate-800 min-h-screen flex items-center justify-center">
        <div class="text-center max-w-lg px-6">
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-3xl shadow-2xl p-10 border border-white/20">
                <h1 class="text-4xl md:text-5xl font-bold text-slate-800 dark:text-white mb-4 font-cairo">
                    مرحباًبك
                </h1>

                <a
                    href="{{ route('filament.admin.home') }}"
                    class="inline-flex items-center gap-2 px-8 py-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl font-cairo"
                >
                    <span>دخول إلى النظام</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
            <p class="mt-8 text-sm text-slate-500 dark:text-slate-400">
                &copy; {{ date('Y') }} Hybat Aqua Farms
            </p>
        </div>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Dark mode: apply before CSS to prevent flash -->
        <script>
            (function(){
                var t = localStorage.getItem('theme');
                if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/src/app.js'])
        @routes
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-gray-100">
        @inertia
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        @php
            $pageTitle = $meta['title'] ?? config('app.name', 'Laravel');
            $pageDescription = $meta['description'] ?? 'Top up game dan produk digital dengan cepat, aman, dan harga terbaik.';
            $pageUrl = $meta['url'] ?? url()->current();
            $pageImage = $meta['image'] ?? asset('images/og-image.jpg');
            $applicationName = $meta['application_name'] ?? config('app.name', 'Laravel');
        @endphp

        <title inertia>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}" />

        @if(isset($meta['description']))
            <meta name="description" content="{{ $meta['description'] }}" />
        @endif
        @if(isset($meta['keywords']))
            <meta name="keywords" content="{{ $meta['keywords'] }}" />
        @endif
        @if(isset($meta['author']))
            <meta name="author" content="{{ $meta['author'] }}" />
        @endif
        <meta name="application-name" content="{{ $applicationName }}" />
        <meta name="theme-color" content="#0f172a" />
        @if(config('services.meta_domain_verification'))
            <meta name="facebook-domain-verification" content="{{ config('services.meta_domain_verification') }}" />
        @endif
        <link rel="canonical" href="{{ $pageUrl }}" />

        <!-- Open Graph -->
        <meta property="og:type" content="{{ $meta['type'] ?? 'website' }}" />
        <meta property="og:site_name" content="{{ $applicationName }}" />
        <meta property="og:locale" content="id_ID" />
        <meta property="og:title" content="{{ $pageTitle }}" />
        <meta property="og:description" content="{{ $pageDescription }}" />
        <meta property="og:url" content="{{ $pageUrl }}" />
        <meta property="og:image" content="{{ $pageImage }}" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:image:alt" content="{{ $pageTitle }}" />
        <link rel="shortcut icon" href="{{ getSetting('favicon') ?: '/favicon.svg' }}" />
        <link rel="icon" type="image/svg+xml" href="{{ getSetting('favicon') ?: '/favicon.svg' }}" />

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $pageTitle }}" />
        <meta name="twitter:description" content="{{ $pageDescription }}" />
        <meta name="twitter:image" content="{{ $pageImage }}" />
        <meta name="twitter:image:alt" content="{{ $pageTitle }}" />
        <meta name="twitter:site" content="{{ $applicationName }}" />

        <meta name="robots" content="{{ $meta['robots'] ?? 'index, follow' }}" />

        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $applicationName,
                'url' => url('/'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => url('/?search={search_term_string}'),
                    'query-input' => 'required name=search_term_string',
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>

        @foreach($meta['structured_data'] ?? [] as $structuredData)
            <script type="application/ld+json">
                {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
            </script>
        @endforeach

        @if(config('services.meta_pixel_id'))
            <script>
                !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,
                'script','https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '{{ config('services.meta_pixel_id') }}');
                fbq('track', 'PageView');
            </script>
            <noscript>
                <img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ config('services.meta_pixel_id') }}&ev=PageView&noscript=1" />
            </noscript>
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @if(config('services.meta_pixel_id'))
            <link rel="preconnect" href="https://connect.facebook.net" crossorigin>
        @endif

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

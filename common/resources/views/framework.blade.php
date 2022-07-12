<!doctype html>
<html>
    <head>
        <title class="dst">{{ $settings->get('branding.site_name') }}</title>

        <base href="{{ $htmlBaseUri }}">

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500&display=swap" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="{{$settings->get('branding.favicon')}}">

        @if($themes = $bootstrapData->get('themes'))
            <script>
                (function() {
                    window.beThemes = JSON.parse('{!! json_encode($themes) !!}');

                    @if ($settings->get('themes.user_change'))
                        var storedBeTheme = localStorage && localStorage.getItem('{{ str_slug($settings->get('branding.site_name')) . '.theme'}}');
                        window.beSelectedTheme = storedBeTheme ? JSON.parse(storedBeTheme) : '{{ $settings->get('themes.default_mode', 'light') }}';
                    @else
                        window.beSelectedTheme = '{{ $settings->get('themes.default_mode', 'light') }}';
                    @endif

                    var style = document.createElement('style');
                    style.innerHTML = ':root ' + JSON.stringify(beThemes[beSelectedTheme].colors).replace(/"/g, '').replace(/,--/g, ';--');
                    document.head.appendChild(style);
                })();
            </script>
        @endif

        @yield('progressive-app-tags')

        @yield('angular-styles')

        @if (file_exists($customCssPath))
            @if ($content = file_get_contents($customCssPath))
                <style>{!! $content !!}</style>
            @endif
        @endif

        @yield('head-end')
	</head>

    <body>
        <app-root>
            @yield('before-loaded-content')
        </app-root>

        <script>
            if (window.beSelectedTheme === 'dark') {
                document.documentElement.classList.add('be-dark-mode');
            } else {
                document.documentElement.classList.add('be-light-mode');
            }
            window.bootstrapData = "{!! $bootstrapData->getEncoded() !!}";
        </script>

        @yield('angular-scripts')

        @if (file_exists($customHtmlPath))
            @if ($content = file_get_contents($customHtmlPath))
                {!! $content !!}
            @endif
        @endif

        @if ($code = $settings->get('analytics.tracking_code'))
            <script>
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

                ga('create', '{{ $settings->get('analytics.tracking_code') }}', 'auto');
                ga('send', 'pageview');
            </script>
        @endif

        <noscript>You need to have javascript enabled in order to use <strong>{{config('app.name')}}</strong>.</noscript>

        @yield('body-end')
	</body>
</html>
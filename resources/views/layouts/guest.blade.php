<!DOCTYPE html>
<html class="h-full" data-theme="true" data-theme-mode="light" dir="ltr" lang="fr">
<head>
    <title>Coding Tool Box</title>
    <meta charset="utf-8"/>
    <meta content="follow, index" name="robots"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <meta content="" name="description"/>
    <link href="{{ asset('media/icon.png') }}" rel="shortcut icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="{{ asset('metronic/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet"/>
    <link href="{{ asset('metronic/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('metronic/css/styles.css') }}" rel="stylesheet"/>
</head>
<body class="antialiased flex h-full text-base text-gray-700 [--tw-page-bg:#F6F6F9] [--tw-page-bg-dark:var(--tw-coal-200)]
                [--tw-content-bg:var(--tw-light)] [--tw-content-bg-dark:var(--tw-coal-500)]
                [--tw-content-scrollbar-color:#e8e8e8] [--tw-header-height:60px] [--tw-sidebar-width:270px]
                bg-[--tw-page-bg] dark:bg-[--tw-page-bg-dark] lg:overflow-hidden">
<!-- Theme Mode -->
<script>
    const defaultThemeMode = 'light'; // light|dark|system
    let themeMode;
    if ( document.documentElement ) {
        if ( localStorage.getItem('theme') ) {
            themeMode = localStorage.getItem('theme');
        } else if ( document.documentElement.hasAttribute('data-theme-mode') ) {
            themeMode = document.documentElement.getAttribute('data-theme-mode');
        } else {themeMode = defaultThemeMode;}

        if ( themeMode === 'system' ) {themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';}
        document.documentElement.classList.add(themeMode);
    }
</script>
<!-- End of Theme Mode -->

<!-- Page -->
<style>
    .page-bg {
        background-image: url({{ asset('metronic/media/images/2600x1200/bg-10.png') }} );
    }
    .dark .page-bg {
        background-image: url({{ asset('metronic/media/images/2600x1200/bg-10-dark.png') }} );
    }
</style>
<div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
    {{ $slot }}
</div>
<!-- End of Page -->

<!-- Scripts -->
<script src="{{ asset('metronic/js/core.bundle.js') }}"></script>
<script src="{{ asset('metronic/vendors/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('metronic/js/widgets/general.js') }}"></script>
<!-- End of Scripts -->
</body>
</html>

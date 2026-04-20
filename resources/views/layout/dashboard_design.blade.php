<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Punto')</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.dataTables.js"></script>

    <link rel="stylesheet" href="{{ asset('css/layout/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/topbar.css') }}">
    @stack('styles')
</head>

<body data-theme-preference="{{ $uiPreferences['theme'] ?? 'light' }}">
    <div class="app-shell">
        @include('partials.sidebar')

        <div class="main-wrapper">
            @include('partials.topbar')

            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (function () {
            const body = document.body;
            const preference = body.dataset.themePreference || 'light';

            function applyTheme() {
                let resolved = preference;

                if (preference === 'auto') {
                    resolved = window.matchMedia('(prefers-color-scheme: dark').matches ? 'dark' : 'light';  
                }

                body.setAttribute('data-theme', resolved);
            }

            applyTheme();

            const media = window.matchMedia('(prefers-color-scheme): dark');
            if (media && preference === 'auto') {
                media.addEventListener('change', applyTheme);
            }
        })();

        document.addEventListener('DOMContentLoaded', function(){
            const logoInput = document.getElementById('logoInput');
            const addressInput = document.getElementById('addressInput');
            const currencyInput = document.getElementById('currencyInput');
            const openingInput = document.getElementById('openingInput');
            const closingInput = document.getElementById('closingInput');
            const paymentMethods = document.querySelectorAll('.payment-method');

            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const progressFields = document.getElementById('progressFields');

            if(!progressFill || !progressText || !progressFields) {
                return;
            }

            const totalFields = 6;

            function isPaymentMethodSelected(){
                return Array.from(paymentMethods).some(input => input.checked);
            }

            function calculateCompletedFields(){
                let completed = 0;

                if(logoInput && logoInput.files.length > 0) completed++;
                if(addressInput && addressInput.value.trim() !== '') completed++;
                if(currencyInput && currencyInput.value.trim() !== '') completed++;
                if(openingInput && openingInput.value.trim() !== '') completed++;
                if(closingInput && closingInput.value.trim() !== '') completed++;
                if(isPaymentMethodSelected()) completed++;

                return completed;
            }

            function updateProgress(){
                const completed = calculateCompletedFields();
                const percentage = Math.round((completed / totalFields) * 100);
                const remaining = totalFields - completed;

                progressFill.style.width = percentage + '%';
                progressText.textContent = percentage + '% completado';
                progressFields.textContent = remaining + ' campos sugeridos';
            }

            if (logoInput) logoInput.addEventListener('change', updateProgress);
            if (addressInput) addressInput.addEventListener('input', updateProgress);
            if (currencyInput) currencyInput.addEventListener('change', updateProgress);
            if (openingInput) openingInput.addEventListener('change', updateProgress);
            if (closingInput) closingInput.addEventListener('change', updateProgress);

            paymentMethods.forEach(method => {
                method.addEventListener('change', updateProgress);
            });

            updateProgress();
        });
    </script>
</body>
</html>
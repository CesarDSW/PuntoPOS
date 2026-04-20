<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Punto')</title>
    
    <link rel="stylesheet" href="{{ asset('css/layout/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/topbar.css') }}">

    @stack('styles')
</head>

@php
    use App\Support\CompanyPreference;

    $companyId = auth()->check() ? (int) auth()->user()->company_idfk : null;
@endphp

<script>
    window.appPrefs = {
        currency: @json(CompanyPreference::currency($companyId)),
        decimals: @json(CompanyPreference::decimals($companyId)),
        timezone: @json(CompanyPreference::timezone($companyId)),
        dateFormat: @json(CompanyPreference::dateFormat($companyId)),
        timeFormat: @json(CompanyPreference::timeFormat($companyId)),
    };

    window.appFormat = (() => {
        function parseDate(value) {
            if (!value) return null;
            if (value instanceof Date)  return value;

            const raw = String(value).trim();

            let parsed = new Date(raw.replace(' ', 'T'));
            if (!isNaN(parsed.getTime())) return parsed;

            const match = raw.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/);
            if(!match) return null;

            const [, y, m, d, hh = '00', mm = '00', ss = '00'] = match;
            parsed = new Date(
                Number(y),
                Number(m) - 1,
                Number(d),
                Number(hh),
                Number(mm),
                Number(ss)
            );

            return isNan(parsed.getTime()) ? null : parsed;
        }

        function parts(value) {
            const date =paseDate(value);
            if(!date) return null;

            const dateParts = new Intl.DateTimeFormat('en-CA', {
                timeZone: window.appPrefs.timezone,
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
            }).formatToParts(date);

            const time24Parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: window.appPrefs.timezone,
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }).formatToParts(date);

            const time12Parts = new Intl.DateTimeFormat('en-US', {
                timeZone: window.appPrefs.timezone,
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            }).formatToParts(date);

            const toMap = (arr) => Object.fromEntries(
                arr.filter(p => p.type !== 'literal').map(p => [p.type, p.value])
            );

            const d = toMap(dateParts);
            const t24 = toMap(time24Parts);
            const t12 = toMap(time12Parts);

            return {
                year: d.year,
                month: d.month,
                day: d.day,
                hour24: t24.hour,
                minute: t24.minute,
                hour12: t12.hour,
                dayPeriod: (t12.dayPeriod || '').toUpperCase(),
            };
        }

        function money(value) {
            const num = Number(value || 0);
            const decimals = Number(window.appPrefs.decimals ?? 2);
            const symbol = window.appPrefs.currency === 'USD' ? 'US$' : 'MX$';

            const number = new Intl.NumberFormat('es-MX', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(num);

            return `${symbol}${number}`; 
        }

        function date(value) {
            const p = parts(value);
            if (!p) return  '-';

            switch (window.appPrefs.dateFormat) {
                case 'm/d/Y':
                    return `${p.month}/${p.day}/${p.year}`;
                case 'Y-m-d':
                    return `${p.year}-${p.month}-${p.day}`;
                default:
                    return `${p.day}/${p.month}/${p.year}`;
            }
        }

        function time(value) {
            const p = parts(value);
            if (!p) return '-';

            if (window.appPrefs.timeFormat === 'h:i A'){
                return `${p.hour12}:${p.minute} ${p.dayPeriod}`;
            }
            return `${p.hour24}:${p.minute}`;
        }

        function dateTime(value) {
            return `${date(value)} ${time(value)}`; 
        }

        function currentDateTime() {
            return dateTime(new Date());
        }

        return { money, date, time, dateTime, currentDateTime };
    })();
</script>

<body>
    <div class="app-shell">
        @include('partials.sidebar')

        <div class="main-wrapper">
            @include('partials.topbar')

            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
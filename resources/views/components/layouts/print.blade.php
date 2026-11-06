@props([
    'title' => 'Cetak',
    'pageSize' => '100mm 60mm',
    'pageMargin' => '4mm',
    'containerClass' => 'max-w-2xl',
])

<!DOCTYPE html>
<html lang="id" class="bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — TosyoCSSD</title>
    @vite(['resources/css/app.css'])
    <style>
        /* Label thermal (default) atau A4 untuk laporan — lihat prop pageSize. */
        @page {
            size: {{ $pageSize }};
            margin: {{ $pageMargin }};
        }

        @media print {
            .print-label {
                page-break-inside: avoid;
                page-break-after: always;
            }

            .print-label:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 font-sans antialiased">

    <div class="mx-auto {{ $containerClass }} px-4 py-6 print:max-w-none print:px-0 print:py-0">
        <div class="print:hidden mb-4 flex items-center justify-between">
            <h1 class="text-sm font-bold text-gray-500">{{ $title }}</h1>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-400 px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak
            </button>
        </div>

        {{ $slot }}
    </div>

</body>

</html>

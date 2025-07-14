<!doctype html>
<html dir="rtl" lang="fa-IR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'سامانه پرسش و پاسخ' }}</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/style/main.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>

    @vite('resources/js/app.js', 'resources/css/app.css')

    <!-- Custom Styles -->
    <style>
        /* استایل سفارشی برای اینپوت Select2 */
        .select2-container--default .select2-selection--multiple {
            background-color: #FFFFFF;
            border: 1px solid #DFE2EB;
            border-radius: 12px;
            padding: 10px 5px 10px 5px;
            min-height: 38px;
            z-index: 50000;
            font-size: 14px;
        }

        .dark .select2-container--default .select2-selection--multiple {
            background-color: #0C0D0F;
            border-color: #33353B;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            color: white;
            border: 1px solid #0056b3;
            border-radius: 4px;
            padding: 2px 0px 2px 16px;
            margin: 2px;
            z-index: 50000;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #ffffff;
            margin-left: 5px;
            cursor: pointer;
            z-index: 50000;
            font-size: 14px;
        }

        /* استایل سفارشی برای برچسب‌ها */
        .tags-container {
            margin-top: 10px;
            z-index: 50000;
        }

        .tag {
            display: inline-block;
            background-color: #F4F4F480;
            color: #5A5F66;
            font-size: 14px;
            padding: 4px 8px 4px 8px;
            margin: 5px;
            border-radius: 8px;
            z-index: 50000;
        }

        .dark .tag {
            background-color: #3434344D;
        }

        .tag .remove-tag {
            margin-right: 10px;
            cursor: pointer;
            color: #5A5F66;
            z-index: 50000;
        }

        /* Scrollbar Styles */
        .overflow-y-auto::-webkit-scrollbar-track {
            border-radius: 10px;
            background-color: transparent;
            overflow-x: auto;
        }

        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
            height: 6px;
            background-color: transparent;
            overflow-x: auto;
            border-radius: 10px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            border-radius: 10px;
            background-color: #DFE2EB;
            overflow-x: auto;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb {
            background-color: #191B21;
        }

        .searchScroll::-webkit-scrollbar-track {
            border-radius: 10px;
            background-color: #F4F4F4;
            overflow-x: auto;
            padding: 5px;
        }

        .dark .searchScroll::-webkit-scrollbar-track {
            border-radius: 10px;
            background-color: black;
            overflow-x: auto;
            width: 20px;
            padding: 5px;
        }

        .searchScroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
            background-color: #F4F4F4;
            overflow-x: auto;
            border-radius: 10px;
            padding: 5px;
        }

        .searchScroll::-webkit-scrollbar-thumb {
            border-radius: 10px;
            padding: 5px;
            background-color: #DFE2EB;
            overflow-x: auto;
        }

        .dark .searchScroll::-webkit-scrollbar-thumb {
            background-color: #191B21;
        }

        /* CKEditor Styles */
        .ck.ck-toolbar {
            border: none !important;
            padding-top: 20px!important;
            padding-bottom: 20px!important;
        }

        .ck.ck-content {
            height: 200px;
        }

        .dark .ck.ck-content {
            background-color: #1e1e1e;
            color: #e0e0e0;
            border-width: 0px !important;
        }

        .dark .ck.ck-toolbar {
            background-color: #333;
            border-color: #444;
        }

        .dark .ck.ck-button {
            color: #e0e0e0;
        }

        .dark .ck.ck-button:hover,
        .dark .ck.ck-button:focus {
            background-color: #444;
        }

        .cke_editable {
            border: none !important;
        }

        .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
            border-bottom: none !important;
            border-right: none !important;
            border-left: none !important;
            border-color: #DFE2EB !important;
        }

        .ck.ck-editor__editable.ck-focused:not(.ck-editor__nested-editable) {
            border-bottom: none !important;
            border-right: none !important;
            border-left: none !important;
            border-color: #DFE2EB !important;
            box-shadow: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-[#fbfdff] dark:bg-black w-full font-azarMehr font-bold">
    <x-layouts.header />
    <x-layouts.sidebar />

    <main class="w-full main-content-smallNav relative">
        {{ $slot }}
    </main>

    <x-layouts.footer />

    <!-- Scripts -->
    <script src="{{ asset('assets/script/script.js') }}"></script>
    <script src="{{ asset('assets/script/custom.js') }}"></script>
    <script src="{{ asset('assets/script/preline.js') }}"></script>

    @stack('scripts')
</body>
</html>

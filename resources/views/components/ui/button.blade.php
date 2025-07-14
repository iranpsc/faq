@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
])

@php
    $classes = match($variant) {
        'primary' => 'bg-[#2667FF] dark:bg-[#FFC700] dark:text-black text-white hover:bg-[#1a52cc] dark:hover:bg-[#e6b800]',
        'secondary' => 'bg-[#20D05C] dark:bg-[#02501D] text-white hover:bg-[#1ab84a] dark:hover:bg-[#03602a]',
        'outline' => 'border border-[#2667FF] dark:border-[#FFC700] text-[#2667FF] dark:text-[#FFC700] bg-transparent hover:bg-[#2667FF] hover:text-white dark:hover:bg-[#FFC700] dark:hover:text-black',
        'ghost' => 'bg-transparent text-[#2667FF] dark:text-[#FFC700] hover:bg-[#E9F0FF] dark:hover:bg-black',
        'danger' => 'bg-[#FF5722] text-white hover:bg-[#e64a19]',
        default => 'bg-[#2667FF] dark:bg-[#FFC700] dark:text-black text-white hover:bg-[#1a52cc] dark:hover:bg-[#e6b800]',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
        'xl' => 'px-8 py-4 text-xl',
        default => 'px-4 py-2 text-base',
    };

    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed' : '';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => "rounded-[10px] font-bold transition-colors duration-200 {$classes} {$sizeClasses} {$disabledClasses}"
    ]) }}
    @if($disabled) disabled @endif
>
    {{ $slot }}
</button>

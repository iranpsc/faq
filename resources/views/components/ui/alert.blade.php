@props([
    'type' => 'info',
    'dismissible' => false,
    'id' => null,
])

@php
    $classes = match($type) {
        'success' => 'bg-[#E9FAEF66] dark:bg-[#342A0933] border-[#20D05C] dark:border-[#FFC700] text-[#20D05C] dark:text-[#FFC700]',
        'error' => 'bg-[#FFEBEE] dark:bg-[#4A1F1F] border-[#F44336] text-[#F44336]',
        'warning' => 'bg-[#FFF3E0] dark:bg-[#4A3A1F] border-[#FF9800] text-[#FF9800]',
        'info' => 'bg-[#E3F2FD] dark:bg-[#1F2A4A] border-[#2196F3] text-[#2196F3]',
        default => 'bg-[#E3F2FD] dark:bg-[#1F2A4A] border-[#2196F3] text-[#2196F3]',
    };
@endphp

<div
    @if($id) id="{{ $id }}" @endif
    class="rounded-xl border-2 p-4 mb-4 {{ $classes }}"
    role="alert"
>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            {{ $slot }}
        </div>

        @if($dismissible)
            <button
                type="button"
                class="mr-2 text-current hover:opacity-70 focus:opacity-70"
                onclick="this.parentElement.parentElement.remove()"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        @endif
    </div>
</div>

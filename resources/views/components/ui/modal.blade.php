@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'lg',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'md:w-[50%] lg:w-[40%]',
        'md' => 'md:w-[70%] lg:w-[60%]',
        'lg' => 'md:w-[90%] lg:w-[80%] 3xl:w-[70%]',
        'xl' => 'md:w-[95%] lg:w-[90%] 3xl:w-[80%]',
        default => 'md:w-[90%] lg:w-[80%] 3xl:w-[70%]',
    };
@endphp

<div id="{{ $id }}" class="hidden w-full h-full bg-black/60 flex justify-center md:items-center fixed z-[2000] pt-10 md:pt-0 top-0 bottom-0 right-0">
    <div class="bg-[#FBFDFF] dark:bg-[#000000] rounded-xl w-full {{ $sizeClasses }} p-5 overflow-y-auto md:h-[95vh] relative">
        <!-- Close Button -->
        <span id="closeModalBtn" class="close absolute right-5 top-5 text-4xl cursor-pointer w-5 h-5 text-[#0F0F0E] dark:text-[#FCFCFC] hover:text-[#2667FF] dark:hover:text-[#FFC700]">&times;</span>

        <!-- Modal Header -->
        @if($title)
            <div class="text-[#0F0F0E] text-lg text-center space-y-2 mb-6">
                <h2 class="dark:text-[#FCFCFC] md:text-[32px] font-bold">{{ $title }}</h2>
            </div>
        @endif

        <!-- Modal Content -->
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>

<script>
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}
</script>

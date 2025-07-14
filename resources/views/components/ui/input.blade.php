@props([
    'type' => 'text',
    'label' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => '',
])

<div class="flex flex-col gap-2">
    @if($label)
        <label class="text-[#0F0F0E] text-sm dark:text-[#FCFCFC]">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="bg-[#FFFFFF] dark:bg-[#0C0D0F] dark:border-[#33353B] dark:text-[#A8ABB4] border border-[#DFE2EB] w-full flex items-center justify-center px-4 py-1 rounded-xl">
        <input
            type="{{ $type }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'placeholder:text-[#A8ABB4] w-full bg-transparent ring-0 focus:ring-0 border-0 focus:border-0 text-[#0F0F0E] dark:text-[#FCFCFC]'
            ]) }}
        />
    </div>

    @if($error)
        <span class="text-red-500 text-sm">{{ $error }}</span>
    @endif
</div>

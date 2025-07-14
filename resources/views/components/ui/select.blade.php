@props([
    'label' => '',
    'placeholder' => 'انتخاب کنید',
    'required' => false,
    'disabled' => false,
    'error' => '',
    'options' => [],
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

    <select
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge([
            'class' => 'w-full bg-[#FFFFFF] dark:bg-[#0C0D0F] dark:border-[#33353B] border-[#DFE2EB] text-[#262626] dark:text-[#84858F] rounded-xl py-[14px] pl-4 pr-8 text-xs md:text-base focus:border-[#2667FF] focus:ring-[#2667FF] dark:focus:border-[#FFC700] dark:focus:ring-[#FFC700]'
        ]) }}
    >
        @if($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif

        @if(count($options) > 0)
            @foreach($options as $value => $text)
                <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    @if($error)
        <span class="text-red-500 text-sm">{{ $error }}</span>
    @endif
</div>

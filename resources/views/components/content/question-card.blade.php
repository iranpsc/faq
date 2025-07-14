@props([
    'question' => '',
    'description' => '',
    'category' => '',
    'isPinned' => false,
    'author' => '',
    'authorImage' => '',
    'answersCount' => 0,
    'votesCount' => 0,
    'viewsCount' => 0,
    'createdAt' => '',
    'href' => '#',
])

<div class="w-full bg-[#FFFFFF] dark:bg-[#0C0D0F] rounded-xl border border-[#F4F4F4] dark:border-[#191B21] flex flex-col gap-4
    {{ $isPinned ? 'bg-[#E9FAEF66] dark:bg-[#342A0933]' : '' }}">

    <!-- Header -->
    <div class="w-full flex justify-between items-center p-4">
        <div class="flex gap-3">
            @if($category)
                <span class="text-[#5A5F66] bg-[#FFFFFF] dark:bg-black border border-[#DFE2EB] dark:border-[#84858F] p-2 md:px-4 md:py-2 rounded-full text-xs md:text-base">
                    {{ $category }}
                </span>
            @endif

            @if($isPinned)
                <div class="flex gap-3 items-center text-[#20D05C] dark:text-[#FFC700] bg-[#FFFFFF] dark:bg-black border border-[#20D05C] dark:border-[#FFC700] px-4 py-2 rounded-full text-xs md:text-base">
                    <span>پین شده</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path class="dark:stroke-[#FFC700]" d="M2.77977 10.2L5.79977 13.22C7.03977 14.46 9.0531 14.46 10.2998 13.22L13.2264 10.2934C14.4664 9.05337 14.4664 7.04003 13.2264 5.79337L10.1998 2.78003C9.56643 2.1467 8.6931 1.8067 7.79977 1.85337L4.46643 2.01337C3.1331 2.07337 2.0731 3.13337 2.00643 4.46003L1.84643 7.79337C1.80643 8.69337 2.14643 9.5667 2.77977 10.2Z" stroke="#20D05C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path class="dark:stroke-[#FFC700]" d="M6.33317 8.00002C7.25365 8.00002 7.99984 7.25383 7.99984 6.33335C7.99984 5.41288 7.25365 4.66669 6.33317 4.66669C5.4127 4.66669 4.6665 5.41288 4.6665 6.33335C4.6665 7.25383 5.4127 8.00002 6.33317 8.00002Z" stroke="#20D05C" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </div>
            @endif
        </div>

        @if($createdAt)
            <div class="text-[#5A5F66] dark:text-[#84858F] text-xs">
                <time>{{ $createdAt }}</time>
            </div>
        @endif
    </div>

    <!-- Question Content -->
    <div class="mt-[-20px]">
        <a href="{{ $href }}">
            <h2 class="text-[#0F0F0E] md:text-xl dark:text-[#FCFCFC] border-r-2 border-[#20D05C] dark:border-[#FFC700] px-4 py-2 hover:text-[#2667FF] dark:hover:text-[#FFC700] transition-colors">
                {{ $question }}
            </h2>
        </a>

        @if($description)
            <p class="text-[#A8ABB4] dark:text-[#84858F] mt-1 px-4 truncate hidden md:block">
                {{ $description }}
            </p>
        @endif
    </div>

    <!-- Footer Stats -->
    <div class="p-4 flex flex-wrap items-center gap-y-4">
        <!-- Author -->
        @if($author)
            <div class="px-4 flex items-center gap-1 text-sm border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max">
                @if($authorImage)
                    <img class="w-6 h-6 rounded-full" src="{{ $authorImage }}" alt="profile">
                @else
                    <img class="w-6 h-6 rounded-full" src="{{ asset('assets/images/profile.png') }}" alt="profile">
                @endif
                <a href="#" class="text-[#20D05C] dark:text-[#FFC700] hover:underline">{{ $author }}</a>
            </div>
        @endif

        <!-- Answers Count -->
        <div class="px-4 flex items-center gap-1 text-sm border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max">
            <div>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.38672 3.44H11.6134C12.7201 3.44 13.6134 4.33334 13.6134 5.44V7.65334" stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M4.49339 1.33334L2.38672 3.43999L4.49339 5.54668" stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M13.6134 12.56H4.38672C3.28005 12.56 2.38672 11.6667 2.38672 10.56V8.34668" stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M11.5068 14.6667L13.6135 12.56L11.5068 10.4533" stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <a href="#" class="text-[#A8ABB4] dark:text-[#84858F] hover:text-[#2667FF] dark:hover:text-[#FFC700]">{{ $answersCount }} پاسخ</a>
        </div>

        <!-- Votes Count -->
        <div class="px-4 flex items-center gap-1 text-sm border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max">
            <div>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 1.33334V5.33334L9.33333 4.00001" stroke="#5A5F66" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M7.99984 5.33333L6.6665 4" stroke="#5A5F66" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M4.66667 8C2 8 2 9.19333 2 10.6667V11.3333C2 13.1733 2 14.6667 5.33333 14.6667H10.6667C13.3333 14.6667 14 13.1733 14 11.3333V10.6667C14 9.19333 14 8 11.3333 8C10.6667 8 10.48 8.14 10.1333 8.4L9.45333 9.12C8.66667 9.96 7.33333 9.96 6.54 9.12L5.86667 8.4C5.52 8.14 5.33333 8 4.66667 8Z" stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M3.3335 8V5.33333C3.3335 3.99333 3.3335 2.88666 5.3335 2.69333" stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M12.6665 8V5.33333C12.6665 3.99333 12.6665 2.88666 10.6665 2.69333" stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <a href="#" class="text-[#A8ABB4] dark:text-[#84858F] hover:text-[#2667FF] dark:hover:text-[#FFC700] min-w-max">{{ $votesCount }} رای</a>
        </div>

        <!-- Views Count -->
        <div class="px-4 flex items-center gap-1 text-sm">
            <div>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.3866 7.99995C10.3866 9.31995 9.31995 10.3866 7.99995 10.3866C6.67995 10.3866 5.61328 9.31995 5.61328 7.99995C5.61328 6.67995 6.67995 5.61328 7.99995 5.61328C9.31995 5.61328 10.3866 6.67995 10.3866 7.99995Z" stroke="#6A6B74" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M7.9999 13.5133C10.3532 13.5133 12.5466 12.1266 14.0732 9.72665C14.6732 8.78665 14.6732 7.20665 14.0732 6.26665C12.5466 3.86665 10.3532 2.47998 7.9999 2.47998C5.64656 2.47998 3.45323 3.86665 1.92656 6.26665C1.32656 7.20665 1.32656 8.78665 1.92656 9.72665C3.45323 12.1266 5.64656 13.5133 7.9999 13.5133Z" stroke="#6A6B74" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <a href="#" class="text-[#A8ABB4] dark:text-[#84858F] hover:text-[#2667FF] dark:hover:text-[#FFC700]">{{ number_format($viewsCount) }} بازدید</a>
        </div>
    </div>
</div>

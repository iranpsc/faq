<header>
    <!-- Mobile Navigation -->
    <nav class="w-full m-0 p-0 fixed top-0 z-[1000] lg:hidden">
        <div class="w-full items-center flex justify-between p-4 bg-white dark:bg-[#0F0F0E]">
            <div class="flex justify-between items-center w-full fixed right-0 top-0 lg:relative bg-white dark:bg-[#0F0F0E] lg:bg-transparent lg:p-0 px-5 py-4">
                <!-- Mobile menu button -->
                <div class="flex items-center gap-5">
                    <div class="w-max p-3 rounded-full cursor-pointer flex" onclick="openNav2()">
                        <svg class="lg:hidden dark:fill-white" width="30" height="22" viewBox="0 0 30 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="dark:fill-white" fill-rule="evenodd" clip-rule="evenodd" d="M0 1.375C0 0.615608 0.6296 0 1.40625 0H28.5938C29.3704 0 30 0.615608 30 1.375C30 2.13439 29.3704 2.75 28.5938 2.75H1.40625C0.6296 2.75 0 2.13439 0 1.375ZM0 11C0 10.2406 0.6296 9.625 1.40625 9.625H28.5938C29.3704 9.625 30 10.2406 30 11C30 11.7594 29.3704 12.375 28.5938 12.375H1.40625C0.6296 12.375 0 11.7594 0 11ZM0 20.625C0 19.8656 0.6296 19.25 1.40625 19.25H28.5938C29.3704 19.25 30 19.8656 30 20.625C30 21.3844 29.3704 22 28.5938 22H1.40625C0.6296 22 0 21.3844 0 20.625Z" fill="#0713EF" />
                        </svg>
                    </div>
                    <!-- Logo -->
                    <div class="w-max lg:w-auto flex justify-center items-center">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('assets/images/faq-irpsc 1.png') }}" alt="faq">
                        </a>
                    </div>
                </div>
                <!-- Ask Question Button -->
                <div class="w-1/3 flex justify-center">
                    <button class="bg-[#2667FF] dark:bg-[#FFC700] dark:text-black text-white py-3 w-32 rounded-[10px] text-xl openModalBtn">
                        بپرس
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Search -->
    <div class="lg:hidden mt-28">
        <div class="w-full px-7">
            <input type="text" id="searchInput" placeholder="سوال یا کلمه مورد نظر خود را جستجو کنید"
                   class="bg-[#FFFFFF] dark:bg-black border border-[#ECEEF3] focus:border-[#2667FF] focus:ring-[#2667FF] dark:focus:border-[#FFC700] dark:focus:ring-[#FFC700] text-[#A8ABB4] placeholder-[#A8ABB4] focus:ring-1 p-3 rounded-xl w-full">
        </div>

        <!-- Mobile Search Results -->
        <div id="hiddenDiv" class="bg-white dark:bg-[#0C0D0F] w-full hidden absolute top-44 right-0 px-7 md:px-20 duration-1000 transition-all z-10 overflow-hidden">
            <div class="overflow-y-scroll searchScroll h-full py-5">
                <div class="pb-7">
                    <span class="text-[#A8ABB4]">34 مورد یافت شد</span>
                </div>
                <!-- Search results will be populated here -->
            </div>
        </div>
    </div>

</header>

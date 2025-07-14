<!-- Sidebar Navigation -->
<div id="main-nav" class="sidenav !z-[5000]">
    <!-- Opened Navigation -->
    <div id="open00" dir="ltr" class="hidden bg-white dark:bg-[#0F0F0E] p-4 relative pr-0 h-full !overflow-y-scroll scrollbar !z-[5000]">
        <nav dir="rtl" class="w-full space-y-6 relative">
            <div class="space-y-6">
                <!-- Header with Logo -->
                <div class="gap-5 my-2 w-full items-center flex justify-between px-2 bg-white dark:bg-[#0F0F0E]">
                    <div class="flex items-center gap-1">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('assets/icon/3dmeta55.png') }}" alt="">
                        </a>
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('assets/icon/3ddmetaa143.png') }}" alt="">
                        </a>
                    </div>

                    <!-- Close Button -->
                    <div>
                        <div id="close-nav-btn" class="bg-slate-200 dark:bg-[#090909] w-10 h-10 p-3 rounded-full cursor-pointer flex items-center justify-center" onclick="closeNav2()">
                            <img src="{{ asset('assets/icon/aroowww.svg') }}" alt="" class="w-[60%]">
                        </div>
                    </div>
                </div>

                <!-- User Profile Section -->
                <div>
                    <ul class="tree">
                        <li class="flex flex-col gap-4">
                            <input type="checkbox" id="c1" class="peer" />
                            <label class="px-[20px] py-4 w-full rounded-[10px] text-[#282828] font-bold dark:text-[#868B90] peer-checked:[&>div>svg]:rotate-180 transition-[3s] flex items-center" for="c1">
                                <div class="flex w-full items-center justify-between gap-5">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ asset('assets/icon/profile3d.png') }}" alt="" class="w-7 h-7 rounded-full">
                                        <a href="#">نام یوزرنیم</a>
                                    </div>
                                    <svg class="transition-[5s] duration-300" width="15" height="9" viewBox="0 0 15 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="dark:stroke-[#2C2F32]" d="M14 1L7.5 7.5L1 0.999999" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </label>
                            <ul>
                                <li>
                                    <label class="text-[#868B90] before:text-transparent tree_label">
                                        <div class="flex justify-between items-center">
                                            <img src="{{ asset('assets/icon/Union.svg') }}" alt="" class="w-7 h-7">
                                            <a href="#">پروفایل</a>
                                        </div>
                                    </label>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <!-- Shopping Cart -->
                <div class="flex gap-5 items-center select-none border-b-2 pb-5 border-[#EFEFEF] dark:border-[#868B90] text-[#868B90] px-6" for="c2">
                    <img src="{{ asset('assets/icon/bag.svg') }}" alt="" class="w-7 h-7">
                    <p>سبد خرید</p>
                    <div class="bg-[#FF5722] w-5 h-5 rounded-full text-white text-xs font-bold flex justify-center items-center">
                        <span>2</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="h-auto lg:h-[30vh] xl:h-[35vh] 2xl:h-[45vh] 3xl:h-[53vh] overflow-auto space-y-3">
                <!-- 3D Meta -->
                <div class="pr-[20PX] p-[14px] flex items-center gap-4">
                    <img src="{{ asset('assets/icon/3dmetaicon.svg') }}" alt="" class="w-7 h-7">
                    <a href="#" class="text-[#868B90]">سه بعدی متا</a>
                </div>

                <!-- 3D Models Bank -->
                <div>
                    <ul class="tree">
                        <li class="flex flex-col gap-3">
                            <input type="checkbox" id="c2" class="peer" />
                            <label class="px-[20px] py-[14px] w-full rounded-[10px] text-[#868B90] peer-checked:text-white head_label peer-checked:bg-[#000BEE] dark:peer-checked:bg-[#C2008C] peer-checked:[&>div>svg]:rotate-180 peer-checked:[&>div>svg>path]:stroke-white" for="c2">
                                <div class="flex w-full justify-between items-center select-none">
                                    <div class="flex gap-4 items-center">
                                        <img src="{{ asset('assets/icon/Group1000002695.svg') }}" alt="" class="w-7 h-7">
                                        <p>بانک مدل های سه بعدی</p>
                                    </div>
                                    <svg class="transition-[5s] duration-300" width="15" height="9" viewBox="0 0 15 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="stroke-[#868B90]" d="M14 1L7.5 7.5L1 0.999999" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </label>
                            <ul>
                                <li>
                                    <input type="checkbox" id="c3" class="peer" />
                                    <label for="c3" class="tree_label w-full text-[#868B90] peer-checked:text-[#000BEE] dark:peer-checked:text-white peer-checked:bg-[#ECF4FE] dark:peer-checked:bg-[#C2008C]/30">
                                        مدل سه بعدی
                                    </label>
                                    <ul>
                                        <div class="flex flex-col text-sm text-[#000BEE] font-bold gap-5 p-3 pr-[20PX] dark:text-[#868B90]">
                                            <a href="#">آواتار</a>
                                            <a href="#">پکیج آواتار</a>
                                            <a href="#">سه بعدی</a>
                                            <a href="#">پکیج سه بعدی</a>
                                        </div>
                                    </ul>
                                </li>
                                <!-- Other menu items -->
                            </ul>
                        </li>
                    </ul>
                </div>

                <!-- Other Menu Items -->
                <div class="pr-[20PX] p-[14px] flex items-center gap-4">
                    <img src="{{ asset('assets/icon/amoozesh.svg') }}" alt="" class="w-7 h-7">
                    <a href="#" class="text-[#868B90]">آموزش</a>
                </div>

                <div class="pr-[20PX] p-[14px] flex items-center gap-4">
                    <img src="{{ asset('assets/icon/sefareshicon.svg') }}" alt="" class="w-7 h-7">
                    <a href="#" class="text-[#868B90]">ثبت سفارش</a>
                </div>

                <div class="pr-[20PX] p-[14px] flex items-center gap-4">
                    <img src="{{ asset('assets/icon/contactus.svg') }}" alt="" class="w-7 h-7">
                    <a href="#" class="text-[#868B90]">تماس با ما</a>
                </div>

                <div class="pr-[20PX] p-[14px] flex items-center gap-4">
                    <img src="{{ asset('assets/icon/abuotus.svg') }}" alt="" class="w-7 h-7">
                    <a href="#" class="text-[#868B90]">درباره ما</a>
                </div>

                <!-- Language Selection -->
                <div>
                    <ul class="tree">
                        <li class="flex flex-col gap-3">
                            <input type="checkbox" id="c11" class="peer" />
                            <label class="px-[20px] py-4 w-full rounded-[10px] text-[#868B90] peer-checked:text-white head_label peer-checked:bg-[#000BEE] dark:peer-checked:bg-[#C2008C] peer-checked:[&>div>svg]:rotate-180 peer-checked:[&>div>svg>path]:stroke-white" for="c11">
                                <div class="flex w-full justify-between items-center select-none">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ asset('assets/icon/zaban.svg') }}" alt="" class="w-7 h-7">
                                        <p>زبان</p>
                                    </div>
                                    <svg class="transition-[5s] duration-300" width="15" height="9" viewBox="0 0 15 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="stroke-[#868B90]" d="M14 1L7.5 7.5L1 0.999999" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </label>
                            <ul>
                                <div id="zaban" class="flex flex-col text-sm text-[#000BEE] font-bold gap-5 p-3 pr-[20PX] dark:text-[#868B90]">
                                    <a href="#">فارسی</a>
                                    <a href="#">انگیلیسی</a>
                                </div>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="space-y-6 lg:absolute bottom-0 w-full h-auto lg:h-1/5 2xl:h-1/6 bg-white dark:bg-[#0F0F0E] pb-10 lg:pb-1">
                <!-- Login Button -->
                <div class="w-full flex justify-between items-center px-6 p-[10px] font-bold text-white dark:text-black bg-[#2667FF] dark:bg-[#FFC700] rounded-[10px]">
                    <img src="{{ asset('assets/icon/login3d.svg') }}" alt="" class="w-7 h-7">
                    <a href="#" class="font-bold">ورود</a>
                </div>

                <!-- Dark Mode Toggle -->
                <div class="pt-5 border-t-2 border-[#EFEFEF] dark:border-[#868B90]">
                    <div class="flex rounded-full w-full p-[6px] bg-[#F4F4F4] dark:bg-[#090909]">
                        <button class="enable-dark-mode bg-transparent dark:bg-[#0F0F0E] flex justify-center p-1 rounded-full w-1/2">
                            <div>
                                <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                                    <path class="fill-slate-400 dark:fill-white" d="M6.2 1C3.2 1.8 1 4.6 1 7.9 1 11.8 4.2 15 8.1 15c3.3 0 6-2.2 6.9-5.2C9.7 11.2 4.8 6.3 6.2 1Z" />
                                    <path class="fill-slate-500 dark:fill-white" d="M12.5 5a.625.625 0 0 1-.625-.625 1.252 1.252 0 0 0-1.25-1.25.625.625 0 1 1 0-1.25 1.252 1.252 0 0 0 1.25-1.25.625.625 0 1 1 1.25 0c.001.69.56 1.249 1.25 1.25a.625.625 0 1 1 0 1.25c-.69.001-1.249.56-1.25 1.25A.625.625 0 0 1 12.5 5Z" />
                                </svg>
                            </div>
                        </button>
                        <button class="disable-dark-mode bg-[#FCFCFC] dark:bg-transparent flex justify-center p-1 rounded-full w-1/2 shadow-[0_0_6px_0_rgba(0,0,0,0.1)] dark:shadow-none">
                            <div>
                                <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                                    <path class="fill-slate-300 dark:fill-white" d="M7 0h2v2H7zM12.88 1.637l1.414 1.415-1.415 1.413-1.413-1.414zM14 7h2v2h-2zM12.95 14.433l-1.414-1.413 1.413-1.415 1.415 1.414zM7 14h2v2H7zM2.98 14.364l-1.413-1.415 1.414-1.414 1.414 1.415zM0 7h2v2H0zM3.05 1.706 4.463 3.12 3.05 4.535 1.636 3.12z" />
                                    <path class="fill-slate-400 dark:fill-white" d="M8 4C5.8 4 4 5.8 4 8s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4Z" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <!-- Closed Navigation -->
    <div dir="ltr" id="close00" class="h-full">
        <nav dir="rtl" class="bg-[#FCFCFC] dark:bg-[#0F0F0E] pr-0 space-y-9 flex justify-start items-center flex-col py-6 h-full relative">
            <div class="w-full space-y-9 flex flex-col items-center justify-center border-b-2 border-[#EFEFEF] px-3 dark:border-[#868B90] pb-6 lg:h-[40%] xl:[40%] 2xl:h-[35%] 3xl:h-[30%]">
                <!-- Open button -->
                <div id="open-nav-btn" class="items-center w-7 h-7" onclick="openNav2()">
                    <svg class="dark:fill-white" width="30" height="22" viewBox="0 0 30 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path class="dark:fill-white" fill-rule="evenodd" clip-rule="evenodd" d="M0 1.375C0 0.615608 0.6296 0 1.40625 0H28.5938C29.3704 0 30 0.615608 30 1.375C30 2.13439 29.3704 2.75 28.5938 2.75H1.40625C0.6296 2.75 0 2.13439 0 1.375ZM0 11C0 10.2406 0.6296 9.625 1.40625 9.625H28.5938C29.3704 9.625 30 10.2406 30 11C30 11.7594 29.3704 12.375 28.5938 12.375H1.40625C0.6296 12.375 0 11.7594 0 11ZM0 20.625C0 19.8656 0.6296 19.25 1.40625 19.25H28.5938C29.3704 19.25 30 19.8656 30 20.625C30 21.3844 29.3704 22 28.5938 22H1.40625C0.6296 22 0 21.3844 0 20.625Z" fill="#0713EF" />
                    </svg>
                </div>

                <!-- Collapsed menu icons -->
                <div class="w-7 flex items-center justify-center">
                    <a href="{{ route('home') }}" class="w-full">
                        <img src="{{ asset('assets/icon/3ddmetaa143.png') }}" alt="" class="w-full">
                    </a>
                </div>

                <div class="w-7 flex items-center justify-center">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/profile3d.png') }}" alt="" class="w-full border rounded-full">
                    </a>
                </div>

                <div class="w-7 flex items-center justify-center">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/bag.svg') }}" alt="" class="w-full">
                    </a>
                </div>
            </div>

            <!-- Collapsed menu items -->
            <div class="flex items-center flex-col gap-11 h-auto pr-4 lg:h-[30%] xl:h-[35%] 2xl:h-[50%] 3xl:h-[55%] !overflow-y-scroll scrollbar">
                <div class="w-7 h-7">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/3dmetaicon.svg') }}" alt="" class="w-full">
                    </a>
                </div>

                <div class="w-7 h-7">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/Group1000002695.svg') }}" alt="" class="w-full">
                    </a>
                </div>

                <div class="w-7 h-7">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/amoozesh.svg') }}" alt="" class="w-full h-full">
                    </a>
                </div>

                <div class="w-7 h-7">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/sefareshicon.svg') }}" alt="" class="w-full">
                    </a>
                </div>

                <div class="w-7 h-7">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/contactus.svg') }}" alt="" class="w-full">
                    </a>
                </div>

                <div class="w-7 h-7">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/abuotus.svg') }}" alt="" class="w-full">
                    </a>
                </div>

                <div class="w-7 h-7">
                    <a href="#" class="w-full">
                        <img src="{{ asset('assets/icon/zaban.svg') }}" alt="" class="w-full">
                    </a>
                </div>
            </div>

            <!-- Bottom section collapsed -->
            <div class="w-full space-y-6 px-3 h-auto lg:h-[30%] xl:h-[26%] 2xl:h-[15%] bg-white dark:bg-[#0F0F0E] pb-5">
                <div class="w-10 justify-center items-center">
                    <a href="#" class="flex w-full p-1 justify-center items-center aspect-square font-bold text-white bg-[#2667FF] dark:bg-[#FFC700] rounded-[10px]">
                        <img src="{{ asset('assets/icon/login3d.svg') }}" alt="" class="w-[70%]">
                    </a>
                </div>

                <div class="pt-6 border-t-2 border-[#EFEFEF] flex justify-center dark:border-[#868B90]">
                    <div class="flex rounded-full w-max p-[6px] bg-[#F4F4F4] dark:bg-[#090909]">
                        <button class="enable-dark-mode2 dark:hidden bg-transparent dark:bg-[#0F0F0E] flex justify-center items-center p-1 rounded-full w-5 h-5">
                            <div>
                                <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                                    <path class="fill-slate-400 dark:fill-white" d="M6.2 1C3.2 1.8 1 4.6 1 7.9 1 11.8 4.2 15 8.1 15c3.3 0 6-2.2 6.9-5.2C9.7 11.2 4.8 6.3 6.2 1Z" />
                                    <path class="fill-slate-500 dark:fill-white" d="M12.5 5a.625.625 0 0 1-.625-.625 1.252 1.252 0 0 0-1.25-1.25.625.625 0 1 1 0-1.25 1.252 1.252 0 0 0 1.25-1.25.625.625 0 1 1 1.25 0c.001.69.56 1.249 1.25 1.25a.625.625 0 1 1 0 1.25c-.69.001-1.249.56-1.25 1.25A.625.625 0 0 1 12.5 5Z" />
                                </svg>
                            </div>
                        </button>
                        <button class="disable-dark-mode2 hidden bg-[#FCFCFC] dark:bg-transparent dark:flex justify-center items-center p-1 rounded-full w-5 h-5 shadow-[0_0_6px_0_rgba(0,0,0,0.1)] dark:shadow-none">
                            <div>
                                <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                                    <path class="fill-slate-300 dark:fill-white" d="M7 0h2v2H7zM12.88 1.637l1.414 1.415-1.415 1.413-1.413-1.414zM14 7h2v2h-2zM12.95 14.433l-1.414-1.413 1.413-1.415 1.415 1.414zM7 14h2v2H7zM2.98 14.364l-1.413-1.415 1.414-1.414 1.414 1.415zM0 7h2v2H0zM3.05 1.706 4.463 3.12 3.05 4.535 1.636 3.12z" />
                                    <path class="fill-slate-400 dark:fill-white" d="M8 4C5.8 4 4 5.8 4 8s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4Z" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</div>

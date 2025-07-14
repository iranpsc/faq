<x-layouts.layout>
    <!-- Desktop Search Bar -->
    <section class="w-full mx-auto hidden lg:block" style="z-index: 2000; top: 0; left: 0px; position: sticky;">
        <div
            class="flex justify-between w-full bg-white dark:bg-[#0F0F0E] py-4 px-6 drop-shadow-[0_1px_3px_rgba(0,0,0,0.05)] relative">
            <div class="w-2/3">
                <input type="text" id="searchInput2" placeholder="سوال یا کلمه مورد نظر خود را جستجو کنید"
                    class="mr-5 bg-[#FFFFFF] dark:bg-black border border-[#ECEEF3] focus:border-[#2667FF] focus:ring-[#2667FF] dark:focus:border-[#FFC700] dark:focus:ring-[#FFC700] text-[#A8ABB4] placeholder-[#A8ABB4] focus:ring-1 p-3 rounded-xl max-w-sm w-full">
            </div>
            <div id="hiddenDiv2"
                class="suggestions bg-white dark:bg-[#0C0D0F] w-full absolute top-20 right-0 px-7 md:px-20 duration-1000 transition-all overflow-hidden overflow-y-scroll searchScroll hidden">
                <!-- Search results would go here -->
            </div>
        </div>
    </section>

    <main class="w-full main-content-smallNav relative">
        <section class="w-full mx-auto max-w-[1500px] mt-10 px-5">
            <!-- Question Header -->
            <div
                class="bg-[#FFFFFF] dark:bg-[#0C0D0F] border border-[#ECEEF3] dark:border-[#191B21] rounded-xl w-full p-5 flex flex-col gap-7">
                <div class="flex justify-between items-center">
                    <div class="flex gap-3">
                        <span
                            class="text-[#5A5F66] bg-[#FFFFFF] dark:bg-black border border-[#DFE2EB] dark:border-[#84858F] px-4 py-2 rounded-full">املاک
                            و مستغلات</span>
                    </div>
                    <div class="text-[#5A5F66] dark:text-[#84858F] text-xs">
                        <time>22 فروردین 1403</time>
                    </div>
                </div>

                <h1
                    class="text-[#0F0F0E] text-xl dark:text-[#FCFCFC] border-r-2 border-[#20D05C] dark:border-[#FFC700] px-4 py-2">
                    استاندارد متاورس چیست و استاندارد های متاورس را چه کسانی با چه هدفی عنوان خواهند کرد ؟
                </h1>

                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('assets/images/profile.png') }}" alt="User"
                            class="w-10 h-10 rounded-full">
                        <div>
                            <a href="#" class="text-[#20D05C] dark:text-[#FFC700]">مهدی قربان زاده</a>
                            <span class="text-[#5A5F66] dark:text-[#84858F] text-sm ml-2">امتیاز: 65,431</span>
                        </div>
                    </div>

                    <div class="items-center hidden md:flex">
                        <div
                            class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#ECEEF3] dark:border-[#191B21]">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10.3866 7.99995C10.3866 9.31995 9.31995 10.3866 7.99995 10.3866C6.67995 10.3866 5.61328 9.31995 5.61328 7.99995C5.61328 6.67995 6.67995 5.61328 7.99995 5.61328C9.31995 5.61328 10.3866 6.67995 10.3866 7.99995Z"
                                    stroke="#6A6B74" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M7.9999 13.5133C10.3532 13.5133 12.5466 12.1266 14.0732 9.72665C14.6732 8.78665 14.6732 7.20665 14.0732 6.26665C12.5466 3.86665 10.3532 2.47998 7.9999 2.47998C5.64656 2.47998 3.45323 3.86665 1.92656 6.26665C1.32656 7.20665 1.32656 8.78665 1.92656 9.72665C3.45323 12.1266 5.64656 13.5133 7.9999 13.5133Z"
                                    stroke="#6A6B74" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <a href="#" class="text-[#A8ABB4] dark:text-[#84858F]">3.982 بازدید</a>
                        </div>
                        <div
                            class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#ECEEF3] dark:border-[#191B21]">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M14.1665 15.3588H10.8332L7.12483 17.8254C6.57483 18.1921 5.83317 17.8004 5.83317 17.1338V15.3588C3.33317 15.3588 1.6665 13.6921 1.6665 11.1921V6.19206C1.6665 3.69206 3.33317 2.02539 5.83317 2.02539H14.1665C16.6665 2.02539 18.3332 3.69206 18.3332 6.19206V11.1921C18.3332 13.6921 16.6665 15.3588 14.1665 15.3588Z"
                                    stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M10 9.4668V9.29183C10 8.72516 10.35 8.42515 10.7 8.18348C11.0417 7.95015 11.3833 7.65016 11.3833 7.10016C11.3833 6.33349 10.7667 5.7168 10 5.7168C9.23334 5.7168 8.6167 6.33349 8.6167 7.10016"
                                    stroke="#5A5F66" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M9.99641 11.4577H10.0039" stroke="#5A5F66" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <a href="#" class="text-[#A8ABB4] dark:text-[#84858F]">گزارش</a>
                        </div>
                        <div
                            class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#ECEEF3] dark:border-[#191B21]">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M11.05 3.00002L4.20829 10.2417C3.94996 10.5167 3.69996 11.0584 3.64996 11.4334L3.34162 14.1334C3.23329 15.1084 3.93329 15.775 4.89996 15.6084L7.58329 15.15C7.95829 15.0834 8.48329 14.8084 8.74162 14.525L15.5833 7.28335C16.7666 6.03335 17.3 4.60835 15.4583 2.86668C13.625 1.14168 12.2333 1.75002 11.05 3.00002Z"
                                    stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M9.9082 4.20801C10.2665 6.50801 12.1332 8.26634 14.4499 8.49967"
                                    stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M2.5 18.333H17.5" stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <a href="#" class="text-[#A8ABB4] dark:text-[#84858F]">ویرایش</a>
                        </div>
                        <div
                            class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#ECEEF3] dark:border-[#191B21]">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M3.47483 12.7502L7.24983 16.5252C8.79983 18.0752 11.3165 18.0752 12.8748 16.5252L16.5332 12.8668C18.0832 11.3168 18.0832 8.80016 16.5332 7.24183L12.7498 3.47516C11.9582 2.6835 10.8665 2.2585 9.74983 2.31683L5.58316 2.51683C3.9165 2.59183 2.5915 3.91683 2.50816 5.57516L2.30816 9.74183C2.25816 10.8668 2.68316 11.9585 3.47483 12.7502Z"
                                    stroke="#5A5F66" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M7.91634 9.99967C9.06693 9.99967 9.99967 9.06693 9.99967 7.91634C9.99967 6.76575 9.06693 5.83301 7.91634 5.83301C6.76575 5.83301 5.83301 6.76575 5.83301 7.91634C5.83301 9.06693 6.76575 9.99967 7.91634 9.99967Z"
                                    stroke="#5A5F66" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                            <a href="#" class="text-[#A8ABB4] dark:text-[#84858F]">پین کردن</a>
                        </div>
                        <div
                            class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#ECEEF3] dark:border-[#191B21]">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.5 4.98307C14.725 4.70807 11.9333 4.56641 9.15 4.56641C7.5 4.56641 5.85 4.64974 4.2 4.81641L2.5 4.98307"
                                    stroke="#EB5757" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M7.0835 4.14102L7.26683 3.04935C7.40016 2.25768 7.50016 1.66602 8.9085 1.66602H11.0918C12.5002 1.66602 12.6085 2.29102 12.7335 3.05768L12.9168 4.14102"
                                    stroke="#EB5757" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M15.7082 7.61719L15.1665 16.0089C15.0748 17.3172 14.9998 18.3339 12.6748 18.3339H7.32484C4.99984 18.3339 4.92484 17.3172 4.83317 16.0089L4.2915 7.61719"
                                    stroke="#EB5757" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M8.6084 13.75H11.3834" stroke="#EB5757" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M7.9165 10.416H12.0832" stroke="#EB5757" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <a href="#" class="text-[#EB5757]">حذف</a>
                        </div>
                    </div>

                    <div class="items-center flex">
                        <div
                            class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#DFE2EB] dark:border-[#191B21]">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.2334 15.292L8.81673 17.292C9.15006 17.6254 9.90006 17.792 10.4001 17.792H13.5667C14.5667 17.792 15.6501 17.042 15.9001 16.042L17.9001 9.95871C18.3167 8.79205 17.5667 7.79205 16.3167 7.79205H12.9834C12.4834 7.79205 12.0667 7.37538 12.1501 6.79205L12.5667 4.12538C12.7334 3.37538 12.2334 2.54205 11.4834 2.29205C10.8167 2.04205 9.9834 2.37538 9.65006 2.87538L6.2334 7.95871"
                                    stroke="#343434" stroke-width="1.5" stroke-miterlimit="10" />
                                <path
                                    d="M1.9834 15.291V7.12435C1.9834 5.95768 2.4834 5.54102 3.65007 5.54102H4.4834C5.65006 5.54102 6.15007 5.95768 6.15007 7.12435V15.291C6.15007 16.4577 5.65006 16.8743 4.4834 16.8743H3.65007C2.4834 16.8743 1.9834 16.4577 1.9834 15.291Z"
                                    stroke="#343434" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <a href="#"
                                class="text-[#A8ABB4] dark:text-[#84858F] text-xs md:text-base">3.982</a>
                        </div>
                        <div
                            class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#DFE2EB] dark:border-[#191B21]">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M13.7668 4.70898L11.1834 2.70898C10.8501 2.37565 10.1001 2.20898 9.6001 2.20898H6.43344C5.43344 2.20898 4.3501 2.95898 4.1001 3.95898L2.1001 10.0423C1.68344 11.209 2.43344 12.209 3.68344 12.209H7.01677C7.51677 12.209 7.93344 12.6257 7.8501 13.209L7.43344 15.8756C7.26677 16.6256 7.76677 17.459 8.51677 17.709C9.18344 17.959 10.0168 17.6256 10.3501 17.1256L13.7668 12.0423"
                                    stroke="#343434" stroke-width="1.5" stroke-miterlimit="10" />
                                <path
                                    d="M18.0168 4.70833V12.875C18.0168 14.0417 17.5168 14.4583 16.3501 14.4583H15.5168C14.3501 14.4583 13.8501 14.0417 13.8501 12.875V4.70833C13.8501 3.54167 14.3501 3.125 15.5168 3.125H16.3501C17.5168 3.125 18.0168 3.54167 18.0168 4.70833Z"
                                    stroke="#343434" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <a href="#"
                                class="text-[#A8ABB4] dark:text-[#84858F] text-xs md:text-base">3.982</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Content with Sidebar -->
            <div class="flex flex-col lg:flex-row gap-5 mt-5">
                <div class="w-full lg:w-3/4">
                    <img src="{{ asset('assets/images/pooster.jpg') }}" alt="" class="w-full rounded-[20px]">
                    <div class="flex items-center gap-1 mt-2">
                        <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M14.6668 8.50065C14.6668 12.1807 11.6802 15.1673 8.00016 15.1673C4.32016 15.1673 1.3335 12.1807 1.3335 8.50065C1.3335 4.82065 4.32016 1.83398 8.00016 1.83398C11.6802 1.83398 14.6668 4.82065 14.6668 8.50065Z"
                                stroke="#20D05C" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path
                                d="M10.4734 10.6192L8.40675 9.38586C8.04675 9.17253 7.75342 8.65919 7.75342 8.23919V5.50586"
                                stroke="#20D05C" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        <span class="text-[#63DE8D] dark:text-dark-yellow text-xs md:text-sm">2 ساعت پیش توسط مهدی
                            قربان زاده ویرایش شد</span>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="w-full lg:w-1/4">
                    <div
                        class="w-full max-w-full dark:text-[#FCFCFC] rounded-xl border border-[#F4F4F4] dark:border-0 min-h-[540px] p-3 bg-white dark:bg-[#0C0D0F] dark:border-[#33353B] flex flex-col gap-7 max-h-[540px] overflow-y-auto !overflow-x-hidden">
                        <div class="text-center">
                            <span class="text-[#173E99] dark:text-[#FCFCFC] text-center">مطالب پیشنهادی</span>
                        </div>

                        <!-- Suggested Content Items -->
                        @for ($i = 0; $i < 5; $i++)
                            <div class="w-full">
                                <div class="flex gap-3 overflow-x-hidden h-max w-full">
                                    <div class="w-[25%]">
                                        <img src="{{ asset('assets/images/5ddef01d39f33b711ad7da10be137285.jpg') }}"
                                            alt="img" class="rounded-lg w-full aspect-square">
                                    </div>
                                    <div class="flex flex-col justify-between items-center w-[75%] py-2">
                                        <div class="flex truncate items-center w-full text-xs">
                                            <a href="#"
                                                class="text-[#0F0F0E] dark:text-[#FCFCFC] truncate text-sm">مبلغ بیعانه
                                                برای اجاره خانه بر چه مبنایی تعیین میشه؟</a>
                                        </div>
                                        <div class="flex truncate items-center w-full text-xs">
                                            <a href="#"
                                                class="truncate border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max text-[#A8ABB4] dark:text-[#84858F] px-2"
                                                title="رشته های ورزشی">رشته های ورزشی</a>
                                            <a href="#"
                                                class="truncate border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max text-[#A8ABB4] dark:text-[#84858F] px-2"
                                                title="مهدی قربان زاده">مهدی قربان زاده</a>
                                            <a href="#"
                                                class="truncate border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max text-[#A8ABB4] dark:text-[#84858F] px-2"
                                                title="13 اردیبهشت 1402">13 اردیبهشت 1402</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- Question Content -->
        <section class="w-full mx-auto max-w-[1500px] px-5 mt-7">
            <div class="text-[#0F0F0E] dark:text-[#ECEEF3] text-sm md:text-lg font-normal leading-[33px]">
                <p>
                    از این رو استاندارد به شما کمک خواهد کرد که بتوانید محصول مورد نظر را از نظر یکسری خصوصیات تایید شده
                    بدانید،
                    چرا هر استاندارد نشانگر رعایت یکسری چهارچوب برای تولید محصول میباشد.
                    دنیای متاورس نیز میبایست یکسری خصوصیات داشته باشد که از نظر جامعه جهانی و نیز شهروندان تایید و به
                    بهره برداری برسد.
                    چهارچوبی که برای دنیاهای موازی متاورس درنظر گرفته میشود همان استانداردها هستند.
                </p>
            </div>

            <!-- Promotional Cards -->
            <div class="flex flex-col lg:flex-row gap-7 items-center my-10">
                <div class="bg-[#1E52CC] w-full lg:w-1/2 rounded-xl">
                    <div class="flex flex-col gap-5 p-7">
                        <p class="text-white text-xl">1,432 فعال انجمن متاورس رو میشناسی؟</p>
                        <span class="text-[#DFE2EB] text-xs">
                            لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک
                            است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است
                        </span>
                        <a href="#" class="dark:bg-black bg-white rounded-xl px-5 py-3 w-max">
                            <span class="text-[#1E52CC] text-sm">فعالان انجمن</span>
                        </a>
                    </div>
                </div>
                <div class="bg-[#1DBB53] w-full lg:w-1/2 rounded-xl">
                    <div class="flex flex-col gap-5 p-7">
                        <p class="text-white text-xl">1,432 فعال انجمن متاورس رو میشناسی؟</p>
                        <span class="text-[#DFE2EB] text-xs">
                            لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک
                            است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است
                        </span>
                        <a href="#" class="dark:bg-black bg-white rounded-xl px-5 py-3 w-max">
                            <span class="text-[#20D05C] text-sm">فعالان انجمن</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Content Sections -->
            <div class="space-y-5 my-7">
                <h2 class="text-[#0F0F0E] dark:text-[#ECEEF3] text-lg md:text-xl">چرا استاندارد متاورس برای کشور های
                    خارجی حائز اهمیت است؟</h2>
                <ul
                    class="text-[#0F0F0E] dark:text-[#ECEEF3] text-sm md:text-lg font-normal leading-[33px] list-disc px-7">
                    <li>کنترل عملکرد تمام شرکت های تولید کننده دنیای موازی متاورس.</li>
                    <li>کسب درآمد از تحویل تاییدیه.</li>
                    <li>قدرت نمایی به منظور مدیریت ذهن عموم جامعه جهانی نسبت به محصولات تولید شده خود.</li>
                    <li>ایجاد بیس اتصال میان دنیاهای موازی با یکدیگر به جهت دریافت کمیسیون اتصال و مدیریت اتصال بین
                        تمامی متاورس ها در آینده.</li>
                    <li>حذف و تحریم شرکت های تولید کننده متاورس با هدف کنترل تمامی انسان ها متناسب با ساختار های سیاسی،
                        اقتصادی، فرهنگی با اهداف خصوصی</li>
                </ul>
            </div>

            <div class="space-y-5 my-7">
                <h2 class="text-[#0F0F0E] dark:text-[#ECEEF3] text-lg md:text-xl">استاندارد های متاورس شامل چه مواردی
                    خواهد بود؟</h2>
                <p class="text-[#0F0F0E] dark:text-[#ECEEF3] text-sm md:text-lg font-normal leading-[33px]">بخشی
                    استانداردهایی که برای متاورس ایرانی "متاورس رنگ" درنظر گرفته شده است به شرح زیر است.</p>
                <ul
                    class="text-[#0F0F0E] dark:text-[#ECEEF3] text-sm md:text-lg font-normal leading-[33px] list-disc px-7">
                    <li>کنترل عملکرد تمام شرکت های تولید کننده دنیای موازی متاورس.</li>
                    <li>کسب درآمد از تحویل تاییدیه.</li>
                    <li>قدرت نمایی به منظور مدیریت ذهن عموم جامعه جهانی نسبت به محصولات تولید شده خود.</li>
                    <li>ایجاد بیس اتصال میان دنیاهای موازی با یکدیگر به جهت دریافت کمیسیون اتصال و مدیریت اتصال بین
                        تمامی متاورس ها در آینده.</li>
                    <li>حذف و تحریم شرکت های تولید کننده متاورس با هدف کنترل تمامی انسان ها متناسب با ساختار های سیاسی،
                        اقتصادی، فرهنگی با اهداف خصوصی</li>
                </ul>
                <p class="text-[#0F0F0E] dark:text-[#ECEEF3] text-sm md:text-lg font-normal leading-[33px]">درمیان شرکت
                    ها و اعضایی که خواهان استاندارد دهی به متاورس هستند متا و مایکروسافت است که میدرخشند چرا که این شرکت
                    ها در صنعت دنیای موازی متاورس میدانند محصولاتشان یا در رقابت پیروز میشود یا درصورتی که پیروزی در
                    محصولات فیزیکی نداشته باشند دنیای موازی متاورس میتواند تهدید بزرگی برای اقتصاد شرکت ها باشد.</p>
            </div>
        </section>

        <!-- Comments Section -->
        <section class="w-full mx-auto max-w-[1500px] mt-10 px-5">
            <div class="w-full relative">
                <div
                    class="bg-[#FFFFFF] dark:bg-[#0C0D0F] border border-[#ECEEF3] dark:border-[#191B21] rounded-xl w-full p-5 flex flex-col gap-10 h-[945px] overflow-y-auto overflow-x-hidden relative">
                    <span class="text-[#0F0F0E] text-2xl dark:text-[#FCFCFC]">نظرات شما</span>

                    <!-- Answer Form -->
                    <div class="bg-[#ECEEF3] dark:bg-[#191B21] w-full rounded-[20px] p-5 flex flex-col gap-6">
                        <div class="w-full grid grid-cols-2 justify-between items-center gap-y-4">
                            <div class="flex gap-3 w-max">
                                <div class="flex gap-3 items-center">
                                    <div class="w-[32px] aspect-square overflow-hidden rounded-full">
                                        <img src="{{ asset('assets/images/5ddef01d39f33b711ad7da10be137285.jpg') }}"
                                            alt="" class="w-full">
                                    </div>
                                    <div class="flex gap-3 items-center">
                                        <a href="#"
                                            class="text-[#0F0F0E] dark:text-[#FCFCFC] text-sm md:text-base">مهدی قربان
                                            زاده</a>
                                        <span
                                            class="text-primery-blue dark:text-dark-yellow text-xs md:text-sm">امتیاز:
                                            65,431</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-[#5A5F66] dark:text-[#84858F] text-xs w-full md:text-left">
                                <time>22 فروردین 1403</time>
                            </div>
                        </div>

                        <!-- CKEditor Container -->
                        <div
                            class="overflow-x-hidden border border-[#DFE2EB] dark:border-[#33353B] focus:border-primery-blue rounded-xl focus:dark:border-dark-yellow relative">
                            <textarea id="editor" name="editor" class="w-full"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button
                                class="bg-[#2667FF] dark:bg-[#FFC700] text-white dark:text-black py-3 px-6 rounded-xl">
                                ارسال پاسخ
                            </button>
                        </div>
                    </div>

                    <!-- Existing Comments -->
                    @for ($i = 0; $i < 3; $i++)
                        <div class="dark:bg-[#0C0D0F] bg-[#FFFFFF] w-full rounded-[20px] p-5 flex flex-col gap-6">
                            <div class="w-full grid grid-cols-2 justify-between items-center gap-y-4">
                                <div class="flex gap-3 w-max">
                                    <div class="flex gap-3 items-center">
                                        <div class="w-[32px] aspect-square overflow-hidden rounded-full">
                                            <img src="{{ asset('assets/images/5ddef01d39f33b711ad7da10be137285.jpg') }}"
                                                alt="" class="w-full">
                                        </div>
                                        <div class="flex gap-3 items-center">
                                            <a href="#"
                                                class="text-[#0F0F0E] dark:text-[#FCFCFC] text-sm md:text-base">مهدی
                                                قربان زاده</a>
                                            <span
                                                class="text-primery-blue dark:text-dark-yellow text-xs md:text-sm">امتیاز:
                                                65,431</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-[#5A5F66] dark:text-[#84858F] text-xs w-full md:text-left">
                                    <time>22 فروردین 1403</time>
                                </div>
                            </div>

                            <div>
                                <p class="dark:text-[#DEDEE9] text-[#343434] text-xs md:text-base">سخت و متون و فرهنگ
                                    پیشرو فراوان و با موجود شامل جامعه که، در صنعت طراحان خلاقی داشت استفاده و کاربردهای
                                    از شرایط و متخصصان جامعه ابزارهای کاربردی لازم در با هدف طراحان طراحان خلاقی در و
                                    سطر و مجله برای طراحی با، شامل و دشواری در بلکه شناخبردهای موجود و مجله می باشد و
                                    زمان بهبود در این صورت تایپ سخت</p>
                            </div>

                            <div class="flex justify-between gap-10 items-center">
                                <div class="items-center flex-wrap gap-y-4 flex">
                                    <div
                                        class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#DFE2EB] dark:border-[#191B21]">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M6.2334 15.292L8.81673 17.292C9.15006 17.6254 9.90006 17.792 10.4001 17.792H13.5667C14.5667 17.792 15.6501 17.042 15.9001 16.042L17.9001 9.95871C18.3167 8.79205 17.5667 7.79205 16.3167 7.79205H12.9834C12.4834 7.79205 12.0667 7.37538 12.1501 6.79205L12.5667 4.12538C12.7334 3.37538 12.2334 2.54205 11.4834 2.29205C10.8167 2.04205 9.9834 2.37538 9.65006 2.87538L6.2334 7.95871"
                                                stroke="#343434" stroke-width="1.5" stroke-miterlimit="10" />
                                            <path
                                                d="M1.9834 15.291V7.12435C1.9834 5.95768 2.4834 5.54102 3.65007 5.54102H4.4834C5.65006 5.54102 6.15007 5.95768 6.15007 7.12435V15.291C6.15007 16.4577 5.65006 16.8743 4.4834 16.8743H3.65007C2.4834 16.8743 1.9834 16.4577 1.9834 15.291Z"
                                                stroke="#343434" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        <a href="#"
                                            class="text-[#A8ABB4] dark:text-[#84858F] text-xs md:text-base">3.982</a>
                                    </div>
                                    <div
                                        class="px-4 flex items-center gap-1 text-sm min-w-max border-l border-[#DFE2EB] dark:border-[#191B21]">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M13.7668 4.70898L11.1834 2.70898C10.8501 2.37565 10.1001 2.20898 9.6001 2.20898H6.43344C5.43344 2.20898 4.3501 2.95898 4.1001 3.95898L2.1001 10.0423C1.68344 11.209 2.43344 12.209 3.68344 12.209H7.01677C7.51677 12.209 7.93344 12.6257 7.8501 13.209L7.43344 15.8756C7.26677 16.6256 7.76677 17.459 8.51677 17.709C9.18344 17.959 10.0168 17.6256 10.3501 17.1256L13.7668 12.0423"
                                                stroke="#343434" stroke-width="1.5" stroke-miterlimit="10" />
                                            <path
                                                d="M18.0168 4.70833V12.875C18.0168 14.0417 17.5168 14.4583 16.3501 14.4583H15.5168C14.3501 14.4583 13.8501 14.0417 13.8501 12.875V4.70833C13.8501 3.54167 14.3501 3.125 15.5168 3.125H16.3501C17.5168 3.125 18.0168 3.54167 18.0168 4.70833Z"
                                                stroke="#343434" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        <a href="#"
                                            class="text-[#A8ABB4] dark:text-[#84858F] text-xs md:text-base">3.982</a>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 text-sm min-w-max">
                                    <a href="#" class="text-[#20D05C] dark:text-dark-yellow">تایید شده</a>
                                </div>
                            </div>

                            <div
                                class="bg-[#FBFDFF] dark:bg-[#191B21] dark:border-[#33353B] dark:text-[#A8ABB4] border border-[#ECEEF3] w-full md:w-[70%] flex items-center justify-center px-4 py-1 rounded-xl">
                                <input type="text"
                                    class="placeholder:text-[#A8ABB4] w-full bg-transparent ring-0 focus:ring-0 border-0 focus:border-0"
                                    placeholder="دیدگاه خود را وارد کنید...">
                                <div>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M2.99983 12.0005L19.3018 19.9665C19.3949 20.0023 19.4965 20.0096 19.5937 19.9875C19.6909 19.9654 19.7793 19.915 19.8478 19.8425C19.9183 19.7681 19.9668 19.6757 19.988 19.5755C20.0092 19.4752 20.0022 19.371 19.9678 19.2745L17.4998 12.0005M2.99983 12.0005L19.3018 4.03452C19.3949 3.99876 19.4965 3.99145 19.5937 4.01353C19.6909 4.0356 19.7793 4.08607 19.8478 4.15852C19.9183 4.23289 19.9668 4.32532 19.988 4.42558C20.0092 4.52583 20.0022 4.62999 19.9678 4.72652L17.4998 12.0005M2.99983 12.0005L17.4998 12.0005"
                                            stroke="#868B90" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </section>
    </main>

    @push('scripts')
        <script>
            ClassicEditor
                .create(document.querySelector('#editor'), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote',
                            '|',
                            'insertTable', 'mediaEmbed',
                            '|',
                            'undo', 'redo', 'alignment', 'fontColor', 'fontBackgroundColor', 'highlight'
                        ]
                    },
                    language: 'fa',
                    table: {
                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                    },
                    licenseKey: '',
                })
                .then(editor => {
                    if (document.documentElement.classList.contains('dark')) {
                        editor.ui.view.editable.element.classList.add('dark-mode');
                    }

                    const observer = new MutationObserver(mutations => {
                        mutations.forEach(mutation => {
                            if (mutation.attributeName === 'class') {
                                if (document.documentElement.classList.contains('dark')) {
                                    editor.ui.view.editable.element.classList.add('dark-mode');
                                } else {
                                    editor.ui.view.editable.element.classList.remove('dark-mode');
                                }
                            }
                        });
                    });

                    observer.observe(document.documentElement, {
                        attributes: true
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush

    @push('styles')
        <style>
            .ck.ck-toolbar {
                border: none !important;
                padding-top: 20px !important;
                padding-bottom: 20px !important;
            }

            .ck.ck-content {
                height: 200px;
                border-bottom: none !important;
                border-right: none !important;
                border-left: none !important;
                border-color: #DFE2EB !important;
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

            .dark .ck.ck-content {
                background-color: #1e1e1e;
                color: #e0e0e0;
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

            .cke_inner {
                background-color: #ffffff;
                border-radius: 10px;
            }

            .cke_editable {
                background-color: #ffffff;
                color: #000000;
                border-radius: 10px;
            }

            .dark-mode .cke_top,
            .dark .cke_bottom {
                background-color: #2e2e2e !important;
                color: #ffffff !important;
                border-radius: 10px;
            }

            .dark .cke_inner {
                background-color: #1e1e1e !important;
                border-radius: 10px;
            }

            .dark .cke_editable {
                background-color: #1e1e1e !important;
                color: #ffffff !important;
                border-radius: 10px;
            }
        </style>
    @endpush

</x-layouts.layout>

<x-layouts.layout title="سامانه پرسش و پاسخ">
    <!-- Desktop Search Bar -->
    <section class="w-full mx-auto hidden lg:block sticky top-0" style="z-index: 2000; left: 0px;">
        <div class="flex justify-between w-full bg-white dark:bg-[#0F0F0E] py-4 px-6 drop-shadow-[0_1px_3px_rgba(0,0,0,0.05)] relative">
            <div class="w-2/3">
                <input type="text" id="searchInput2" placeholder="سوال یا کلمه مورد نظر خود را جستجو کنید"
                       class="mr-5 bg-[#FFFFFF] dark:bg-black border border-[#ECEEF3] focus:border-[#2667FF] focus:ring-[#2667FF] dark:focus:border-[#FFC700] dark:focus:ring-[#FFC700] text-[#A8ABB4] placeholder-[#A8ABB4] focus:ring-1 p-3 rounded-xl max-w-sm w-full">
            </div>

            <!-- Desktop Search Results -->
            <div id="hiddenDiv2" class="suggestions bg-white dark:bg-[#0C0D0F] w-full absolute top-20 right-0 px-7 md:px-20 duration-1000 transition-all overflow-hidden overflow-y-scroll searchScroll">
                <div class="h-full py-5">
                    <div class="pb-7">
                        <span class="text-[#A8ABB4]">34 مورد یافت شد</span>
                    </div>
                    <!-- Search results will be populated here -->
                </div>
            </div>

            <!-- Ask Question Button -->
            <div class="w-1/3 flex justify-center">
                <button id="openModalBtn" class="bg-[#2667FF] dark:bg-[#FFC700] dark:text-black text-white py-3 w-32 rounded-[10px] text-xl 3xl:ml-[-200px]">
                    بپرس
                </button>
            </div>
        </div>
    </section>

    <!-- Question Modal -->
    <x-ui.modal id="modal" title="سوال خود را وارد کنید">
        <div class="text-sm md:text-xl dark:text-[#DEDEE9] text-center mb-4">
            مشخصات مربوط به سوال خود را در کادرهای زیر وارد کنید.
        </div>

        <div class="flex items-center gap-2 mb-4 mt-3">
            <div class="rounded-full overflow-hidden bg-[#E9F0FF80] flex items-center justify-center w-[40px] aspect-square">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="dark:stroke-[#FFC700]" d="M8 7.9987C9.84095 7.9987 11.3333 6.50631 11.3333 4.66536C11.3333 2.82442 9.84095 1.33203 8 1.33203C6.15905 1.33203 4.66667 2.82442 4.66667 4.66536C4.66667 6.50631 6.15905 7.9987 8 7.9987Z" stroke="#93B3FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path class="dark:stroke-[#FFC700]" d="M13.7267 14.6667C13.7267 12.0867 11.16 10 8 10C4.84 10 2.27334 12.0867 2.27334 14.6667" stroke="#93B3FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <a href="#" class="text-[#2667FF] text-xs md:text-sm font-normal dark:text-[#FFC700]">وارد شوید</a>
        </div>

        <form class="flex flex-col gap-[23px]">
            <x-ui.select
                label="دسته بندی"
                placeholder="انتخاب دسته بندی"
                class="w-full md:w-1/2"
            >
                <option value="crypto">ارز دیجیتال</option>
                <option value="real-estate">املاک و مستغلات</option>
                <option value="technology">فناوری</option>
            </x-ui.select>

            <x-ui.input
                label="عنوان سوال"
                placeholder="عنوان سوال خود را وارد کنید..."
                required
            />

            <div class="flex flex-col gap-2">
                <span class="text-[#0F0F0E] text-sm dark:text-[#FCFCFC]">شرح سوال</span>
                <div class="overflow-x-hidden border border-[#DFE2EB] dark:border-[#33353B] focus:border-[#2667FF] rounded-xl focus:dark:border-[#FFC700] relative">
                    <textarea name="editor" id="editor" class="!relative !border-0"></textarea>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-[#0F0F0E] text-sm dark:text-[#FCFCFC]">برچسب ها</span>
                <p class="text-[#5A5F66] text-xs font-normal dark:text-[#A0A0AB]">
                    مثال : سوالی درباره کود مناسب درختان نوشته اید پس برچسب ها میتواند ( کود مناسب ، تغذیه درختان ، مواد غذایی برای درخت ، کود برای رشد درخت ، رشد بهتر درخت ) باشد .
                </p>
                <select class="my-select" multiple="multiple" style="width: 100%;">
                    <option value="1">گزینه 1</option>
                    <option value="2">گزینه 2</option>
                    <option value="3">گزینه 3</option>
                    <option value="4">گزینه 4</option>
                </select>
                <div class="tags-container"></div>
            </div>

            <x-ui.button type="submit" class="w-full md:w-[141px] h-[48px]">
                ثبت سوال
            </x-ui.button>
        </form>
    </x-ui.modal>

    <!-- Main Content -->
    <section class="w-full mx-auto max-w-[1500px]">
        <!-- Categories Section -->
        <div class="flex flex-col gap-10 items-center px-5 mt-10">
            <div class="flex justify-start w-full">
                <span class="text-[#081533] text-xl dark:text-[#FCFCFC]">دسته بندي</span>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-7 w-full justify-between">
                @for($i = 0; $i < 9; $i++)
                    <a href="#" class="flex gap-[10px] dark:text-[#FCFCFC] bg-[#FFFFFF] dark:bg-[#0C0D0F] dark:border-[#191B21] border border-[#F4F4F4] rounded-xl px-2 py-2 md:px-6 md:py-4 w-full items-center hover:border-[#2667FF] dark:hover:border-[#FFC700] transition-colors">
                        <div class="bg-[#ECEEFF] dark:bg-[#201A06] rounded-full p-[1px] w-[33px] h-[33px]">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path class="dark:stroke-[#A8ABB4]" d="M5.96016 15.2267L3.64017 12.9067L1.3335 15.2267" stroke="#0F0F0E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path class="dark:stroke-[#A8ABB4]" d="M26.04 16.7734L28.36 19.0934L30.68 16.7734" stroke="#0F0F0E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path class="dark:stroke-[#A8ABB4]" d="M28.3465 19.0933V16C28.3465 9.17333 22.8132 3.65332 15.9999 3.65332C12.1065 3.65332 8.62653 5.4667 6.35986 8.28003" stroke="#0F0F0E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path class="dark:stroke-[#A8ABB4]" d="M3.65332 12.9067V16C3.65332 22.8267 9.18666 28.3467 16 28.3467C19.8933 28.3467 23.3733 26.5333 25.64 23.72" stroke="#0F0F0E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path class="dark:stroke-[#A8ABB4]" d="M12 11.3333H17.84C19.1333 11.3333 20.1733 12.5066 20.1733 13.6666C20.1733 14.96 19.1333 16 17.84 16H12V11.3333Z" stroke="#0F0F0E" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                <path class="dark:stroke-[#A8ABB4]" d="M12 16H18.6667C20.1333 16 21.3333 17.04 21.3333 18.3333C21.3333 19.6267 20.1333 20.6667 18.6667 20.6667H12V16Z" stroke="#0F0F0E" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                <path class="dark:stroke-[#A8ABB4]" d="M15.7334 20.6667V23" stroke="#0F0F0E" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                <path class="dark:stroke-[#A8ABB4]" d="M15.7334 9V11.3333" stroke="#0F0F0E" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <span class="text-center mx-auto text-sm md:text-base">ارز ديجيتال</span>
                    </a>
                @endfor

                <a href="#" class="flex gap-[10px] dark:text-[#FCFCFC] bg-[#FFFFFF] dark:bg-[#0C0D0F] dark:border-[#191B21] border border-[#F4F4F4] rounded-xl p-2 md:px-6 md:py-4 w-full items-center hover:border-[#20D05C] dark:hover:border-[#FFC700] transition-colors">
                    <div class="rounded-full p-[1px] w-[33px] h-[33px]">
                        <svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="dark:stroke-[#FFC700]" d="M29.8333 11.36V5.30669C29.8333 3.42669 28.98 2.66669 26.86 2.66669H21.4733C19.3533 2.66669 18.5 3.42669 18.5 5.30669V11.3467C18.5 13.24 19.3533 13.9867 21.4733 13.9867H26.86C28.98 14 29.8333 13.24 29.8333 11.36Z" stroke="#20D05C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path class="dark:stroke-[#FFC700]" d="M29.8333 26.36V20.9733C29.8333 18.8533 28.98 18 26.86 18H21.4733C19.3533 18 18.5 18.8533 18.5 20.9733V26.36C18.5 28.48 19.3533 29.3333 21.4733 29.3333H26.86C28.98 29.3333 29.8333 28.48 29.8333 26.36Z" stroke="#20D05C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path class="dark:stroke-[#FFC700]" d="M14.4998 11.36V5.30669C14.4998 3.42669 13.6465 2.66669 11.5265 2.66669H6.13984C4.01984 2.66669 3.1665 3.42669 3.1665 5.30669V11.3467C3.1665 13.24 4.01984 13.9867 6.13984 13.9867H11.5265C13.6465 14 14.4998 13.24 14.4998 11.36Z" stroke="#20D05C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path class="dark:stroke-[#FFC700]" d="M14.4998 26.36V20.9733C14.4998 18.8533 13.6465 18 11.5265 18H6.13984C4.01984 18 3.1665 18.8533 3.1665 20.9733V26.36C3.1665 28.48 4.01984 29.3333 6.13984 29.3333H11.5265C13.6465 29.3333 14.4998 28.48 14.4998 26.36Z" stroke="#20D05C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <span class="text-center mx-auto text-[#20D05C] dark:text-[#FFC700] text-xs md:text-base">بیشتر</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Filters and Questions Section -->
    <section class="w-full mx-auto max-w-[1500px]">
        <div class="px-5 mt-16 flex flex-col md:flex-row gap-5 w-full md:items-center">
            <span class="text-[#081533] text-xl dark:text-[#FCFCFC]">فیلتر ها</span>
            <div class="flex gap-4 flex-wrap">
                <div class="flex gap-4">
                    <div class="relative inline-block w-max">
                        <div class="relative w-max min-w-[200px]">
                            <button id="dropdownButton" class="w-full bg-[#ffffff] dark:bg-[#0C0D0F] border border-[#F4F4F4] dark:border-[#33353B] text-[#262626] dark:text-[#84858F] rounded-xl py-2 pl-4 pr-8 text-xs md:text-base flex justify-between items-center">
                                انتخاب گزینه‌ها
                                <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-xs md:text-base bg-[#F4F4F480] dark:bg-[#34343480] w-max rounded-xl px-4 py-2 flex items-center gap-3">
                    <span class="text-[#5A5F66] dark:text-[#6A6B74]">حل نشده</span>
                    <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path class="dark:stroke-[#6A6B74]" d="M11.7464 10.5L16.6346 5.61179C16.8666 5.38021 16.9971 5.06595 16.9974 4.73815C16.9977 4.41035 16.8677 4.09586 16.6361 3.86387C16.4045 3.63188 16.0903 3.50138 15.7625 3.50109C15.4347 3.5008 15.1202 3.63074 14.8882 3.86233L10 8.75054L5.11179 3.86233C4.8798 3.63033 4.56515 3.5 4.23706 3.5C3.90897 3.5 3.59432 3.63033 3.36233 3.86233C3.13033 4.09432 3 4.40897 3 4.73706C3 5.06515 3.13033 5.3798 3.36233 5.61179L8.25054 10.5L3.36233 15.3882C3.13033 15.6202 3 15.9349 3 16.2629C3 16.591 3.13033 16.9057 3.36233 17.1377C3.59432 17.3697 3.90897 17.5 4.23706 17.5C4.56515 17.5 4.8798 17.3697 5.11179 17.1377L10 12.2495L14.8882 17.1377C15.1202 17.3697 15.4349 17.5 15.7629 17.5C16.091 17.5 16.4057 17.3697 16.6377 17.1377C16.8697 16.9057 17 16.591 17 16.2629C17 15.9349 16.8697 15.6202 16.6377 15.3882L11.7464 10.5Z" fill="#5A5F66" />
                    </svg>
                </div>

                <div class="flex gap-4">
                    <x-ui.select placeholder="برچسب ها" class="text-xs md:text-base w-max">
                        <option value="tag1">برچسب 1</option>
                        <option value="tag2">برچسب 2</option>
                    </x-ui.select>
                </div>
            </div>
        </div>

        <!-- Questions and Sidebar -->
        <div class="flex flex-col gap-7 md:flex-row mt-5 p-5">
            <!-- Questions List -->
            <div class="w-full md:w-[50%] xl:w-[63%] 2xl:w-[70%] 3xl:w-[73%] flex flex-col gap-7">
                <!-- Sample Questions -->
                <x-content.question-card
                    question="استاندارد متاورس چیست و استاندارد های متاورس را چه کسانی با چه هدفی عنوان خواهند کرد ؟"
                    description="لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است"
                    category="املاک و مستغلات"
                    :isPinned="true"
                    author="نام کاربر"
                    :answersCount="12"
                    :votesCount="32"
                    :viewsCount="3982"
                    createdAt="22 فروردین 1403"
                />

                <x-content.question-card
                    question="مبلغ بیعانه برای اجاره خانه بر چه مبنایی تعیین میشه؟"
                    description="لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است"
                    category="املاک و مستغلات"
                    author="نام کاربر"
                    :answersCount="8"
                    :votesCount="15"
                    :viewsCount="2156"
                    createdAt="21 فروردین 1403"
                />

                <x-content.question-card
                    question="چطور میتونم در بازار ارز دیجیتال سرمایه گذاری کنم؟"
                    description="آیا سرمایه گذاری در ارز دیجیتال مناسب است و چه نکاتی باید رعایت کرد؟"
                    category="ارز دیجیتال"
                    author="علی احمدی"
                    :answersCount="25"
                    :votesCount="67"
                    :viewsCount="8945"
                    createdAt="20 فروردین 1403"
                />

                <!-- Promotional Cards -->
                <div class="flex flex-col lg:flex-row gap-7 items-center">
                    <div class="bg-[#1E52CC] w-full lg:w-1/2 rounded-xl">
                        <div class="flex flex-col gap-5 p-7">
                            <p class="text-white text-xl">1,432 فعال انجمن متاورس رو میشناسی؟</p>
                            <span class="text-[#DFE2EB] text-xs">
                                لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است
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
                                لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است
                            </span>
                            <a href="#" class="dark:bg-black bg-white rounded-xl px-5 py-3 w-max">
                                <span class="text-[#20D05C] text-sm">فعالان انجمن</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="w-full md:w-[46%] xl:w-[35%] 2xl:w-[27.5%] 3xl:w-[25%] flex flex-col gap-7">
                <!-- Statistics -->
                <div class="w-full overflow-hidden border border-[#F4F4F4] dark:border-0 h-min rounded-xl">
                    <div class="flex gap-3 items-center px-6 py-4 bg-[#FFFFFF] dark:bg-[#0C0D0F] w-full">
                        <span class="text-[#173E99] dark:text-[#5A5F66]">کل سوالات پرسیده شده :</span>
                        <span class="dark:text-[#DFE2EB] text-[#5A5F66]">5264</span>
                    </div>
                    <div class="flex gap-3 items-center px-6 py-4 bg-[#E9F0FF66] dark:bg-[#191B21B2] w-full">
                        <span class="text-[#173E99] dark:text-[#5A5F66]">کل سوالات حل شده :</span>
                        <span class="dark:text-[#DFE2EB] text-[#5A5F66]">4892</span>
                    </div>
                    <div class="flex gap-3 items-center px-6 py-4 bg-[#FFFFFF] dark:bg-[#0C0D0F] w-full">
                        <span class="text-[#173E99] dark:text-[#5A5F66]">کل پاسخ ارسال شده :</span>
                        <span class="dark:text-[#DFE2EB] text-[#5A5F66]">12358</span>
                    </div>
                    <div class="flex gap-3 items-center px-6 py-4 bg-[#E9F0FF66] dark:bg-[#191B21B2] w-full">
                        <span class="text-[#173E99] dark:text-[#5A5F66]">کل اعضای انجمن :</span>
                        <span class="dark:text-[#DFE2EB] text-[#5A5F66]">2847</span>
                    </div>
                </div>

                <!-- Suggested Content -->
                <div class="w-full max-w-full dark:text-[#FCFCFC] rounded-xl border border-[#F4F4F4] dark:border-0 h-min p-3 bg-white dark:bg-[#0C0D0F] dark:border-[#33353B] flex flex-col gap-7 max-h-[511px] overflow-y-auto !overflow-x-hidden min-h-[300px]">
                    <div class="text-center">
                        <span class="text-[#173E99] dark:text-[#FCFCFC] text-center">مطالب پیشنهادی</span>
                    </div>

                    @for($i = 0; $i < 5; $i++)
                        <div class="w-full">
                            <div class="flex gap-3 overflow-x-hidden h-max w-full">
                                <div class="w-[25%]">
                                    <img src="{{ asset('assets/images/profile.png') }}" alt="img" class="rounded-lg w-full aspect-square">
                                </div>
                                <div class="flex flex-col justify-between items-center w-[75%] py-2">
                                    <div class="flex truncate items-center w-full text-xs">
                                        <a href="#" class="text-[#0F0F0E] dark:text-[#FCFCFC] truncate text-sm">مبلغ بیعانه برای اجاره خانه بر چه مبنایی تعیین میشه؟</a>
                                    </div>
                                    <div class="flex truncate items-center w-full text-xs">
                                        <a href="#" class="truncate border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max text-[#A8ABB4] dark:text-[#84858F] px-2" title="رشته های ورزشی">رشته های ورزشی</a>
                                        <a href="#" class="truncate border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max text-[#A8ABB4] dark:text-[#84858F] px-2" title="مهدی قربان زاده">مهدی قربان زاده</a>
                                        <a href="#" class="truncate border-l border-[#ECEEF3] dark:border-[#191B21] min-w-max text-[#A8ABB4] dark:text-[#84858F] px-2" title="13 اردیبهشت 1402">13 اردیبهشت 1402</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Load More Button -->
        <div class="mt-10 w-full flex justify-center lg:hidden">
            <a href="#" class="bg-[#E9F0FF] dark:bg-black w-max rounded-lg text-[#2667FF] dark:text-[#FFC700] px-5 py-3 border border-[#2667FF] dark:border-[#FFC700]">
                <span>مشاهده بیشتر</span>
            </a>
        </div>
    </section>

    <!-- Active Members Section -->
    <section class="w-full mx-auto max-w-[1500px] mt-20 px-5">
        <div class="flex justify-between items-center w-full">
          <div class="flex flex-col gap-4">
            <p class="text-xl text-[#081533] dark:text-[#FCFCFC]">فعالان انجمن</p>
            <p class="text-[#5A5F66] dark:text-[#5A5F66] ">لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت
              چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است
            </p>
          </div>
          <div class="hidden lg:block">
            <a href="#"
              class="bg-[#E9F0FF] dark:bg-black w-max rounded-lg text-[#2667FF] dark:text-[#FFC700]  px-5 py-3 border border-[#2667FF] dark:border-[#FFC700]">
              <span>مشاهده بیشتر</span>
            </a>
          </div>
        </div>
        <div class="mt-20 grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-[30px] gap-y-10">
          <div class="w-full rounded-xl bg-white dark:bg-[#0C0D0F] flex flex-col gap-5 p-1  ">
            <div class="w-full flex justify-center mt-[-35px]">
              <img src="{{ asset('assets/images/fasetest.jpg') }}" alt="profile"
                class="aspect-square w-[64px] 3xl:w-[100px] border-[3px] border-[#E9F0FF] dark:border-[#6A6B74] rounded-full ">
            </div>
            <div class=" w-full flex flex-col justify-center items-center gap-4">
              <div class="flex flex-col items-center justify-center gap-[9px] py-5 dark:border-[#A0A0AB] border-b w-full">
                <span class="text-[#0F0F0E] md:text-xl dark:text-gray-200">
                  نام کاربر
                </span>
                <span
                  class="dark:text-[#A0A0AB] text-[10px] md:text-base text-[#5A5F66] border rounded-full border-[#ECEEF3] dark:border-[#A0A0AB] px-[10px] py-1 bg-white dark:bg-black">560
                  امتیاز</span>
                <div class="flex items-center gap-2 text-[#5A5F66] dark:text-[#A0A0AB] text-[10px] md:text-base">

                  <div class="flex items-center gap-1 text-[10px] ">
                    <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M1.79004 3.07996H8.71005C9.54005 3.07996 10.2101 3.74996 10.2101 4.57996V6.23996"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M3.37004 1.5L1.79004 3.07999L3.37004 4.66" stroke="#5A5F66"
                        stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M10.2101 9.92001H3.29004C2.46004 9.92001 1.79004 9.25001 1.79004 8.42001V6.76001"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M8.62988 11.5L10.2099 9.91998L8.62988 8.33997"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                    <span>
                      پاسخ داده شده :
                    </span>
                  </div>
                  <span>1545</span>
                </div>
                <div class="flex items-center gap-2 text-[#5A5F66] dark:text-[#A0A0AB] text-[10px] md:text-base">

                  <div class="flex items-center gap-1 ">

                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M12.4868 7.69334V10.36C12.4868 10.5333 12.4802 10.7 12.4602 10.86C12.3068 12.66 11.2468 13.5533 9.2935 13.5533H9.02684C8.86017 13.5533 8.70016 13.6333 8.60016 13.7667L7.80017 14.8333C7.44684 15.3067 6.8735 15.3067 6.52016 14.8333L5.72015 13.7667C5.63349 13.6533 5.44016 13.5533 5.2935 13.5533H5.02684C2.90017 13.5533 1.8335 13.0267 1.8335 10.36V7.69334C1.8335 5.74001 2.7335 4.68001 4.52684 4.52667C4.68684 4.50667 4.8535 4.5 5.02684 4.5H9.2935C11.4202 4.5 12.4868 5.56667 12.4868 7.69334Z"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M15.1535 5.02671V7.69338C15.1535 9.65338 14.2535 10.7067 12.4602 10.86C12.4802 10.7 12.4869 10.5334 12.4869 10.36V7.69338C12.4869 5.56671 11.4202 4.50004 9.29352 4.50004H5.02686C4.85352 4.50004 4.68686 4.50671 4.52686 4.52671C4.68019 2.73338 5.74019 1.83337 7.69352 1.83337H11.9602C14.0869 1.83337 15.1535 2.90005 15.1535 5.02671Z"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M9.49684 9.33333H9.50284" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M7.16383 9.33333H7.16983" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M4.83033 9.33333H4.83633" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>
                      نظرات :
                    </span>
                  </div>
                  <span>1545</span>
                </div>
              </div>
              <div class="w-full p-2">
                <a href="#"
                  class="bg-[#E9F0FF] dark:bg-black rounded-lg w-full text-[#2667FF] dark:text-[#FFC700]  flex items-center justify-between px-4 py-3 text-[12px] md:text-base">
                  <span>گفتوگو</span>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class=" dark:stroke-[#FFC700] " d="M8.5 10.5H15.5" stroke="#2667FF" stroke-width="1.5"
                      stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path class=" dark:stroke-[#FFC700] "
                      d="M7 18.4299H11L15.45 21.39C16.11 21.83 17 21.3599 17 20.5599V18.4299C20 18.4299 22 16.4299 22 13.4299V7.42993C22 4.42993 20 2.42993 17 2.42993H7C4 2.42993 2 4.42993 2 7.42993V13.4299C2 16.4299 4 18.4299 7 18.4299Z"
                      stroke="#2667FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>

                </a>
              </div>
            </div>
          </div>
          <div class="w-full rounded-xl bg-white dark:bg-[#0C0D0F] flex flex-col gap-5 p-1  ">
            <div class="w-full flex justify-center mt-[-35px]">
              <img src="{{ asset('assets/images/fasetest.jpg') }}" alt="profile"
                class="aspect-square w-[64px] 3xl:w-[100px] border-[3px] border-[#E9F0FF] dark:border-[#6A6B74] rounded-full ">
            </div>
            <div class=" w-full flex flex-col justify-center items-center gap-4">
              <div class="flex flex-col items-center justify-center gap-[9px] py-5 dark:border-[#A0A0AB] border-b w-full">
                <span class="text-[#0F0F0E] md:text-xl dark:text-gray-200">
                  نام کاربر
                </span>
                <span
                  class="dark:text-[#A0A0AB] text-[10px] md:text-base text-[#5A5F66] border rounded-full border-[#ECEEF3] dark:border-[#A0A0AB] px-[10px] py-1 bg-white dark:bg-black">560
                  امتیاز</span>
                <div class="flex items-center gap-2 text-[#5A5F66] dark:text-[#A0A0AB] text-[10px] md:text-base">

                  <div class="flex items-center gap-1 text-[10px] ">
                    <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M1.79004 3.07996H8.71005C9.54005 3.07996 10.2101 3.74996 10.2101 4.57996V6.23996"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M3.37004 1.5L1.79004 3.07999L3.37004 4.66" stroke="#5A5F66"
                        stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M10.2101 9.92001H3.29004C2.46004 9.92001 1.79004 9.25001 1.79004 8.42001V6.76001"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M8.62988 11.5L10.2099 9.91998L8.62988 8.33997"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                    <span>
                      پاسخ داده شده :
                    </span>
                  </div>
                  <span>1545</span>
                </div>
                <div class="flex items-center gap-2 text-[#5A5F66] dark:text-[#A0A0AB] text-[10px] md:text-base">

                  <div class="flex items-center gap-1 ">

                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M12.4868 7.69334V10.36C12.4868 10.5333 12.4802 10.7 12.4602 10.86C12.3068 12.66 11.2468 13.5533 9.2935 13.5533H9.02684C8.86017 13.5533 8.70016 13.6333 8.60016 13.7667L7.80017 14.8333C7.44684 15.3067 6.8735 15.3067 6.52016 14.8333L5.72015 13.7667C5.63349 13.6533 5.44016 13.5533 5.2935 13.5533H5.02684C2.90017 13.5533 1.8335 13.0267 1.8335 10.36V7.69334C1.8335 5.74001 2.7335 4.68001 4.52684 4.52667C4.68684 4.50667 4.8535 4.5 5.02684 4.5H9.2935C11.4202 4.5 12.4868 5.56667 12.4868 7.69334Z"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M15.1535 5.02671V7.69338C15.1535 9.65338 14.2535 10.7067 12.4602 10.86C12.4802 10.7 12.4869 10.5334 12.4869 10.36V7.69338C12.4869 5.56671 11.4202 4.50004 9.29352 4.50004H5.02686C4.85352 4.50004 4.68686 4.50671 4.52686 4.52671C4.68019 2.73338 5.74019 1.83337 7.69352 1.83337H11.9602C14.0869 1.83337 15.1535 2.90005 15.1535 5.02671Z"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M9.49684 9.33333H9.50284" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M7.16383 9.33333H7.16983" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M4.83033 9.33333H4.83633" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>
                      نظرات :
                    </span>
                  </div>
                  <span>1545</span>
                </div>
              </div>
              <div class="w-full p-2">
                <a href="#"
                  class="bg-[#E9F0FF] dark:bg-black rounded-lg w-full text-[#2667FF] dark:text-[#FFC700]  flex items-center justify-between px-4 py-3 text-[12px] md:text-base">
                  <span>گفتوگو</span>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class=" dark:stroke-[#FFC700] " d="M8.5 10.5H15.5" stroke="#2667FF" stroke-width="1.5"
                      stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path class=" dark:stroke-[#FFC700] "
                      d="M7 18.4299H11L15.45 21.39C16.11 21.83 17 21.3599 17 20.5599V18.4299C20 18.4299 22 16.4299 22 13.4299V7.42993C22 4.42993 20 2.42993 17 2.42993H7C4 2.42993 2 4.42993 2 7.42993V13.4299C2 16.4299 4 18.4299 7 18.4299Z"
                      stroke="#2667FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>

                </a>
              </div>
            </div>
          </div>
          <div class="w-full rounded-xl bg-white dark:bg-[#0C0D0F] flex flex-col gap-5 p-1  ">
            <div class="w-full flex justify-center mt-[-35px]">
              <img src="{{ asset('assets/images/fasetest.jpg') }}" alt="profile"
                class="aspect-square w-[64px] 3xl:w-[100px] border-[3px] border-[#E9F0FF] dark:border-[#6A6B74] rounded-full ">
            </div>
            <div class=" w-full flex flex-col justify-center items-center gap-4">
              <div class="flex flex-col items-center justify-center gap-[9px] py-5 dark:border-[#A0A0AB] border-b w-full">
                <span class="text-[#0F0F0E] md:text-xl dark:text-gray-200">
                  نام کاربر
                </span>
                <span
                  class="dark:text-[#A0A0AB] text-[10px] md:text-base text-[#5A5F66] border rounded-full border-[#ECEEF3] dark:border-[#A0A0AB] px-[10px] py-1 bg-white dark:bg-black">560
                  امتیاز</span>
                <div class="flex items-center gap-2 text-[#5A5F66] dark:text-[#A0A0AB] text-[10px] md:text-base">

                  <div class="flex items-center gap-1 text-[10px] ">
                    <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M1.79004 3.07996H8.71005C9.54005 3.07996 10.2101 3.74996 10.2101 4.57996V6.23996"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M3.37004 1.5L1.79004 3.07999L3.37004 4.66" stroke="#5A5F66"
                        stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M10.2101 9.92001H3.29004C2.46004 9.92001 1.79004 9.25001 1.79004 8.42001V6.76001"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M8.62988 11.5L10.2099 9.91998L8.62988 8.33997"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                    <span>
                      پاسخ داده شده :
                    </span>
                  </div>
                  <span>1545</span>
                </div>
                <div class="flex items-center gap-2 text-[#5A5F66] dark:text-[#A0A0AB] text-[10px] md:text-base">

                  <div class="flex items-center gap-1 ">

                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M12.4868 7.69334V10.36C12.4868 10.5333 12.4802 10.7 12.4602 10.86C12.3068 12.66 11.2468 13.5533 9.2935 13.5533H9.02684C8.86017 13.5533 8.70016 13.6333 8.60016 13.7667L7.80017 14.8333C7.44684 15.3067 6.8735 15.3067 6.52016 14.8333L5.72015 13.7667C5.63349 13.6533 5.44016 13.5533 5.2935 13.5533H5.02684C2.90017 13.5533 1.8335 13.0267 1.8335 10.36V7.69334C1.8335 5.74001 2.7335 4.68001 4.52684 4.52667C4.68684 4.50667 4.8535 4.5 5.02684 4.5H9.2935C11.4202 4.5 12.4868 5.56667 12.4868 7.69334Z"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M15.1535 5.02671V7.69338C15.1535 9.65338 14.2535 10.7067 12.4602 10.86C12.4802 10.7 12.4869 10.5334 12.4869 10.36V7.69338C12.4869 5.56671 11.4202 4.50004 9.29352 4.50004H5.02686C4.85352 4.50004 4.68686 4.50671 4.52686 4.52671C4.68019 2.73338 5.74019 1.83337 7.69352 1.83337H11.9602C14.0869 1.83337 15.1535 2.90005 15.1535 5.02671Z"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M9.49684 9.33333H9.50284" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M7.16383 9.33333H7.16983" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M4.83033 9.33333H4.83633" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>
                      نظرات :
                    </span>
                  </div>
                  <span>1545</span>
                </div>
              </div>
              <div class="w-full p-2">
                <a href="#"
                  class="bg-[#E9F0FF] dark:bg-black rounded-lg w-full text-[#2667FF] dark:text-[#FFC700]  flex items-center justify-between px-4 py-3 text-[12px] md:text-base">
                  <span>گفتوگو</span>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class=" dark:stroke-[#FFC700] " d="M8.5 10.5H15.5" stroke="#2667FF" stroke-width="1.5"
                      stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path class=" dark:stroke-[#FFC700] "
                      d="M7 18.4299H11L15.45 21.39C16.11 21.83 17 21.3599 17 20.5599V18.4299C20 18.4299 22 16.4299 22 13.4299V7.42993C22 4.42993 20 2.42993 17 2.42993H7C4 2.42993 2 4.42993 2 7.42993V13.4299C2 16.4299 4 18.4299 7 18.4299Z"
                      stroke="#2667FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>

                </a>
              </div>
            </div>
          </div>
          <div class="w-full rounded-xl bg-white dark:bg-[#0C0D0F] flex flex-col gap-5 p-1  ">
            <div class="w-full flex justify-center mt-[-35px]">
              <img src="{{ asset('assets/images/fasetest.jpg') }}" alt="profile"
                class="aspect-square w-[64px] 3xl:w-[100px] border-[3px] border-[#E9F0FF] dark:border-[#6A6B74] rounded-full ">
            </div>
            <div class=" w-full flex flex-col justify-center items-center gap-4">
              <div class="flex flex-col items-center justify-center gap-[9px] py-5 dark:border-[#A0A0AB] border-b w-full">
                <span class="text-[#0F0F0E] md:text-xl dark:text-gray-200">
                  نام کاربر
                </span>
                <span
                  class="dark:text-[#A0A0AB] text-[10px] md:text-base text-[#5A5F66] border rounded-full border-[#ECEEF3] dark:border-[#A0A0AB] px-[10px] py-1 bg-white dark:bg-black">560
                  امتیاز</span>
                <div class="flex items-center gap-2 text-[#5A5F66] dark:text-[#A0A0AB] text-[10px] md:text-base">

                  <div class="flex items-center gap-1 text-[10px] ">
                    <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M1.79004 3.07996H8.71005C9.54005 3.07996 10.2101 3.74996 10.2101 4.57996V6.23996"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M3.37004 1.5L1.79004 3.07999L3.37004 4.66" stroke="#5A5F66"
                        stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M10.2101 9.92001H3.29004C2.46004 9.92001 1.79004 9.25001 1.79004 8.42001V6.76001"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M8.62988 11.5L10.2099 9.91998L8.62988 8.33997"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                    <span>
                      پاسخ داده شده :
                    </span>
                  </div>
                  <span>1545</span>
                </div>
                <div class="flex items-center gap-2 text-[#5A5F66] dark:text-[#A0A0AB] text-[10px] md:text-base">

                  <div class="flex items-center gap-1 ">

                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M12.4868 7.69334V10.36C12.4868 10.5333 12.4802 10.7 12.4602 10.86C12.3068 12.66 11.2468 13.5533 9.2935 13.5533H9.02684C8.86017 13.5533 8.70016 13.6333 8.60016 13.7667L7.80017 14.8333C7.44684 15.3067 6.8735 15.3067 6.52016 14.8333L5.72015 13.7667C5.63349 13.6533 5.44016 13.5533 5.2935 13.5533H5.02684C2.90017 13.5533 1.8335 13.0267 1.8335 10.36V7.69334C1.8335 5.74001 2.7335 4.68001 4.52684 4.52667C4.68684 4.50667 4.8535 4.5 5.02684 4.5H9.2935C11.4202 4.5 12.4868 5.56667 12.4868 7.69334Z"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] "
                        d="M15.1535 5.02671V7.69338C15.1535 9.65338 14.2535 10.7067 12.4602 10.86C12.4802 10.7 12.4869 10.5334 12.4869 10.36V7.69338C12.4869 5.56671 11.4202 4.50004 9.29352 4.50004H5.02686C4.85352 4.50004 4.68686 4.50671 4.52686 4.52671C4.68019 2.73338 5.74019 1.83337 7.69352 1.83337H11.9602C14.0869 1.83337 15.1535 2.90005 15.1535 5.02671Z"
                        stroke="#5A5F66" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M9.49684 9.33333H9.50284" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M7.16383 9.33333H7.16983" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                      <path class=" dark:stroke-[#A0A0AB] " d="M4.83033 9.33333H4.83633" stroke="#5A5F66" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>
                      نظرات :
                    </span>
                  </div>
                  <span>1545</span>
                </div>
              </div>
              <div class="w-full p-2">
                <a href="#"
                  class="bg-[#E9F0FF] dark:bg-black rounded-lg w-full text-[#2667FF] dark:text-[#FFC700]  flex items-center justify-between px-4 py-3 text-[12px] md:text-base">
                  <span>گفتوگو</span>
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class=" dark:stroke-[#FFC700] " d="M8.5 10.5H15.5" stroke="#2667FF" stroke-width="1.5"
                      stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path class=" dark:stroke-[#FFC700] "
                      d="M7 18.4299H11L15.45 21.39C16.11 21.83 17 21.3599 17 20.5599V18.4299C20 18.4299 22 16.4299 22 13.4299V7.42993C22 4.42993 20 2.42993 17 2.42993H7C4 2.42993 2 4.42993 2 7.42993V13.4299C2 16.4299 4 18.4299 7 18.4299Z"
                      stroke="#2667FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-10 w-full flex justify-center lg:hidden">
          <a href="#"
            class="bg-[#E9F0FF] dark:bg-black w-max rounded-lg text-[#2667FF] dark:text-[#FFC700]  px-5 py-3 border border-[#2667FF] dark:border-[#FFC700]">
            <span>مشاهده بیشتر</span>
          </a>
        </div>
      </section>

    <x-slot name="scripts">
        <script>
            // Initialize CKEditor
            ClassicEditor
                .create(document.querySelector('#editor'))
                .catch(error => {
                    console.error(error);
                });

            // Initialize Select2
            $('.my-select').select2({
                placeholder: 'انتخاب برچسب ها',
                allowClear: true
            });

            // Modal functionality
            document.getElementById('openModalBtn').addEventListener('click', function() {
                openModal('questionModal');
            });

            // Mobile modal button
            document.querySelector('.lg\\:hidden button').addEventListener('click', function() {
                openModal('questionModal');
            });
        </script>
    </x-slot>
</x-layouts.layout>

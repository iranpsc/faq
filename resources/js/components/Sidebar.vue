<template>
    <aside
        class="sidebar-container fixed right-0 top-0 h-full bg-white dark:bg-gray-800 shadow-lg z-50 transform transition-all duration-300 ease-in-out flex flex-col lg:translate-x-0"
        :class="{ 'w-80': isOpen, 'w-16': !isOpen, 'translate-x-0': isOpen, 'translate-x-full lg:translate-x-0': !isOpen }">
        <!-- Toggle Button (when collapsed) -->
        <div v-if="!isOpen" class="flex justify-center border-b border-gray-200 dark:border-gray-700 flex-shrink-0 p-2">
            <button @click="$emit('toggle')"
                class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 flex-shrink-0 rotate-180">
                <svg class="rotate-180 w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Fixed Header -->
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 flex-shrink-0"
            :class="{ 'p-4': isOpen, 'p-2': !isOpen }">
            <div class="flex items-center gap-3" :class="{ 'flex-1': isOpen }">
                <a href="/">
                    <div
                        class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center flex-shrink-0">
                        <div
                            class="w-6 h-6 bg-green-500 dark:bg-green-600 rounded-full flex items-center justify-center">
                            <img :src="logoUrl" alt="انجمن حم" class="w-full h-full object-contain rounded-full">
                        </div>
                    </div>
                </a>
                <a href="/" class="text-right transition-all duration-300"
                    :class="{ 'opacity-100 flex-1': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">انجمن حم</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">پرسش و پاسخ عمومی</p>
                </a>
            </div>
            <!-- Toggle Button (when expanded) -->
            <button v-if="isOpen" @click="$emit('toggle')"
                class="p-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-full transition-all duration-300 flex-shrink-0">
                <svg class="rotate-180  w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Fixed User Profile Section -->
        <div class="border-b border-gray-200 dark:border-gray-700 flex-shrink-0"
            :class="{ 'p-4': isOpen, 'p-2': !isOpen }">
            <div v-if="isAuthenticated && user" class="flex items-center gap-3"
                :class="{ 'mb-4': isOpen, 'mb-0': !isOpen }">

                <!-- User Profile with Collapsible Menu (when expanded) -->
                <div v-if="isOpen" class="flex-1">
                    <button
                        @click="toggleUserDropdown"
                        class="flex items-center gap-3 w-full p-2 rounded-lg transition-colors outline-none focus:outline-none focus:ring-0 focus:border-0"
                    >
                        <BaseAvatar :src="user.image_url" :name="user.name" size="lg"
                            :status="user.online ? 'online' : 'offline'" />
                        <div class="text-right flex-1">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ user.name }}</h4>
                            <p v-if="user.score !== undefined" class="text-xs text-gray-500 dark:text-gray-400">
                                امتیاز:
                                <BaseBadge variant="primary" size="xs" class="mr-1">{{ user.score }}</BaseBadge>
                            </p>
                        </div>
                        <svg
                            class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': userDropdownOpen }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Collapsible Menu Content -->
                    <div
                        class="overflow-hidden transition-all duration-300 ease-in-out"
                        :style="{ maxHeight: userDropdownOpen ? '200px' : '0px' }"
                    >
                        <div class="mt-2 ml-4 space-y-1">
                            <router-link
                                to="/profile"
                                @click="closeUserDropdown"
                                class="dropdown-item flex items-center gap-3 px-4 py-2 text-sm rounded-lg transition-colors"
                                :class="{
                                    'text-gray-700 hover:bg-gray-100': theme === 'light',
                                    'text-gray-300 hover:bg-gray-700': theme === 'dark'
                                }"
                                role="menuitem"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                پروفایل
                            </router-link>
                        </div>
                    </div>
                </div>

                <!-- Collapsed view - just avatar -->
                <div v-else class="flex items-center justify-center">
                    <BaseAvatar :src="user.image_url" :name="user.name" size="lg"
                        :status="user.online ? 'online' : 'offline'" />
                </div>
            </div>

            <!-- Guest User Section -->
            <div v-else class="flex items-center gap-3" :class="{ 'mb-4': isOpen, 'mb-0': !isOpen }">
                <BaseAvatar size="lg" variant="secondary" />
                <div class="text-right flex-1 transition-all duration-300"
                    :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">کاربر مهمان</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">برای ورود کلیک کنید</p>
                </div>
            </div>
        </div>

        <!-- Scrollable Menu Items -->
        <div class="flex-1 overflow-y-auto scrollable-menu">
            <nav :class="{ 'p-4': isOpen, 'p-2': !isOpen }">
                <ul class="space-y-2">
                    <!-- Categories -->
                    <li>
                        <router-link to="/categories" class="flex items-center rounded-lg transition-colors"
                            :class="{ 'gap-3 p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': isOpen, 'p-2 justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !isOpen }">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="text-sm transition-all duration-300 whitespace-nowrap"
                                :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">دسته بندی ها</span>
                        </router-link>
                    </li>

                    <!-- Daily Activity -->
                    <li>
                        <router-link to="/activities" class="flex items-center rounded-lg transition-colors"
                            :class="{ 'gap-3 p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': isOpen, 'p-2 justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !isOpen }">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm transition-all duration-300 whitespace-nowrap"
                                :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">فعالیت
                                روز</span>
                        </router-link>
                    </li>

                    <!-- Active Members / Authors -->
                    <li>
                        <router-link to="/authors" class="flex items-center rounded-lg transition-colors"
                            :class="{ 'gap-3 p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': isOpen, 'p-2 justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !isOpen }">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="text-sm transition-all duration-300 whitespace-nowrap"
                                :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">فعالان انجمن</span>
                        </router-link>
                    </li>

                    <!-- News Metaverse -->
                    <li>
                        <a href="#" class="flex items-center rounded-lg transition-colors"
                            :class="{ 'gap-3 p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': isOpen, 'p-2 justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !isOpen }">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                                </path>
                            </svg>
                            <span class="text-sm transition-all duration-300 whitespace-nowrap"
                                :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">اخبار
                                متاورس</span>
                        </a>
                    </li>

                    <!-- Metaverse Association -->
                    <li>
                        <a href="#" class="flex items-center rounded-lg transition-colors"
                            :class="{ 'gap-3 p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': isOpen, 'p-2 justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !isOpen }">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="text-sm transition-all duration-300 whitespace-nowrap"
                                :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">انجمن
                                متاورس</span>
                        </a>
                    </li>

                    <!-- About Us -->
                    <li>
                        <a href="#" class="flex items-center rounded-lg transition-colors"
                            :class="{ 'gap-3 p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': isOpen, 'p-2 justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !isOpen }">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm transition-all duration-300 whitespace-nowrap"
                                :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">درباره
                                ما</span>
                        </a>
                    </li>

                    <!-- Contact Us -->
                    <li>
                        <a href="#" class="flex items-center rounded-lg transition-colors"
                            :class="{ 'gap-3 p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': isOpen, 'p-2 justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !isOpen }">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span class="text-sm transition-all duration-300 whitespace-nowrap"
                                :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">ارتباط با
                                ما</span>
                        </a>
                    </li>

                    <!-- Language -->
                    <li>
                        <a href="#" class="flex items-center rounded-lg transition-colors"
                            :class="{ 'gap-3 p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': isOpen, 'p-2 justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !isOpen }">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                            <span class="text-sm transition-all duration-300 whitespace-nowrap"
                                :class="{ 'opacity-100': isOpen, 'opacity-0 w-0 overflow-hidden': !isOpen }">زبان</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Fixed Footer Actions -->
        <div class="border-t border-gray-200 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800"
            :class="{ 'p-4': isOpen, 'p-2': !isOpen }">
            <!-- Login Button (only for guests) -->
            <BaseButton v-if="!isAuthenticated" @click="handleLogin" variant="primary" size="lg" block
                :class="{ 'mb-4': isOpen, 'mb-2': !isOpen }">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                        </path>
                    </svg>
                </template>
                <span v-if="isOpen">ورود</span>
            </BaseButton>

            <!-- Logout Button (only for authenticated users) -->
            <BaseButton v-if="isAuthenticated" @click="handleLogout" variant="danger" size="lg" block
                :class="{ 'mb-4': isOpen, 'mb-2': !isOpen }">
                <template #icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                        </path>
                    </svg>
                </template>
                <span v-if="isOpen">خروج</span>
            </BaseButton>

            <!-- Theme Toggle -->
            <div v-if="isOpen" class="flex bg-gray-100 dark:bg-gray-700 rounded-full p-1">
                <BaseButton @click="toggleTheme('light')" :variant="theme === 'light' ? 'primary' : 'ghost'" size="sm"
                    class="flex-1 rounded-full">
                    <template #icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </template>
                </BaseButton>
                <BaseButton @click="toggleTheme('dark')" :variant="theme === 'dark' ? 'primary' : 'ghost'" size="sm"
                    class="flex-1 rounded-full">
                    <template #icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                            </path>
                        </svg>
                    </template>
                </BaseButton>
            </div>

            <!-- Collapsed Actions -->
            <div v-if="!isOpen" class="flex flex-col gap-2">
                <!-- Theme Toggle (Collapsed) - Single Button Cycle -->
                <div class="flex justify-center">
                    <button @click="toggleTheme(theme === 'light' ? 'dark' : 'light')"
                        class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg v-if="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </aside>
</template>

<script>
import { useAuth } from '../composables/useAuth.js'
import { BaseAvatar, BaseBadge, BaseButton } from './ui'

export default {
    name: 'Sidebar',
    components: {
        BaseAvatar,
        BaseBadge,
        BaseButton
    },
    props: {
        isOpen: {
            type: Boolean,
            default: false
        },
        theme: {
            type: String,
            default: 'light'
        }
    },
    emits: ['toggle', 'theme-change'],
    data() {
        return {
            userDropdownOpen: false
        }
    },
    setup() {
        const { user, isAuthenticated, logout, handleLogin, getInitials } = useAuth()

        // Create logo URL
        const logoUrl = '/assets/icon/main-logo.PNG'

        return {
            user,
            isAuthenticated,
            logout,
            handleLogin,
            getInitials,
            logoUrl
        }
    },
    methods: {
        toggleTheme(theme) {
            // Emit theme change to parent component
            this.$emit('theme-change', theme)
        },
        handleLogout() {
            this.logout()
        },
        toggleUserDropdown() {
            this.userDropdownOpen = !this.userDropdownOpen
        },
        closeUserDropdown() {
            this.userDropdownOpen = false
        },
        handleLogoutAndCloseDropdown() {
            this.handleLogout()
            this.closeUserDropdown()
        }
    }
};
</script>

<style scoped>
.sidebar-container {
    direction: rtl;
    font-family: 'Vazirmatn', 'Tahoma', sans-serif;
}

/* Custom scrollbar for the navigation area */
.scrollable-menu::-webkit-scrollbar {
    width: 4px;
}

.scrollable-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.scrollable-menu::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.scrollable-menu::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Dark mode scrollbar */
:global(.dark) .scrollable-menu::-webkit-scrollbar-track {
    background: #374151;
}

:global(.dark) .scrollable-menu::-webkit-scrollbar-thumb {
    background: #6b7280;
}

:global(.dark) .scrollable-menu::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Ensure proper flex layout */
.sidebar-container {
    display: flex;
    flex-direction: column;
}

/* Hover effects */
nav ul li a:hover {
    transform: translateX(-2px);
}

/* Smooth transitions */
nav ul li a,
button {
    transition: all 0.2s ease-in-out;
}

/* Dark mode specific adjustments */
:global(.dark) .sidebar-container {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}

/* Ensure dropdown is properly positioned and doesn't overflow */
.sidebar-container {
    overflow: visible;
}

/* Ensure the user profile section allows dropdown overflow */
.border-b {
    overflow: visible;
}

/* Dropdown specific styles */
.dropdown-item {
    transition: all 0.2s ease-in-out;
}

.dropdown-item:hover {
    transform: translateX(-2px);
}

/* Ensure dropdown is properly positioned */
:deep(.dropdown-item) {
    text-decoration: none;
    color: inherit;
}

:deep(.dropdown-item:hover) {
    text-decoration: none;
}

/* Override router-link styles in dropdown */
:deep(.dropdown-item.router-link-active) {
    background-color: #e5e7eb;
}

:global(.dark) :deep(.dropdown-item.router-link-active) {
    background-color: #374151;
}
</style>

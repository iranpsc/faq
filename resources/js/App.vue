<template>
    <div
        class="app-container bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
        <div :class="{ 'lg:mr-80': sidebarOpen, 'lg:mr-16': !sidebarOpen, 'mr-0': true }"
            class="flex flex-col flex-grow transition-all duration-300">
            <Header :sidebarOpen="sidebarOpen" @toggle-sidebar="toggleSidebar" @main-action="handleMainAction" />
            <router-view @edit-question="handleEditQuestion" />
            <Footer />
        </div>
        <Sidebar :isOpen="sidebarOpen" :theme="theme" @toggle="toggleSidebar" @theme-change="handleThemeChange" />

        <QuestionModal v-if="showQuestionModal" @close="showQuestionModal = false" :question-to-edit="questionToEdit"
            @question-created="handleQuestionCreated" @question-updated="handleQuestionUpdated" />

        <!-- Overlay for mobile/tablet/medium screens -->
        <div v-if="sidebarOpen" @click="closeSidebar" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>
    </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, getCurrentInstance, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import Sidebar from './components/Sidebar.vue';
import MainContent from './pages/Home.vue';
import QuestionModal from './components/QuestionModal.vue';
import { useTheme } from './composables/useTheme.js';
import { useAuth } from './composables/useAuth.js';
import questionService from './services/questionService.js';

export default {
    name: 'App',
    components: {
        Header,
        Footer,
        Sidebar,
        MainContent,
        QuestionModal,
    },
    setup() {
        const { isDark, theme, setTheme, initializeTheme, setupSystemThemeListener } = useTheme();
        const { isAuthenticated, handleLogin } = useAuth();
        const router = useRouter();
        const showQuestionModal = ref(false);
        const questionToEdit = ref(null);
        const app = getCurrentInstance();

        const sidebarOpen = ref(window.innerWidth >= 1024);
        const resizeTimeout = ref(null);
        const cleanupThemeListener = ref(null);

        const handleResize = () => {
            // Debounce resize handler to avoid too many calls
            clearTimeout(resizeTimeout.value);
            resizeTimeout.value = setTimeout(() => {
                // Force sidebar open only on large screens (1024px and above)
                if (window.innerWidth >= 1024) {
                    sidebarOpen.value = true;
                } else {
                    sidebarOpen.value = false;
                }
            }, 150);
        };

        const toggleSidebar = () => {
            sidebarOpen.value = !sidebarOpen.value;
        };

        const closeSidebar = () => {
            sidebarOpen.value = false;
        };

        const handleMainAction = () => {
            if (isAuthenticated.value) {
                questionToEdit.value = null;
                showQuestionModal.value = true;
            } else {
                showAuthenticationDialog();
            }
        };

        const showAuthenticationDialog = () => {
            const swal = app.appContext.config.globalProperties.$swal;

            swal({
                html: `
                    <div class="${isDark.value ? 'text-white' : 'text-gray-800'}">
                        <h2 class="text-xl font-bold mb-2">وارد حساب کاربری شوید</h2>
                        <p>برای ثبت سوال خود وارد حساب کاربری خود شوید و اگر حساب کاربری ندارید، ثبت نام کنید.</p>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: 'ورود',
                cancelButtonText: 'ثبت نام',
                confirmButtonColor: isDark.value ? '#60a5fa' : '#3b82f6',
                cancelButtonColor: isDark.value ? '#34d399' : '#10b981',
                reverseButtons: true,
                focusConfirm: false,
                focusCancel: false,
                allowOutsideClick: true,
                allowEscapeKey: true,
                showLoaderOnConfirm: true,
                backdrop: true,
                background: isDark.value ? '#1f2937' : '#ffffff',
                preConfirm: async () => {
                    try {
                        await handleLogin();
                        return true;
                    } catch (error) {
                        console.error('Login error:', error);
                        swal.showValidationMessage('خطا در ورود. لطفاً دوباره تلاش کنید.');
                        return false;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Login was successful (handled in preConfirm)
                } else if (result.dismiss === swal.DismissReason.cancel) {
                    // User clicked register button - show loading and redirect
                    handleRegisterRedirect();
                }
            });
        };

        const handleRegisterRedirect = async () => {
            const swal = app.appContext.config.globalProperties.$swal;

            // Show loading dialog for register
            swal({
                title: 'در حال انتقال...',
                text: 'لطفاً صبر کنید',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    swal.showLoading();
                }
            });

            try {
                // Small delay to show loading state
                await new Promise(resolve => setTimeout(resolve, 800));
                window.open('https://accounts.irpsc.com/register', '_blank');
                swal.close();
            } catch (error) {
                console.error('Register redirect error:', error);
                swal({
                    title: 'خطا',
                    text: 'خطا در باز کردن صفحه ثبت نام.',
                    icon: 'error',
                    confirmButtonText: 'باشه'
                });
            }
        };

        const handleThemeChange = (theme) => {
            setTheme(theme);
        };

        const handleEditQuestion = (question) => {
            questionToEdit.value = question;
            showQuestionModal.value = true;
        };

        const handleQuestionCreated = async (newQuestion) => {
            showQuestionModal.value = false;

            // Use the question service to notify other components
            questionService.questionCreated(newQuestion);
        };

        const handleQuestionUpdated = async (updatedQuestion) => {
            showQuestionModal.value = false;
            questionToEdit.value = null;

            // Use the question service to notify other components
            questionService.questionUpdated(updatedQuestion);
        };

        onMounted(async () => {
            // Initialize theme system
            initializeTheme();
            cleanupThemeListener.value = setupSystemThemeListener();

            // Set initial sidebar state based on screen size
            handleResize();

            // Add event listener for keyboard shortcuts
            window.addEventListener('keydown', (e) => {
                // Close sidebar on Escape key
                if (e.key === 'Escape' && sidebarOpen.value) {
                    closeSidebar();
                }
            });

            // Add event listener for window resize to handle responsive behavior
            window.removeEventListener('resize', handleResize); // Remove any existing listener first
            window.addEventListener('resize', handleResize);
        });

        onBeforeUnmount(() => {
            // Clean up event listeners
            window.removeEventListener('resize', handleResize);
            if (cleanupThemeListener.value) {
                cleanupThemeListener.value();
            }
        });

        return {
            sidebarOpen,
            theme,
            toggleSidebar,
            closeSidebar,
            handleMainAction,
            showAuthenticationDialog,
            handleRegisterRedirect,
            handleThemeChange,
            showQuestionModal,
            questionToEdit,
            handleEditQuestion,
            handleQuestionCreated,
            handleQuestionUpdated,
        };
    },
};
</script>

<style>
/* Global styles */
* {
    box-sizing: border-box;
}

.app-container {
    min-height: 100vh;
    display: flex;
    direction: rtl;
    font-family: 'Vazirmatn', 'Tahoma', sans-serif;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Smooth transitions */
* {
    transition: all 0.3s ease-in-out;
}

/* Responsive adjustments */
@media (min-width: 1024px) {
    .mr-80 {
        margin-right: 20rem;
        /* 320px */
        transition: all 0.3s ease;
    }

    .mr-16 {
        margin-right: 4rem;
        /* 64px */
        transition: all 0.3s ease;
    }
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Dark mode scrollbar */
.dark ::-webkit-scrollbar-track {
    background: #374151;
}

.dark ::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Focus outline for accessibility */
button:focus,
input:focus,
a:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

.dark button:focus,
.dark input:focus,
.dark a:focus {
    outline: 2px solid #60a5fa;
    outline-offset: 2px;
}

/* Theme transition overlay */
.app-container::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: transparent;
    transition: all 0.3s ease;
    pointer-events: none;
    z-index: -1;
}

/* Selection colors */
::selection {
    background-color: #3b82f6;
    color: white;
}

.dark ::selection {
    background-color: #60a5fa;
    color: #1f2937;
}
</style>

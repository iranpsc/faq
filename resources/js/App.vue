<template>
    <div class="app-container bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
        <div :class="{ 'md:mr-80': sidebarOpen, 'md:mr-16': !sidebarOpen, 'mr-0': true }" class="flex flex-col flex-grow transition-all duration-300">
            <Header
                :sidebarOpen="sidebarOpen"
                @toggle-sidebar="toggleSidebar"
                @main-action="handleMainAction"
            />
            <router-view @edit-question="handleEditQuestion" ref="mainContentRef" />
            <Footer />
        </div>
        <Sidebar
            :isOpen="sidebarOpen"
            :theme="theme"
            @toggle="toggleSidebar"
            @theme-change="handleThemeChange"
        />

        <QuestionModal
            v-if="showQuestionModal"
            @close="showQuestionModal = false"
            :question-to-edit="questionToEdit"
            @question-created="handleQuestionCreated"
            @question-updated="handleQuestionUpdated"
        />

        <!-- Overlay for mobile/tablet -->
        <div
            v-if="sidebarOpen"
            @click="closeSidebar"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
        ></div>
    </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, getCurrentInstance } from 'vue'
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import Sidebar from './components/Sidebar.vue';
import MainContent from './pages/Home.vue';
import QuestionModal from './components/QuestionModal.vue';
import { useTheme } from './composables/useTheme.js';
import { useAuth } from './composables/useAuth.js';

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
        const { isAuthenticated, initializeAuth } = useAuth();
        const showQuestionModal = ref(false);
        const questionToEdit = ref(null);
        const mainContentRef = ref(null);
        const app = getCurrentInstance();

        const sidebarOpen = ref(window.innerWidth >= 768);
        const resizeTimeout = ref(null);
        const cleanupThemeListener = ref(null);

        const handleResize = () => {
            // Debounce resize handler to avoid too many calls
            clearTimeout(resizeTimeout.value);
            resizeTimeout.value = setTimeout(() => {
                // Force sidebar open on medium/large screens
                if (window.innerWidth >= 768) {
                    sidebarOpen.value = true;
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
                app.appContext.config.globalProperties.$swal({
                    title: 'خطا',
                    text: 'برای پرسیدن سوال، ابتدا باید وارد شوید.',
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

        const handleQuestionCreated = (newQuestion) => {
            showQuestionModal.value = false;
            // Add the new question to the top of the list if Home component supports it
            if (mainContentRef.value && mainContentRef.value.prependQuestion) {
                mainContentRef.value.prependQuestion(newQuestion);
            } else if (mainContentRef.value && mainContentRef.value.refreshQuestions) {
                // Fallback to refresh if prepend is not available
                mainContentRef.value.refreshQuestions();
            }
        };

        const handleQuestionUpdated = (updatedQuestion) => {
            showQuestionModal.value = false;
            questionToEdit.value = null;
            // Update the question in the list if Home component supports it
            if (mainContentRef.value && mainContentRef.value.updateQuestion) {
                mainContentRef.value.updateQuestion(updatedQuestion.id, updatedQuestion);
            } else if (mainContentRef.value && mainContentRef.value.refreshQuestions) {
                // Fallback to refresh if update is not available
                mainContentRef.value.refreshQuestions();
            }
        };

        onMounted(async () => {
            // Initialize theme system
            initializeTheme();
            cleanupThemeListener.value = setupSystemThemeListener();

            // Initialize authentication
            await initializeAuth();

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
            handleThemeChange,
            showQuestionModal,
            questionToEdit,
            mainContentRef,
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
@media (min-width: 768px) {
    .mr-80 {
        margin-right: 20rem; /* 320px */
        transition: all 0.3s ease;
    }
    .mr-16 {
        margin-right: 4rem; /* 64px */
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

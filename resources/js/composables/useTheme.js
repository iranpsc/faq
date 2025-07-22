import { ref, onMounted } from 'vue';

// Global reactive state to ensure all components share the same theme state
const isDark = ref(false);

export function useTheme() {
  const updateTheme = (isDarkValue) => {
    isDark.value = isDarkValue;
    if (isDarkValue) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  };

  const initializeTheme = () => {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const shouldBeDark = savedTheme === 'dark' || (!savedTheme && systemPrefersDark);
    updateTheme(shouldBeDark);
  };

  const toggleTheme = () => {
    updateTheme(!isDark.value);
  };

  // Watch for system theme changes
  onMounted(() => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const handleChange = (e) => {
      // Only update if no theme is manually set
      if (!localStorage.getItem('theme')) {
        updateTheme(e.matches);
      }
    };
    mediaQuery.addEventListener('change', handleChange);
    // No need to return a cleanup function for addEventListener in Vue 3 onMounted
  });

  return {
    isDark,
    initializeTheme,
    toggleTheme,
  };
}

// Initialize theme immediately when the module loads to prevent FOUC
// This follows the Tailwind CSS documentation recommendation
if (typeof window !== 'undefined') {
  // Call the sync initialization immediately
  const initializeThemeSync = () => {
    try {
      const savedTheme = localStorage.getItem('theme')
      const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches

      const shouldBeDark = savedTheme === 'dark' ||
                          (!savedTheme && systemPrefersDark)

      if (shouldBeDark) {
        document.documentElement.classList.add('dark')
        document.documentElement.setAttribute('data-theme', 'dark')
      } else {
        document.documentElement.classList.remove('dark')
        document.documentElement.setAttribute('data-theme', 'light')
      }
    } catch (error) {
      console.error('Error in immediate theme initialization:', error)
    }
  }

  // Run immediately
  initializeThemeSync()
}

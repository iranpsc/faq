import { ref, onMounted, computed } from 'vue';

// Global reactive state to ensure all components share the same theme state
const isDark = ref(false);
const themeMode = ref('light'); // 'light' | 'dark' | 'auto'

let autoTimerId = null;

function computeIsDarkForAuto() {
  const currentHour = new Date().getHours();
  // Night: 19:00 - 06:59, Day: 07:00 - 18:59
  return currentHour < 7 || currentHour >= 19;
}

function applyEffectiveThemeFromMode() {
  const shouldUseDark = themeMode.value === 'dark'
    ? true
    : themeMode.value === 'light'
      ? false
      : computeIsDarkForAuto();

  isDark.value = shouldUseDark;

  if (shouldUseDark) {
    document.documentElement.classList.add('dark');
    document.documentElement.setAttribute('data-theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    document.documentElement.setAttribute('data-theme', 'light');
  }
}

function clearAutoTimer() {
  if (autoTimerId) {
    clearTimeout(autoTimerId);
    autoTimerId = null;
  }
}

function msUntilNextAutoBoundary() {
  const now = new Date();
  const hour = now.getHours();
  const next = new Date(now);
  // Boundaries at 07:00 and 19:00 local time
  if (hour < 7) {
    next.setHours(7, 0, 0, 0);
  } else if (hour < 19) {
    next.setHours(19, 0, 0, 0);
  } else {
    // Next day 07:00
    next.setDate(next.getDate() + 1);
    next.setHours(7, 0, 0, 0);
  }
  return next.getTime() - now.getTime();
}

function scheduleAutoReevaluation() {
  clearAutoTimer();
  if (themeMode.value !== 'auto') return;
  const delay = msUntilNextAutoBoundary();
  autoTimerId = setTimeout(() => {
    applyEffectiveThemeFromMode();
    scheduleAutoReevaluation();
  }, Math.max(60_000, delay)); // enforce minimum 1 minute
}

export function useTheme() {
  const initializeTheme = () => {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'light' || savedTheme === 'dark' || savedTheme === 'auto') {
      themeMode.value = savedTheme;
    } else {
      // Default to system preference on first load
      themeMode.value = systemPrefersDark ? 'dark' : 'light';
    }

    applyEffectiveThemeFromMode();
    if (themeMode.value === 'auto') scheduleAutoReevaluation();
  };

  const setTheme = (mode) => {
    // mode: 'light' | 'dark' | 'auto'
    themeMode.value = mode;
    localStorage.setItem('theme', mode);
    applyEffectiveThemeFromMode();

    clearAutoTimer();
    if (mode === 'auto') scheduleAutoReevaluation();
  };

  const toggleTheme = () => {
    // Only toggles between light and dark for convenience
    setTheme(isDark.value ? 'light' : 'dark');
  };

  // Listen to system theme changes only when no manual preference set (not used for 'auto')
  const setupSystemThemeListener = () => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const handleChange = (e) => {
      if (!localStorage.getItem('theme')) {
        themeMode.value = e.matches ? 'dark' : 'light';
        applyEffectiveThemeFromMode();
      }
    };
    mediaQuery.addEventListener('change', handleChange);
    return () => {
      mediaQuery.removeEventListener('change', handleChange);
    };
  };

  // Effective theme string for UI coloring
  const theme = computed(() => (isDark.value ? 'dark' : 'light'));

  // Keep auto mode up to date if the tab becomes visible again after a long time
  onMounted(() => {
    const handleVisibility = () => {
      if (themeMode.value === 'auto') {
        applyEffectiveThemeFromMode();
        scheduleAutoReevaluation();
      }
    };
    document.addEventListener('visibilitychange', handleVisibility);
  });

  return {
    isDark,
    theme, // effective theme: 'dark' | 'light'
    themeMode, // selected mode: 'dark' | 'light' | 'auto'
    initializeTheme,
    toggleTheme,
    setTheme,
    setupSystemThemeListener,
  };
}

// Initialize theme immediately when the module loads to prevent FOUC
// This follows the Tailwind CSS documentation recommendation
if (typeof window !== 'undefined') {
  const initializeThemeSync = () => {
    try {
      const savedTheme = localStorage.getItem('theme');
      const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

      let mode = 'light';
      if (savedTheme === 'light' || savedTheme === 'dark' || savedTheme === 'auto') {
        mode = savedTheme;
      } else {
        mode = systemPrefersDark ? 'dark' : 'light';
      }

      themeMode.value = mode;

      const shouldBeDark = mode === 'dark' ? true : mode === 'light' ? false : computeIsDarkForAuto();
      isDark.value = shouldBeDark;

      if (shouldBeDark) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
      } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
      }
    } catch (error) {
      console.error('Error in immediate theme initialization:', error);
    }
  };

  initializeThemeSync();
}

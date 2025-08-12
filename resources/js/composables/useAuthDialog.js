import { getCurrentInstance } from 'vue'
import { useTheme } from './useTheme'
import { useAuth } from './useAuth'

// Centralized authentication dialog to avoid duplication across components
export function useAuthDialog() {
  const { isDark } = useTheme()
  const { handleLogin } = useAuth()

  const getSwalApi = () => {
    const instance = getCurrentInstance()
    // Prefer Vue-provided $swal, then window.$swal, then SweetAlert2 class (window.Swal)
    const maybeFn = instance?.appContext?.config?.globalProperties?.$swal || window.$swal
    const maybeClass = window.Swal

    if (maybeFn) {
      return {
        fire: (opts) => maybeFn(opts),
        showLoading: () => (maybeFn.showLoading ? maybeFn.showLoading() : (maybeClass?.showLoading ? maybeClass.showLoading() : undefined)),
        close: () => (maybeFn.close ? maybeFn.close() : (maybeClass?.close ? maybeClass.close() : undefined)),
        showValidationMessage: (msg) => (maybeFn.showValidationMessage ? maybeFn.showValidationMessage(msg) : (maybeClass?.showValidationMessage ? maybeClass.showValidationMessage(msg) : undefined)),
        DismissReason: maybeFn.DismissReason || maybeClass?.DismissReason
      }
    }

    if (maybeClass) {
      return {
        fire: (opts) => maybeClass.fire(opts),
        showLoading: () => maybeClass.showLoading(),
        close: () => maybeClass.close(),
        showValidationMessage: (msg) => maybeClass.showValidationMessage(msg),
        DismissReason: maybeClass.DismissReason
      }
    }

    return null
  }

  const handleRegisterRedirect = async () => {
    const swal = getSwalApi()
    if (!swal) {
      // Fallback: open directly
      window.open('https://accounts.irpsc.com/register', '_blank')
      return
    }

    swal.fire({
      title: 'در حال انتقال...',
      text: 'لطفاً صبر کنید',
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        swal.showLoading()
      }
    })

    try {
      await new Promise(resolve => setTimeout(resolve, 800))
      window.open('https://accounts.irpsc.com/register', '_blank')
      swal.close()
    } catch (error) {
      console.error('Register redirect error:', error)
      swal.fire({
        title: 'خطا',
        text: 'خطا در باز کردن صفحه ثبت نام.',
        icon: 'error',
        confirmButtonText: 'باشه'
      })
    }
  }

  const showAuthenticationDialog = () => {
    const swal = getSwalApi()
    if (!swal) {
      // No Swal instance available; directly start login
      handleLogin()
      return
    }

    swal.fire({
      html: `
                    <div class="${isDark.value ? 'text-white' : 'text-gray-800'}">
                        <h2 class="text-xl font-bold mb-2">وارد حساب کاربری شوید</h2>
                        <p>برای ادامه این عملیات باید وارد حساب کاربری خود شوید. اگر حساب کاربری ندارید، ثبت نام کنید.</p>
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
          await handleLogin()
          return true
        } catch (error) {
          console.error('Login error:', error)
          swal.showValidationMessage('خطا در ورود. لطفاً دوباره تلاش کنید.')
          return false
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        // Login was initiated in preConfirm
      } else if (result.dismiss === swal.DismissReason?.cancel) {
        handleRegisterRedirect()
      }
    })
  }

  return { showAuthenticationDialog, handleRegisterRedirect }
}



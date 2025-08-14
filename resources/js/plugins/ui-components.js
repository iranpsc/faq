// Global UI Components Plugin (async registration for smaller initial bundle)
import { defineAsyncComponent } from 'vue'

const asyncComponents = {
  ContentArea: () => import('../components/ContentArea.vue'),
  BaseButton: () => import('../components/ui/BaseButton.vue'),
  BaseCard: () => import('../components/ui/BaseCard.vue'),
  BaseAlert: () => import('../components/ui/BaseAlert.vue'),
  BaseModal: () => import('../components/ui/BaseModal.vue'),
  BaseInput: () => import('../components/ui/BaseInput.vue'),
  BaseForm: () => import('../components/ui/BaseForm.vue'),
  BaseTextarea: () => import('../components/ui/BaseTextarea.vue'),
  BaseBadge: () => import('../components/ui/BaseBadge.vue'),
  BaseAvatar: () => import('../components/ui/BaseAvatar.vue'),
  BaseDropdown: () => import('../components/ui/BaseDropdown.vue'),
  BasePagination: () => import('../components/ui/BasePagination.vue'),
  BaseSelect2: () => import('../components/ui/BaseSelect2.vue'),
  BaseEditor: () => import('../components/ui/BaseEditor.vue'),
  LoadingSpinner: () => import('../components/ui/LoadingSpinner.vue'),
  VoteButtons: () => import('../components/ui/VoteButtons.vue'),
}

export default {
  install(app) {
    Object.entries(asyncComponents).forEach(([name, loader]) => {
      app.component(name, defineAsyncComponent(loader))
    })
  }
}

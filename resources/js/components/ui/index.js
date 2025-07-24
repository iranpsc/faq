// Base UI Components
export { default as BaseButton } from './BaseButton.vue'
export { default as BaseCard } from './BaseCard.vue'
export { default as BaseAlert } from './BaseAlert.vue'
export { default as BaseModal } from './BaseModal.vue'
export { default as BaseInput } from './BaseInput.vue'
export { default as BaseForm } from './BaseForm.vue'
export { default as BaseTextarea } from './BaseTextarea.vue'
export { default as BaseBadge } from './BaseBadge.vue'
export { default as BaseAvatar } from './BaseAvatar.vue'
export { default as BaseDropdown } from './BaseDropdown.vue'
export { default as LoadingSpinner } from './LoadingSpinner.vue'
export { default as VoteButtons } from './VoteButtons.vue'

// Export all components as an object for global registration
export default {
  BaseButton: () => import('./BaseButton.vue'),
  BaseCard: () => import('./BaseCard.vue'),
  BaseAlert: () => import('./BaseAlert.vue'),
  BaseModal: () => import('./BaseModal.vue'),
  BaseInput: () => import('./BaseInput.vue'),
  BaseForm: () => import('./BaseForm.vue'),
  BaseTextarea: () => import('./BaseTextarea.vue'),
  BaseBadge: () => import('./BaseBadge.vue'),
  BaseAvatar: () => import('./BaseAvatar.vue'),
  BaseDropdown: () => import('./BaseDropdown.vue'),
  LoadingSpinner: () => import('./LoadingSpinner.vue'),
  VoteButtons: () => import('./VoteButtons.vue'),
}

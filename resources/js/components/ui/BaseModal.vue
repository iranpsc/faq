<template>
    <teleport to="body">
        <TransitionRoot as="template" :show="visible">
            <Dialog class="relative z-[9999]" @close="handleDialogClose"
                :aria-labelledby="title ? 'modal-title' : undefined">
                <!-- Backdrop -->
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                    enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 transition-opacity" />
                </TransitionChild>

                <!-- Modal container -->
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto" dir="rtl">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel :class="modalClasses" @click.stop dir="rtl" ref="modalPanel">
                                <!-- Header -->
                                <div v-if="$slots.header || title || closable" class="modal-header"
                                    :class="headerClasses">
                                    <slot name="header">
                                        <div v-if="title">
                                            <DialogTitle as="h3" id="modal-title"
                                                class="text-lg text-right font-semibold text-gray-900 dark:text-gray-100">
                                                {{ title }}
                                            </DialogTitle>
                                            <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                {{ subtitle }}
                                            </p>
                                        </div>
                                    </slot>
                                    <button v-if="closable" @click="close"
                                        class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        type="button">
                                        <span class="sr-only">بستن</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Body -->
                                <div v-if="$slots.default" class="modal-body" :class="bodyClasses">
                                    <slot />
                                </div>

                                <!-- Footer -->
                                <div v-if="$slots.footer" class="modal-footer" :class="footerClasses">
                                    <slot name="footer" />
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </teleport>
</template>

<script>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'

export default {
    name: 'BaseModal',
    components: {
        Dialog,
        DialogPanel,
        DialogTitle,
        TransitionChild,
        TransitionRoot
    },
    emits: ['close', 'confirm', 'cancel'],
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
            default: ''
        },
        subtitle: {
            type: String,
            default: ''
        },
        size: {
            type: String,
            default: 'md',
            validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', 'full'].includes(value)
        },
        closable: {
            type: Boolean,
            default: true
        },
        closeOnBackdrop: {
            type: Boolean,
            default: true
        },
        closeOnEscape: {
            type: Boolean,
            default: true
        },
        persistent: {
            type: Boolean,
            default: false
        },
        headerPadding: {
            type: String,
            default: 'md'
        },
        bodyPadding: {
            type: String,
            default: 'md'
        },
        footerPadding: {
            type: String,
            default: 'md'
        }
    },
    computed: {
        modalClasses() {
            return [
                'relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full',
                this.sizeClasses
            ]
        },
        sizeClasses() {
            const sizes = {
                xs: 'sm:max-w-xs',
                sm: 'sm:max-w-sm',
                md: 'sm:max-w-md',
                lg: 'sm:max-w-lg',
                xl: 'sm:max-w-xl',
                '2xl': 'sm:max-w-2xl',
                '3xl': 'sm:max-w-3xl',
                '4xl': 'sm:max-w-4xl',
                full: 'sm:max-w-full h-full'
            }
            return sizes[this.size]
        },
        headerClasses() {
            return [
                'flex items-center justify-between border-b border-gray-200 dark:border-gray-700',
                this.getPaddingClass(this.headerPadding)
            ]
        },
        bodyClasses() {
            return [
                this.getPaddingClass(this.bodyPadding)
            ]
        },
        footerClasses() {
            return [
                'flex items-center justify-start gap-3 border-t border-gray-200 dark:border-gray-700',
                this.getPaddingClass(this.footerPadding)
            ]
        }
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.handleOpen()
            } else {
                this.handleClose()
            }
        }
    },
    mounted() {
        if (this.closeOnEscape) {
            document.addEventListener('keydown', this.handleEscape)
        }

        if (this.visible) {
            this.handleOpen()
        }
    },
    beforeUnmount() {
        document.removeEventListener('keydown', this.handleEscape)
        this.handleClose()
    },
    methods: {
        close() {
            this.$emit('close')
        },
        confirm() {
            this.$emit('confirm')
        },
        cancel() {
            this.$emit('cancel')
        },
        handleDialogClose() {
            if (!this.persistent) {
                this.close()
            }
        },
        handleEscape(e) {
            if (e.key === 'Escape' && this.visible && !this.persistent) {
                this.close()
            }
        },
        handleOpen() {
            // Prevent body scroll
            document.body.style.overflow = 'hidden'

            // Focus management
            this.$nextTick(() => {
                if (this.$refs.modalPanel) {
                    // Access the actual DOM element from the component instance
                    const modalElement = this.$refs.modalPanel.$el || this.$refs.modalPanel
                    const firstFocusable = modalElement.querySelector(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    )
                    if (firstFocusable) {
                        firstFocusable.focus()
                    }
                }
            })
        },
        handleClose() {
            // Restore body scroll
            document.body.style.overflow = ''
        },
        getPaddingClass(size) {
            const paddings = {
                none: 'p-0',
                sm: 'p-3',
                md: 'p-4',
                lg: 'p-6',
                xl: 'p-8'
            }
            return paddings[size] || paddings.md
        }
    }
}
</script>

<style scoped>
/* RTL support - Default to RTL */
.modal-header {
    direction: rtl;
}

.modal-body {
    direction: rtl;
    text-align: right;
}

.modal-footer {
    direction: rtl;
}

/* LTR override for specific cases */
:global(.ltr) .modal-header {
    direction: ltr;
}

:global(.ltr) .modal-body {
    direction: ltr;
    text-align: left;
}

:global(.ltr) .modal-footer {
    direction: ltr;
    justify-content: flex-end;
}

/* Smooth transitions */
.modal-header,
.modal-body,
.modal-footer {
    transition: all 0.2s ease-in-out;
}
</style>

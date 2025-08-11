<template>
    <div>
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
        </label>
        <Editor
            :id="id"
            :api-key="apiKey"
            :model-value="modelValue"
            :init="editorConfig"
            @update:modelValue="$emit('update:modelValue', $event)"
        />
        <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
    </div>
</template>

<script>
import Editor from '@tinymce/tinymce-vue';

export default {
    name: 'BaseEditor',
    components: {
        Editor
    },
    props: {
        id: {
            type: String,
            default: () => `editor-${Math.random().toString(36).substr(2, 9)}`
        },
        modelValue: {
            type: String,
            default: ''
        },
        label: {
            type: String,
            default: ''
        },
        error: {
            type: String,
            default: ''
        },
        height: {
            type: Number,
            default: 300
        },
        apiKey: {
            type: String,
            default: import.meta.env.VITE_TINYMCE_API_KEY || ''
        },
        mode: {
            type: String,
            default: 'full', // 'full', 'simple', 'minimal'
            validator: (value) => ['full', 'simple', 'minimal'].includes(value)
        },
        imageUpload: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: ''
        }
    },
    emits: ['update:modelValue'],
    computed: {
        editorConfig() {
            // Dynamically set skin and content_css based on app theme
            const isDark = document.documentElement.classList.contains('dark');

            const baseConfig = {
                height: this.height,
                menubar: false,
                directionality: 'rtl',
                placeholder: this.placeholder,
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? 'dark' : 'default',
                convert_urls: false,
                relative_urls: false,
                remove_script_host: false,
                setup: (editor) => {
                    if (this.imageUpload) {
                        editor.on('ImageUploadError', () => {})
                    }
                },
            };

            if (this.mode === 'full') {
                return {
                    ...baseConfig,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
                        'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'paste', 'help', 'wordcount', 'directionality'
                    ],
                    toolbar: 'removeformat | ltr rtl | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify | bold italic backcolor | formatselect | image | undo redo',
                    ...(this.imageUpload && {
                        images_upload_handler: this.handleImageUpload,
                        automatic_uploads: true,
                        paste_data_images: true,
                        file_picker_types: 'image',
                        images_reuse_filename: false,
                    })
                };
            } else if (this.mode === 'simple') {
                return {
                    ...baseConfig,
                    plugins: ['lists', 'link', 'image', 'code', 'help', 'wordcount', 'directionality'],
                    toolbar: 'undo redo | ltr rtl | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | ' + (this.imageUpload ? 'image | ' : '') + 'help',
                    ...(this.imageUpload && {
                        images_upload_handler: this.handleImageUpload,
                        automatic_uploads: true,
                        paste_data_images: true,
                        file_picker_types: 'image',
                        images_reuse_filename: false,
                    })
                };
            } else { // minimal
                return {
                    ...baseConfig,
                    plugins: ['lists', 'link', 'wordcount'],
                    toolbar: 'undo redo | bold italic | bullist numlist | removeformat'
                };
            }
        }
    },
    methods: {
        // Image upload handler for TinyMCE
        handleImageUpload(blobInfo, progress) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.withCredentials = false; // Set to false as per TinyMCE documentation
                xhr.open('POST', '/api/upload/tinymce-image');

                // Get the CSRF token from meta tag
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }

                // Set authorization header if user is authenticated
                const authToken = localStorage.getItem('auth_token');
                if (authToken) {
                    xhr.setRequestHeader('Authorization', `Bearer ${authToken}`);
                }

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        progress(e.loaded / e.total * 100);
                    }
                };

                xhr.onload = () => {
                    if (xhr.status === 403) {
                        reject({ message: 'دسترسی مجاز نیست', remove: true });
                        return;
                    }

                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject({ message: 'خطای HTTP: ' + xhr.status, remove: false });
                        return;
                    }

                    let json;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch (e) {
                        reject({ message: 'خطا در پردازش پاسخ سرور', remove: false });
                        return;
                    }

                    if (!json || typeof json.location !== 'string') {
                        reject({ message: 'پاسخ نامعتبر از سرور: ' + xhr.responseText, remove: false });
                        return;
                    }

                    resolve(json.location);
                };

                xhr.onerror = () => {
                    reject({ message: 'خطا در آپلود تصویر. کد خطا: ' + xhr.status, remove: false });
                };

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                xhr.send(formData);
            });
        }
    }
};
</script>

<style>
/* Fix TinyMCE dialog z-index issues when inside modals */
.tox-tinymce-aux {
    z-index: 99999 !important;
}

.tox-dialog-wrap {
    z-index: 99999 !important;
}

.tox-dialog {
    z-index: 99999 !important;
}

.tox-dialog__backdrop {
    z-index: 99998 !important;
}

/* Fix for TinyMCE toolbar dropdowns */
.tox-pop {
    z-index: 99999 !important;
}

/* Fix for TinyMCE context menus */
.tox-menu {
    z-index: 99999 !important;
}

/* Ensure TinyMCE dialogs appear above the modal backdrop */
.tox .tox-dialog {
    z-index: 99999 !important;
}
</style>

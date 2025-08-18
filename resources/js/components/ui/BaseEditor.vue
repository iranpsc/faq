<template>
    <div>
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
        </label>
        <div :id="id" class="quill-editor-container">
            <div ref="editorRef" :style="{ height: height + 'px' }"></div>
        </div>
        <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
    </div>
</template>

<script>
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

export default {
    name: 'BaseEditor',
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
        mode: {
            type: String,
            default: 'full',
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
    data() {
        return {
            quill: null
        }
    },
    mounted() {
        this.initQuill();
    },
    beforeUnmount() {
        if (this.quill) {
            this.quill.off('text-change', this.handleTextChange);
            this.quill = null;
        }
    },
    watch: {
        modelValue(newValue) {
            if (this.quill && newValue !== this.quill.root.innerHTML) {
                this.quill.root.innerHTML = newValue;
            }
        }
    },
    methods: {
        initQuill() {
            const isDark = document.documentElement.classList.contains('dark');

            // Build toolbar handlers
            const handlers = {
                undo: () => {
                    if (this.quill) this.quill.history.undo();
                },
                redo: () => {
                    if (this.quill) this.quill.history.redo();
                }
            };
            if (this.imageUpload) {
                handlers.image = () => this.selectImage();
            }

            const modules = {
                toolbar: {
                    container: this.getToolbarConfig(),
                    handlers
                },
                history: {
                    delay: 500,
                    maxStack: 500,
                    userOnly: true
                },
                clipboard: {
                    matchVisual: false
                }
            };

            this.quill = new Quill(this.$refs.editorRef, {
                theme: 'snow',
                modules: modules,
                placeholder: this.placeholder
            });

            if (this.modelValue) {
                this.quill.root.innerHTML = this.modelValue;
            }

            this.quill.on('text-change', this.handleTextChange);

            // Initialize image resize for any existing images
            this.$nextTick(() => {
                this.initializeImageResize();
            });

            // Apply dark mode styles
            if (isDark) {
                this.applyDarkMode();
            }

            // Watch for theme changes
            this.watchThemeChanges();
        },

        getToolbarConfig() {
            if (this.mode === 'full') {
                if (this.imageUpload) {
                    return [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        ['link'],
                        ['image'],
                        ['code-block'],
                        ['clean'],
                        ['undo', 'redo']
                    ];
                } else {
                    return [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        ['link'],
                        ['code-block'],
                        ['clean'],
                        ['undo', 'redo']
                    ];
                }
            } else if (this.mode === 'simple') {
                if (this.imageUpload) {
                    return [
                        ['bold', 'italic', 'underline'],
                        [{ 'align': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link'],
                        ['image'],
                        ['code-block'],
                        ['clean'],
                        ['undo', 'redo']
                    ];
                } else {
                    return [
                        ['bold', 'italic', 'underline'],
                        [{ 'align': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link'],
                        ['code-block'],
                        ['clean'],
                        ['undo', 'redo']
                    ];
                }
            } else {
                return [
                    ['bold', 'italic'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean'],
                    ['undo', 'redo']
                ];
            }
        },

        setupImageHandler() {
            const toolbar = this.quill.getModule('toolbar');
            toolbar.addHandler('image', () => {
                this.selectImage();
            });
        },

        selectImage() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = () => {
                const file = input.files[0];
                if (file) {
                    this.uploadImage(file)
                        .then(url => {
                            // Image uploaded successfully
                        })
                        .catch(error => {
                            console.error('Image upload failed:', error);
                        });
                }
            };
        },

        uploadImage(file) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('file', file);

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const authToken = localStorage.getItem('auth_token');

                const headers = {
                    'X-CSRF-TOKEN': token || ''
                };

                if (authToken) {
                    headers['Authorization'] = `Bearer ${authToken}`;
                }

                fetch('/api/upload/quill-image', {
                    method: 'POST',
                    headers: headers,
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.location) {
                        // Insert the image into the editor
                        this.insertImageIntoEditor(data.location);
                        resolve(data.location);
                    } else {
                        console.error('No location in response data:', data);
                        reject(new Error('No image URL received'));
                    }
                })
                .catch(error => {
                    console.error('Image upload failed:', error);
                    if (error.message.includes('401') || error.message.includes('Unauthorized')) {
                        console.error('Authentication required for image upload');
                    }
                    reject(error);
                });
            });
        },

                insertImageIntoEditor(imageUrl) {
            if (!this.quill) {
                console.error('Quill editor is not initialized');
                return;
            }

            // Use HTML insertion as the primary method to avoid Quill selection issues
            try {
                const currentHTML = this.quill.root.innerHTML;
                const imageHTML = `<img src="${imageUrl}" alt="Uploaded image" class="ql-image" style="max-width: 100%; height: auto; display: block; margin: 1rem auto; cursor: pointer;">`;

                // If editor is empty or only has a blank paragraph, replace with image
                if (currentHTML === '<p><br></p>' || currentHTML === '' || currentHTML === '<p></p>') {
                    this.quill.root.innerHTML = imageHTML;
                } else {
                    // Otherwise append the image with a newline
                    this.quill.root.innerHTML = currentHTML + '<br>' + imageHTML;
                }

                // Force a text change event to update the model
                this.$nextTick(() => {
                    this.handleTextChange();
                });

                // Initialize image resize functionality
                this.$nextTick(() => {
                    this.initializeImageResize();
                });

            } catch (htmlError) {
                console.error('HTML insertion failed:', htmlError);

                // Fallback to Quill insertEmbed with extra safety measures
                try {
                    // Reset Quill's selection state by focusing and clearing selection
                    this.quill.focus();
                    this.quill.setSelection(0, 0, 'silent');

                    // Get the current length after reset
                    const currentLength = this.quill.getLength();

                    // Insert at the end using 'silent' source
                    this.quill.insertEmbed(currentLength, 'image', imageUrl, 'silent');

                    // Force a text change event to update the model
                    this.$nextTick(() => {
                        this.handleTextChange();
                    });

                    // Initialize image resize functionality
                    this.$nextTick(() => {
                        this.initializeImageResize();
                    });

                } catch (quillError) {
                    console.error('All insertion methods failed:', quillError);
                }
            }
        },

        handleTextChange() {
            const html = this.quill.root.innerHTML;
            if (html !== this.modelValue) {
                this.$emit('update:modelValue', html);
            }
        },

        applyDarkMode() {
            const editor = this.$refs.editorRef;
            if (editor) {
                editor.classList.add('dark-mode');
            }
        },

        watchThemeChanges() {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const isDark = document.documentElement.classList.contains('dark');
                        const editor = this.$refs.editorRef;
                        if (editor) {
                            if (isDark) {
                                editor.classList.add('dark-mode');
                            } else {
                                editor.classList.remove('dark-mode');
                            }
                        }
                    }
                });
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        },

                initializeImageResize() {
            if (!this.quill) return;

            // Find all images in the editor and ensure they have the proper class
            const images = this.quill.root.querySelectorAll('img');
            images.forEach(img => {
                if (!img.classList.contains('ql-image')) {
                    img.classList.add('ql-image');
                }
                img.style.cursor = 'pointer';

                // Add resize functionality if not already added
                if (!img.hasAttribute('data-resizable')) {
                    this.makeImageResizable(img);
                }
            });
        },

        makeImageResizable(img) {
            img.setAttribute('data-resizable', 'true');

            // Wrap image in a resizer container if not already wrapped
            let wrapper = img.closest('.image-resizer');
            if (!wrapper || wrapper.firstElementChild !== img) {
                wrapper = document.createElement('span');
                wrapper.className = 'image-resizer';
                wrapper.style.position = 'relative';
                wrapper.style.display = 'inline-block';
                if (img.parentNode) {
                    img.parentNode.insertBefore(wrapper, img);
                    wrapper.appendChild(img);
                }
            }

            // Create resize handles
            const handles = ['nw', 'ne', 'sw', 'se'];
            handles.forEach(handle => {
                // Avoid duplicating handles
                if (wrapper.querySelector(`.resize-handle-${handle}`)) return;

                const handleEl = document.createElement('div');
                handleEl.className = `resize-handle resize-handle-${handle}`;
                handleEl.style.cssText = `
                    position: absolute;
                    width: 8px;
                    height: 8px;
                    background: #3b82f6;
                    border: 1px solid white;
                    border-radius: 50%;
                    cursor: ${handle === 'nw' || handle === 'se' ? 'nw-resize' : 'ne-resize'};
                    z-index: 1000;
                    display: none;
                `;

                // Position the handle
                switch(handle) {
                    case 'nw': handleEl.style.top = '-4px'; handleEl.style.left = '-4px'; break;
                    case 'ne': handleEl.style.top = '-4px'; handleEl.style.right = '-4px'; break;
                    case 'sw': handleEl.style.bottom = '-4px'; handleEl.style.left = '-4px'; break;
                    case 'se': handleEl.style.bottom = '-4px'; handleEl.style.right = '-4px'; break;
                }

                wrapper.appendChild(handleEl);

                // Add resize functionality
                this.addResizeListener(handleEl, img, handle);
            });

            // Hover visibility for controls
            if (!wrapper.__hoverBound) {
                wrapper.addEventListener('mouseenter', () => {
                    const controlEls = wrapper.querySelectorAll('.resize-handle, .image-remove-button');
                    controlEls.forEach(el => el.style.display = 'block');
                });
                wrapper.addEventListener('mouseleave', () => {
                    const controlEls = wrapper.querySelectorAll('.resize-handle, .image-remove-button');
                    controlEls.forEach(el => el.style.display = 'none');
                });
                Object.defineProperty(wrapper, '__hoverBound', { value: true, enumerable: false });
            }

            // Add remove button if not present
            if (!wrapper.querySelector('.image-remove-button')) {
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'image-remove-button';
                removeBtn.setAttribute('aria-label', 'Remove image');
                removeBtn.innerHTML = '×';
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const toRemove = wrapper;
                    if (toRemove && toRemove.parentNode) {
                        toRemove.parentNode.removeChild(toRemove);
                        this.$nextTick(() => {
                            this.handleTextChange();
                        });
                    }
                });
                wrapper.appendChild(removeBtn);
            }
        },

        addResizeListener(handle, img, direction) {
            let isResizing = false;
            let startX, startY, startWidth, startHeight;

            handle.addEventListener('mousedown', (e) => {
                e.preventDefault();
                isResizing = true;
                startX = e.clientX;
                startY = e.clientY;
                startWidth = img.offsetWidth;
                startHeight = img.offsetHeight;

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            const onMouseMove = (e) => {
                if (!isResizing) return;

                const deltaX = e.clientX - startX;
                const deltaY = e.clientY - startY;

                let newWidth = startWidth;
                let newHeight = startHeight;

                // Calculate new dimensions based on direction
                if (direction.includes('e')) {
                    newWidth = startWidth + deltaX;
                } else if (direction.includes('w')) {
                    newWidth = startWidth - deltaX;
                }

                if (direction.includes('s')) {
                    newHeight = startHeight + deltaY;
                } else if (direction.includes('n')) {
                    newHeight = startHeight - deltaY;
                }

                // Maintain aspect ratio
                const aspectRatio = startWidth / startHeight;
                if (Math.abs(deltaX) > Math.abs(deltaY)) {
                    newHeight = newWidth / aspectRatio;
                } else {
                    newWidth = newHeight * aspectRatio;
                }

                // Apply minimum size constraints
                newWidth = Math.max(50, newWidth);
                newHeight = Math.max(50, newHeight);

                // Apply maximum size constraints
                const maxWidth = this.$refs.editorRef.offsetWidth - 40;
                newWidth = Math.min(maxWidth, newWidth);
                newHeight = Math.min(maxWidth / aspectRatio, newHeight);

                img.style.width = newWidth + 'px';
                img.style.height = newHeight + 'px';
            };

            const onMouseUp = () => {
                isResizing = false;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                // Trigger text change to update the model
                this.$nextTick(() => {
                    this.handleTextChange();
                });
            };
        }
    }
};
</script>

<style>
.quill-editor-container {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    overflow: hidden;
}

.quill-editor-container .ql-editor {
    direction: rtl;
    text-align: right;
    font-family: 'Vazirmatn', 'Tahoma', ui-sans-serif, system-ui, sans-serif;
    line-height: 1.6;
    padding: 1rem;
}

.quill-editor-container .ql-toolbar {
    border-bottom: 1px solid #d1d5db;
    background-color: #f9fafb;
}

.quill-editor-container .ql-container {
    border: none;
    background-color: white;
}

.dark .quill-editor-container {
    border-color: #4b5563;
}

.dark .quill-editor-container .ql-toolbar {
    background-color: #374151;
    border-bottom-color: #4b5563;
}

.dark .quill-editor-container .ql-container {
    background-color: #1f2937;
}

.dark .quill-editor-container .ql-editor {
    color: #f9fafb;
}

.dark .quill-editor-container .ql-toolbar button {
    color: #d1d5db;
}

.dark .quill-editor-container .ql-toolbar button:hover {
    color: #f9fafb;
}

.dark .quill-editor-container .ql-toolbar button.ql-active {
    color: #3b82f6;
}

.dark .quill-editor-container .ql-picker {
    color: #d1d5db;
}

.dark .quill-editor-container .ql-picker-options {
    background-color: #374151;
    border-color: #4b5563;
}

.dark .quill-editor-container .ql-picker-item {
    color: #d1d5db;
}

.dark .quill-editor-container .ql-picker-item:hover {
    background-color: #4b5563;
    color: #f9fafb;
}

.quill-editor-container:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.quill-editor-container .ql-editor img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 1rem auto;
    cursor: pointer;
}

/* Image resize styles */
.quill-editor-container .ql-editor img.ql-image {
    cursor: pointer;
    position: relative;
}

.quill-editor-container .ql-editor .image-resizer {
    position: relative;
    display: block;
    margin: 1rem auto;
}

.quill-editor-container .ql-editor .image-resizer .ql-image {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 0;
}

.quill-editor-container .ql-editor img {
    position: relative;
}

.quill-editor-container .ql-editor .resize-handle {
    position: absolute;
    width: 8px;
    height: 8px;
    background: #3b82f6;
    border: 1px solid white;
    border-radius: 50%;
    z-index: 1000;
    display: none;
}

.quill-editor-container .ql-editor .resize-handle:hover {
    background: #2563eb;
    transform: scale(1.2);
}

/* Show controls on hover */
.quill-editor-container .ql-editor .image-resizer:hover .resize-handle {
    display: block;
}

/* Remove button for images */
.quill-editor-container .ql-editor .image-remove-button {
    position: absolute;
    top: -10px;
    right: -10px;
    width: 20px;
    height: 20px;
    background: #ef4444;
    color: #ffffff;
    border: 1px solid #ffffff;
    border-radius: 9999px;
    font-weight: 700;
    line-height: 18px;
    text-align: center;
    cursor: pointer;
    z-index: 1001;
    display: none;
}

.quill-editor-container .ql-editor .image-resizer:hover .image-remove-button {
    display: block;
}

.quill-editor-container .ql-editor ul,
.quill-editor-container .ql-editor ol {
    padding-right: 1.5rem;
    padding-left: 0;
}

.quill-editor-container .ql-editor blockquote {
    border-right: 4px solid #e5e7eb;
    padding-right: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #6b7280;
}

.dark .quill-editor-container .ql-editor blockquote {
    border-right-color: #4b5563;
    color: #9ca3af;
}

.quill-editor-container .ql-editor code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.dark .quill-editor-container .ql-editor code {
    background-color: #374151;
}

.quill-editor-container .ql-editor pre {
    background-color: #f3f4f6;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1rem 0;
}

.dark .quill-editor-container .ql-editor pre {
    background-color: #1f2937;
}

.quill-editor-container .ql-tooltip {
    z-index: 99999 !important;
}

.quill-editor-container .ql-picker-options {
    z-index: 99999 !important;
}
</style>

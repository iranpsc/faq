<template>
  <div class="fixed inset-0 bg-black bg-opacity-25 z-40 flex items-center justify-center" @click.self="$emit('close')">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl mx-4 flex flex-col max-h-[90vh]" style="direction: rtl;">
      <div class="flex justify-between items-center border-b pb-3 p-6 flex-shrink-0">
        <h2 class="text-xl font-semibold">{{ isEditMode ? 'ویرایش سوال' : 'سوال جدید' }}</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="overflow-y-auto p-6">
        <p class="text-gray-600 dark:text-gray-400 mb-6">مشخصات مربوط به سوال خود را در کادرهای زیر وارد کنید.</p>

        <form @submit.prevent="handleSubmitQuestion" id="question-form">
          <div class="space-y-6">
            <!-- Category -->
            <div>
              <BaseSelect2
                v-model="form.category"
                :options="categories"
                placeholder="انتخاب دسته بندی"
                label="دسته بندی"
                option-label="name"
                track-by="id"
                :searchable="true"
                :paginated="true"
                :page-size="10"
                :fetch-function="handleFetchCategories"
                :error="allErrors.category"
              />
            </div>

            <!-- Title -->
            <div>
              <BaseInput
                id="title"
                v-model="form.title"
                label="عنوان سوال"
                placeholder="عنوان سوال خود را وارد کنید..."
                required
              />
              <p v-if="allErrors.title" class="text-red-500 text-xs mt-1">{{ allErrors.title }}</p>
            </div>

            <!-- Content -->
            <div>
              <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شرح سوال</label>
              <Editor
                  id="content"
                  api-key="2sfprbtijd268hiw733k56v9bp9bpy8jgsqet6q8z4vvirow"
                  v-model="form.content"
                  :init="editorConfig"
              />
              <p v-if="allErrors.content" class="text-red-500 text-xs mt-1">{{ allErrors.content }}</p>
            </div>

            <!-- Tags -->
            <div>
              <BaseSelect2
                v-model="form.tags"
                :options="tagOptions"
                :multiple="true"
                :taggable="true"
                @tag-add="handleAddTag"
                placeholder="برای سوال خود برچسب وارد کنید..."
                label="برچسب ها"
                option-label="name"
                track-by="id"
                :searchable="true"
                :paginated="true"
                :page-size="10"
                :fetch-function="handleFetchTags"
                :error="allErrors.tags"
              />
              <p class="text-xs text-gray-500 mt-1">مثال: سوالی درباره کود مناسب درختان نوشته اید پس برچسب ها میتواند (کود مناسب، تغذیه درختان، مواد غذایی برای درخت، کود برای رشد درخت، رشد بهتر درخت) باشد.</p>
            </div>
          </div>
        </form>
      </div>

      <div class="mt-auto flex justify-end p-6 border-t flex-shrink-0">
        <button
          type="submit"
          form="question-form"
          :disabled="isSubmitting"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="isSubmitting">در حال {{ isEditMode ? 'ویرایش' : 'ثبت' }}...</span>
          <span v-else>{{ isEditMode ? 'ویرایش سوال' : 'ثبت سوال' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import BaseInput from './ui/BaseInput.vue';
import Editor from '@tinymce/tinymce-vue';
import BaseSelect2 from './ui/BaseSelect2.vue';
import { useQuestions, useCategories, useTags } from '../composables';
import { onMounted, watch, ref, computed } from 'vue';

export default {
  name: 'QuestionModal',
  components: {
    BaseInput,
    Editor,
    BaseSelect2
  },
  props: {
    questionToEdit: {
      type: Object,
      default: null,
    },
  },
  emits: ['close', 'question-created', 'question-updated'],
  setup(props, { emit }) {
    // Composables
    const {
      isSubmitting,
      errors: questionErrors,
      submitQuestion,
      updateQuestion,
      clearErrors: clearQuestionErrors,
    } = useQuestions();

    const {
      categories,
      isLoading: categoriesLoading,
      errors: categoryErrors,
      fetchCategories,
      clearErrors: clearCategoryErrors,
    } = useCategories();

    const {
      tags: tagOptions,
      isLoading: tagsLoading,
      errors: tagErrors,
      fetchTags,
      addTag,
      clearErrors: clearTagErrors,
    } = useTags();

    // Form data
    const form = ref({
      id: null,
      category: null,
      title: '',
      content: '',
      tags: [],
    });

    // Methods
    const populateForm = (question) => {
      if (!question) return;

      form.value = {
        id: question.id,
        title: question.title || '',
        content: question.content || '',
        category: question.category || (question.category_id ? categories.value.find(c => c.id === question.category_id) : null),
        tags: question.tags || [],
      };
    };

    const resetForm = () => {
      form.value = {
        id: null,
        category: null,
        title: '',
        content: '',
        tags: [],
      };
      clearAllErrors();
    };

    const clearAllErrors = () => {
      clearQuestionErrors();
      clearCategoryErrors();
      clearTagErrors();
    };

    // Lifecycle and Watchers
    onMounted(async () => {
      // No need to fetch data upfront as Select2 will handle it with pagination
      if (props.questionToEdit) {
        populateForm(props.questionToEdit);
      }
    });

    watch(() => props.questionToEdit, (newQuestion) => {
      if (newQuestion) {
        populateForm(newQuestion);
      } else {
        resetForm();
      }
    }, { immediate: true });

    // Watch for categories to be loaded and re-populate form if needed
    watch(categories, () => {
      if (props.questionToEdit && form.value.id && !form.value.category) {
        populateForm(props.questionToEdit);
      }
    });

    // Computed
    const isEditMode = computed(() => !!form.value.id);

    // Image upload handler for TinyMCE
    const handleImageUpload = (blobInfo, progress) => {
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

          // Ensure the location is returned exactly as provided by the server
          console.log('Image uploaded successfully:', json.location);
          resolve(json.location);
        };

        xhr.onerror = () => {
          reject({ message: 'خطا در آپلود تصویر. کد خطا: ' + xhr.status, remove: false });
        };

        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());

        xhr.send(formData);
      });
    };

    return {
      form,
      isEditMode,
      isSubmitting,
      questionErrors,
      submitQuestion,
      updateQuestion,
      clearQuestionErrors,
      categories,
      categoriesLoading,
      categoryErrors,
      fetchCategories,
      clearCategoryErrors,
      tagOptions,
      tagsLoading,
      tagErrors,
      fetchTags,
      addTag,
      clearTagErrors,
      clearAllErrors,
      resetForm,
      handleImageUpload,
    };
  },
  computed: {
    allErrors() {
      return {
        ...this.questionErrors,
        ...this.categoryErrors,
        ...this.tagErrors,
      };
    },
    editorConfig() {
      return {
        height: 300,
        menubar: false,
        directionality: 'rtl',
        plugins: [
          'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
          'searchreplace', 'visualblocks', 'code', 'fullscreen',
          'insertdatetime', 'media', 'table', 'paste', 'help', 'wordcount', 'directionality'
        ],
        toolbar: 'removeformat | ltr rtl | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify | bold italic backcolor | formatselect | image | undo redo',
        // Image upload configuration
        images_upload_handler: this.handleImageUpload,
        automatic_uploads: true,
        paste_data_images: true,
        file_picker_types: 'image',
        images_reuse_filename: false,
        // Additional configuration for better image handling
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,
        setup: (editor) => {
          editor.on('init', () => {
            console.log('TinyMCE editor initialized with ID:', editor.id);
          });
          editor.on('ImageUploadSuccess', (e) => {
            console.log('Image upload successful:', e.detail);
          });
          editor.on('ImageUploadError', (e) => {
            console.error('Image upload error:', e.detail);
          });
        }
      };
    },
  },
  methods: {
    async handleSubmitQuestion() {
      if (this.isSubmitting) return;
      this.clearAllErrors();

      // First, upload any images that are still in base64 format
      // Use the proper way to access TinyMCE editor instance
      if (window.tinymce) {
        const editor = window.tinymce.get('content') || window.tinymce.activeEditor;
        if (editor) {
          try {
            console.log('Uploading images before form submission...');
            await editor.uploadImages();
            console.log('Images uploaded successfully');
          } catch (error) {
            console.warn('خطا در آپلود تصاویر:', error);
            // Continue with form submission even if image upload fails
          }
        } else {
          console.warn('TinyMCE editor not found');
        }
      } else {
        console.warn('TinyMCE not available');
      }

      const action = this.isEditMode ? this.updateQuestion : this.submitQuestion;
      const result = await action(this.form);

      if (result.success) {
        this.$swal.fire({
          title: 'موفقیت!',
          text: `سوال شما با موفقیت ${this.isEditMode ? 'ویرایش' : 'ثبت'} شد.`,
          icon: 'success',
          confirmButtonText: 'باشه',
        });

        this.resetForm();
        this.$emit('close');
        const event = this.isEditMode ? 'question-updated' : 'question-created';
        this.$emit(event, result.data);
      } else {
        if (result.error === 'authentication' || result.error === 'authorization' || result.error === 'general') {
          this.$swal.fire({
            title: 'خطا!',
            text: result.message,
            icon: 'error',
            confirmButtonText: 'باشه',
          });
        }
      }
    },
    async handleFetchCategories(params = {}) {
      const result = await this.fetchCategories(params);
      if (!result.success) {
        this.$swal.fire({
          title: 'خطا!',
          text: result.error,
          icon: 'error',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
        });
      }
      return result;
    },
    async handleFetchTags(params = {}) {
      const result = await this.fetchTags(params);
      if (!result.success) {
        this.$swal.fire({
          title: 'خطا!',
          text: result.error,
          icon: 'error',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
        });
      }
      return result;
    },
    handleAddTag(newTag) {
      // Check if tag already exists in form.tags to avoid duplicates
      const existingTag = this.form.tags.find(tag =>
        tag.name.toLowerCase() === newTag.toLowerCase()
      );

      if (existingTag) {
        return; // Don't add duplicate
      }

      const tag = this.addTag(newTag);
      this.form.tags.push(tag);

      // Update tag options to include the new tag for future searches
      const existingInOptions = this.tagOptions.find(opt =>
        opt.name.toLowerCase() === newTag.toLowerCase()
      );

      if (!existingInOptions) {
        this.tagOptions.push(tag);
      }
    },
  },
};
</script>

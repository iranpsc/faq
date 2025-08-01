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
              <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">دسته بندی</label>
              <Multiselect
                v-model="form.category"
                :options="categories"
                placeholder="انتخاب دسته بندی"
                label="name"
                track-by="id"
                :searchable="true"
                @search-change="handleFetchCategories"
              />
              <p v-if="allErrors.category" class="text-red-500 text-xs mt-1">{{ allErrors.category }}</p>
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
                  api-key="2sfprbtijd268hiw733k56v9bp9bpy8jgsqet6q8z4vvirow"
                  v-model="form.content"
                  :init="{
                      height: 250,
                      menubar: false,
                      directionality: 'rtl',
                      plugins: [
                          'advlist autolink lists link image charmap print preview anchor',
                          'searchreplace visualblocks code fullscreen',
                          'insertdatetime media table paste code help wordcount directionality'
                      ],
                      toolbar:
                          'help | removeformat | ltr rtl | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify | bold italic backcolor | formatselect | undo redo'
                  }"
              />
              <p v-if="allErrors.content" class="text-red-500 text-xs mt-1">{{ allErrors.content }}</p>
            </div>

            <!-- Tags -->
            <div>
              <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">برچسب ها</label>
              <Multiselect
                  v-model="form.tags"
                  :options="tagOptions"
                  :multiple="true"
                  :taggable="true"
                  @tag="handleAddTag"
                  placeholder="برای سوال خود برچسب وارد کنید..."
                  label="name"
                  track-by="id"
                  :searchable="true"
                  :close-on-select="false"
                  :clear-on-select="false"
                  :preserve-search="true"
                  tag-placeholder="برای افزودن برچسب جدید اینتر بزنید"
                  @search-change="handleFetchTags"
              />
              <p v-if="allErrors.tags" class="text-red-500 text-xs mt-1">{{ allErrors.tags }}</p>
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
import Multiselect from 'vue-multiselect';
import { useQuestions, useCategories, useTags } from '../composables';
import { onMounted, watch, ref, computed } from 'vue';

export default {
  name: 'QuestionModal',
  components: {
    BaseInput,
    Editor,
    Multiselect
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
      await Promise.all([fetchCategories(), fetchTags()]);
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
  },
  methods: {
    async handleSubmitQuestion() {
      if (this.isSubmitting) return;
      this.clearAllErrors();

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
    async handleFetchCategories(query = '') {
      const result = await this.fetchCategories(query);
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
    },
    async handleFetchTags(query = '') {
      const result = await this.fetchTags(query);
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

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style lang="postcss">
.multiselect__tags {
    @apply w-full border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-700 dark:text-gray-300;
}
.multiselect__input {
    @apply bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300;
}
.multiselect__single {
    @apply bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300;
}
.multiselect__content-wrapper {
    @apply bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600;
}
.multiselect__option--highlight {
    @apply bg-blue-500;
}
.multiselect__option--selected {
    @apply bg-blue-600;
}
.multiselect__tag {
    @apply bg-blue-500;
}
.multiselect__tag-icon:hover {
    @apply bg-blue-600;
}
</style>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-25 z-40 flex items-center justify-center" @click.self="$emit('close')">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl mx-4 flex flex-col max-h-[90vh]" style="direction: rtl;">
      <div class="flex justify-between items-center border-b pb-3 p-6 flex-shrink-0">
        <h2 class="text-xl font-semibold">سوال خود را وارد کنید</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="overflow-y-auto p-6">
        <p class="text-gray-600 dark:text-gray-400 mb-6">مشخصات مربوط به سوال خود را در کادرهای زیر وارد کنید.</p>

        <form @submit.prevent="submitQuestion" id="ask-question-form">
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
                @search-change="fetchCategories"
              />
              <p v-if="errors.category" class="text-red-500 text-xs mt-1">{{ errors.category }}</p>
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
              <p v-if="errors.title" class="text-red-500 text-xs mt-1">{{ errors.title }}</p>
            </div>

            <!-- Body -->
            <div>
              <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شرح سوال</label>
              <Editor
                  api-key="2sfprbtijd268hiw733k56v9bp9bpy8jgsqet6q8z4vvirow"
                  v-model="form.body"
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
              <p v-if="errors.body" class="text-red-500 text-xs mt-1">{{ errors.body }}</p>
            </div>

            <!-- Tags -->
            <div>
              <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">برچسب ها</label>
              <Multiselect
                  v-model="form.tags"
                  :options="tagOptions"
                  :multiple="true"
                  :taggable="true"
                  @tag="addTag"
                  placeholder="برای سوال خود برچسب وارد کنید..."
                  label="name"
                  track-by="id"
                  @search-change="fetchTags"
              />
              <p v-if="errors.tags" class="text-red-500 text-xs mt-1">{{ errors.tags }}</p>
              <p class="text-xs text-gray-500 mt-1">مثال: سوالی درباره کود مناسب درختان نوشته اید پس برچسب ها میتواند (کود مناسب، تغذیه درختان، مواد غذایی برای درخت، کود برای رشد درخت، رشد بهتر درخت) باشد.</p>
            </div>
          </div>
        </form>
      </div>

      <div class="mt-auto flex justify-end p-6 border-t flex-shrink-0">
        <button type="submit" form="ask-question-form" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
          ثبت سوال
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import BaseInput from './ui/BaseInput.vue';

export default {
  name: 'AskQuestionModal',
  components: { BaseInput },
  emits: ['close'],
  data() {
    return {
      form: {
        category: null,
        title: '',
        body: '',
        tags: []
      },
      errors: {},
      categories: [], // Should be loaded from API
      tagOptions: []
    };
  },
  methods: {
    validateForm() {
        const newErrors = {};
        if (!this.form.category) {
            newErrors.category = 'دسته بندی الزامی است.';
        }
        if (!this.form.title.trim()) {
            newErrors.title = 'عنوان سوال الزامی است.';
        }
        if (!this.form.body.trim()) {
            newErrors.body = 'شرح سوال الزامی است.';
        }
        if (this.form.tags.length === 0) {
            newErrors.tags = 'حداقل یک برچسب الزامی است.';
        }
        this.errors = newErrors;
        return Object.keys(newErrors).length === 0;
    },
    async submitQuestion() {
      if (!this.validateForm()) {
        return;
      }

      try {
        const submissionData = {
          category_id: this.form.category ? this.form.category.id : null,
          title: this.form.title,
          body: this.form.body,
          tags: this.form.tags,
        };

        await this.$axios.post('/api/questions', submissionData);

        this.$emit('close');
        // Optionally, you can show a success notification here
        // and refresh the questions list.
      } catch (error) {
        if (error.response && error.response.status === 422) {
          // Handle validation errors
          this.errors = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
            acc[key] = value[0];
            return acc;
          }, {});
        } else {
          console.error('Error submitting question:', error);
          // Optionally, show a generic error message to the user
        }
      }
    },
    async fetchCategories(query = '') {
        try {
            const response = await this.$axios.get('/api/categories', {
                params: { query }
            });
            this.categories = response.data.data;
        } catch (error) {
            console.error('Error fetching categories:', error);
            // Optionally, show an error to the user
        }
    },
    async fetchTags(query = '') {
        try {
            const response = await this.$axios.get('/api/tags', {
                params: { query }
            });
            this.tagOptions = response.data.data;
        } catch (error) {
            console.error('Error fetching tags:', error);
        }
    },
    addTag (newTag) {
      const tag = {
        name: newTag,
        id: newTag // For new tags, we can use the name as a temporary ID
      };
      this.tagOptions.push(tag);
      this.form.tags.push(tag);
    }
  },
  mounted() {
      this.fetchCategories();
      this.fetchTags();
  }
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

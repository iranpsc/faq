<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center" @click.self="$emit('close')">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-3xl mx-4" style="direction: rtl;">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <h2 class="text-xl font-semibold">سوال خود را وارد کنید</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <p class="text-gray-600 dark:text-gray-400 mb-6">مشخصات مربوط به سوال خود را در کادرهای زیر وارد کنید.</p>

      <form @submit.prevent="submitQuestion">
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
            />
          </div>

          <!-- Title -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">عنوان سوال</label>
            <input type="text" id="title" v-model="form.title" placeholder="دیدگاه خود را وارد کنید..." class="w-full border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>

          <!-- Body -->
          <div>
            <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">شرح سوال</label>
            <Editor v-model="form.body" editorStyle="height: 150px" />
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
            />
            <p class="text-xs text-gray-500 mt-1">مثال: سوالی درباره کود مناسب درختان نوشته اید پس برچسب ها میتواند (کود مناسب، تغذیه درختان، مواد غذایی برای درخت، کود برای رشد درخت، رشد بهتر درخت) باشد.</p>
          </div>
        </div>

        <div class="mt-8 flex justify-end">
          <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
            ثبت سوال
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import Editor from 'primevue/editor';
import Multiselect from 'vue-multiselect';

export default {
  name: 'AskQuestionModal',
  components: {
    Editor,
    Multiselect
  },
  emits: ['close'],
  data() {
    return {
      form: {
        category: null,
        title: '',
        body: '',
        tags: []
      },
      categories: [], // Should be loaded from API
      tagOptions: []
    };
  },
  methods: {
    submitQuestion() {
      // Handle question submission logic
      const submissionData = {
        ...this.form,
        category_id: this.form.category ? this.form.category.id : null,
      };
      console.log(submissionData);
      this.$emit('close');
    },
    fetchCategories() {
        // Mock categories. In a real app, you'd fetch this from your backend.
        this.categories = [
            {id: 1, name: 'فناوری'},
            {id: 2, name: 'علمی'},
            {id: 3, name: 'برنامه نویسی'},
            {id: 4, name: 'زندگی روزمره'},
        ];
    },
    addTag (newTag) {
      this.tagOptions.push(newTag)
      this.form.tags.push(newTag)
    }
  },
  mounted() {
      this.fetchCategories();
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

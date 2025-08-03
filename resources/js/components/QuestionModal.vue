<template>
    <BaseModal :visible="true" :title="isEditMode ? 'ویرایش سوال' : 'سوال جدید'"
        subtitle="مشخصات مربوط به سوال خود را در کادرهای زیر وارد کنید." size="4xl" :closable="true"
        :close-on-backdrop="true" :close-on-escape="true" @close="$emit('close')">
        <div class="overflow-y-auto max-h-[60vh]" style="direction: rtl;">
            <form @submit.prevent="handleSubmitQuestion" id="question-form">
                <div class="space-y-6">
                    <!-- Category -->
                    <div>
                        <BaseSelect2 v-model="form.category" :options="categories" placeholder="انتخاب دسته بندی"
                            label="دسته بندی" option-label="name" track-by="id" :searchable="true" :paginated="true"
                            :page-size="10" :fetch-function="handleFetchCategories" :error="allErrors.category" />
                    </div>

                    <!-- Title -->
                    <div>
                        <BaseInput id="title" v-model="form.title" label="عنوان سوال"
                            placeholder="عنوان سوال خود را وارد کنید..." required />
                        <p v-if="allErrors.title" class="text-red-500 text-xs mt-1">{{ allErrors.title }}</p>
                    </div>

                    <!-- Content -->
                    <div>
                        <BaseEditor
                            id="content"
                            v-model="form.content"
                            label="شرح سوال"
                            mode="full"
                            :image-upload="true"
                            :error="allErrors.content"
                            :height="300"
                        />
                    </div>

                    <!-- Tags -->
                    <div>
                        <BaseSelect2 v-model="form.tags" :options="tagOptions" :multiple="true" :taggable="true"
                            @tag-add="handleAddTag" placeholder="برای سوال خود برچسب وارد کنید..." label="برچسب ها"
                            option-label="name" track-by="id" :searchable="true" :paginated="true" :page-size="10"
                            :fetch-function="handleFetchTags" :error="allErrors.tags" />
                        <p class="text-xs text-gray-500 mt-1">مثال: سوالی درباره کود مناسب درختان نوشته اید پس برچسب ها
                            میتواند (کود مناسب، تغذیه درختان، مواد غذایی برای درخت، کود برای رشد درخت، رشد بهتر درخت)
                            باشد.</p>
                    </div>
                </div>
            </form>
        </div>

        <template #footer>
            <button type="submit" form="question-form" :disabled="isSubmitting"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="isSubmitting">در حال {{ isEditMode ? 'ویرایش' : 'ثبت' }}...</span>
                <span v-else>{{ isEditMode ? 'ویرایش سوال' : 'ثبت سوال' }}</span>
            </button>
        </template>
    </BaseModal>
</template>

<script>
import BaseInput from './ui/BaseInput.vue';
import BaseModal from './ui/BaseModal.vue';
import BaseEditor from './ui/BaseEditor.vue';
import BaseSelect2 from './ui/BaseSelect2.vue';
import { useQuestions, useCategories, useTags } from '../composables';
import { onMounted, watch, ref, computed } from 'vue';

export default {
    name: 'QuestionModal',
    components: {
        BaseInput,
        BaseModal,
        BaseEditor,
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



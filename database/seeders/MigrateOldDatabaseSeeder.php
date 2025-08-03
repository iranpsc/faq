<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Tag;
use App\Models\Category;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\Vote;
use Exception;
use Illuminate\Support\Str;

class MigrateOldDatabaseSeeder extends Seeder
{
    protected $migrationLog = [];
    protected $defaultUserId = 1; // Default user ID for orphaned records
    protected $batchSize = 500; // Process records in batches
    protected $columnMappings = []; // Track column mappings for validation

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::disableQueryLog();

        $this->command->info('Starting migration of old database...');

        // Initialize column mappings
        $this->initializeColumnMappings();

        // Display migration statistics
        $this->displayMigrationStats();

        // Validate connection to old database
        try {
            DB::connection('faq_main')->getPdo();
        } catch (Exception $e) {
            $this->command->error('Cannot connect to faq_main database: ' . $e->getMessage());
            return;
        }

        try {
            DB::beginTransaction();

            $this->createDefaultUser();
            $this->migrateUsers();
            $this->migrateCategories();
            $this->migrateTags();
            $this->migrateQuestions();
            $this->migrateAnswers();
            $this->migrateQuestionTags();
            $this->migrateComments();
            $this->migrateVotes();

            DB::commit();
            $this->command->info('Migration completed successfully!');

            if (!empty($this->migrationLog)) {
                $this->command->warn('Some records had issues during migration:');
                foreach ($this->migrationLog as $log) {
                    $this->command->warn($log);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->command->error('Migration failed: ' . $e->getMessage());
            Log::error('Database migration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Initialize column mappings between old and new database structures
     */
    protected function initializeColumnMappings()
    {
        $this->columnMappings = [
            'users' => [
                'common' => ['id', 'name', 'email', 'email_verified_at', 'image', 'score', 'code', 'access_token', 'refresh_token', 'expires_in', 'token_type', 'created_at', 'updated_at'],
                'mapped' => [
                    'phone' => 'mobile', // phone -> mobile
                    'level_id' => 'level', // level_id -> level
                    'last_name' => null, // not imported - will concatenate with name
                ],
                'skipped' => ['password', 'citizen_code', 'remember_token'], // not compatible
            ],
            'categories' => [
                'common' => ['id', 'name', 'slug', 'created_at', 'updated_at'],
                'mapped' => [
                    'parent_id' => 'parent_id', // needs validation (varchar -> bigint)
                ],
                'skipped' => ['status', 'old_id'], // not compatible
            ],
            'questions' => [
                'common' => ['id', 'category_id', 'user_id', 'title', 'content', 'views', 'created_at', 'updated_at'],
                'mapped' => [
                    'is_pinned' => 'featured', // is_pinned -> featured
                    'status' => 'published', // status (1=published) -> published (boolean)
                ],
                'skipped' => ['slug', 'allow_comments', 'old_id'], // not compatible
            ],
            'answers' => [
                'common' => ['id', 'user_id', 'question_id', 'content', 'created_at', 'updated_at'],
                'mapped' => [
                    'is_correct_answer' => 'is_correct', // is_correct_answer -> is_correct
                    'is_accepted' => 'published', // is_accepted -> published
                ],
                'skipped' => ['parent_id', 'old_question_id', 'old_answer_id'], // not compatible
            ],
            'comments' => [
                'common' => ['id', 'commentable_id', 'commentable_type', 'content', 'created_at', 'updated_at'],
                'mapped' => [
                    'user_id' => 'user_id', // needs validation (varchar -> bigint)
                    'status' => 'published', // status ('approved') -> published (boolean)
                ],
                'skipped' => ['old_question_id'], // not compatible
            ],
            'votes' => [
                'common' => ['id', 'user_id', 'created_at', 'updated_at'],
                'mapped' => [
                    'voteable_id' => 'votable_id', // needs validation (varchar -> bigint)
                    'voteable_type' => 'votable_type', // same column, different naming
                    'vote_type' => 'type', // needs mapping (1='up', 0='down')
                ],
                'skipped' => [], // all columns are compatible
            ]
        ];
    }

    /**
     * Display migration statistics
     */
    protected function displayMigrationStats()
    {
        $this->command->info('=== Migration Statistics ===');

        $stats = [
            'users' => DB::connection('faq_main')->table('users')->count(),
            'categories' => DB::connection('faq_main')->table('categories')->count(),
            'tags' => DB::connection('faq_main')->table('tags')->count(),
            'questions' => DB::connection('faq_main')->table('questions')->count(),
            'answers' => DB::connection('faq_main')->table('answers')->count(),
            'question_tags' => DB::connection('faq_main')->table('question_tags')->count(),
            'comments' => DB::connection('faq_main')->table('comments')->count(),
            'votes' => DB::connection('faq_main')->table('votes')->count(),
        ];

        foreach ($stats as $table => $count) {
            $this->command->info("{$table}: {$count} records");
        }

        $this->command->info('============================');

        // Display column mapping information
        $this->displayColumnMappings();
    }

    /**
     * Display column mappings and compatibility information
     */
    protected function displayColumnMappings()
    {
        $this->command->info('=== Column Mappings & Compatibility ===');

        foreach ($this->columnMappings as $table => $mapping) {
            $this->command->info("Table: {$table}");

            if (!empty($mapping['common'])) {
                $this->command->info("  Common columns: " . implode(', ', $mapping['common']));
            }

            if (!empty($mapping['mapped'])) {
                $this->command->info("  Mapped columns:");
                foreach ($mapping['mapped'] as $old => $new) {
                    if ($new) {
                        $this->command->info("    {$old} -> {$new}");
                    } else {
                        $this->command->info("    {$old} -> (concatenated/processed)");
                    }
                }
            }

            if (!empty($mapping['skipped'])) {
                $this->command->warn("  Skipped columns: " . implode(', ', $mapping['skipped']));
            }

            $this->command->info('');
        }

        $this->command->info('=======================================');
    }

    /**
     * Create a default user for orphaned records
     */
    protected function createDefaultUser()
    {
        $this->command->info('Creating default user for orphaned records...');

        User::updateOrCreate(
            ['id' => $this->defaultUserId],
            [
                'name' => 'حسین قدیری',
                'email' => 'hosseinqadiri69@gmail.com',
                'email_verified_at' => now(),
                'mobile' => null,
                'level' => 1,
                'code' => '0',
                'role' => 'admin',
                'score' => 0,
                'image' => null,
                'access_token' => null,
                'refresh_token' => null,
                'expires_in' => null,
                'token_type' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    protected function migrateUsers()
    {
        $this->command->info('Migrating users...');
        $old_users = DB::connection('faq_main')->table('users')->get();
        $count = 0;
        $usersFile = file_get_contents(storage_path('app/private/users.json'));
        $usersData = json_decode($usersFile, true);

        // Create email to display_name mapping from JSON data
        $emailToDisplayName = [];
        if (isset($usersData[2]['data']) && is_array($usersData[2]['data'])) {
            foreach ($usersData[2]['data'] as $jsonUser) {
                if (isset($jsonUser['user_email']) && isset($jsonUser['display_name']) &&
                    !empty($jsonUser['user_email']) && !empty($jsonUser['display_name'])) {
                    $emailToDisplayName[$jsonUser['user_email']] = $jsonUser['display_name'];
                }
            }
        }

        foreach ($old_users as $old_user) {
            try {
                // Skip user with ID 1 if it already exists (our default user)
                if ($old_user->id == $this->defaultUserId && User::find($this->defaultUserId)) {
                    continue;
                }

                // Validate level_id
                $level = is_numeric($old_user->level_id ?? 1) ? (int)$old_user->level_id : 1;
                if ($level < 1) $level = 1;

                // Get correct name from JSON file if email matches, otherwise concatenate name with last_name
                $fullName = '';
                if (!empty($old_user->email) && isset($emailToDisplayName[$old_user->email])) {
                    $fullName = $emailToDisplayName[$old_user->email];
                } else {
                    $fullName = trim($old_user->name . ($old_user->last_name ? ' ' . $old_user->last_name : ''));
                }

                // Validate email
                if (!filter_var($old_user->email, FILTER_VALIDATE_EMAIL)) {
                    $this->migrationLog[] = "Skipping user {$old_user->id}: Invalid email format";
                    continue;
                }

                User::updateOrCreate(
                    ['id' => $old_user->id],
                    [
                        'name' => $fullName,
                        'email' => $old_user->email,
                        'email_verified_at' => $old_user->email_verified_at,
                        'mobile' => $old_user->phone ?? null,
                        'level' => $level,
                        'code' => $old_user->code ?? '0',
                        'role' => 'user', // Default role
                        'score' => is_numeric($old_user->score) ? (int)$old_user->score : 0,
                        'image' => $old_user->image,
                        'access_token' => $old_user->access_token,
                        'refresh_token' => $old_user->refresh_token,
                        'expires_in' => $old_user->expires_in,
                        'token_type' => $old_user->token_type,
                        'created_at' => $old_user->created_at,
                        'updated_at' => $old_user->updated_at,
                    ]
                );
                $count++;

                // Log skipped columns
                $skipped = [];
                if (isset($old_user->password)) $skipped[] = 'password';
                if (isset($old_user->citizen_code)) $skipped[] = 'citizen_code';
                if (isset($old_user->remember_token)) $skipped[] = 'remember_token';

                if (!empty($skipped)) {
                    $this->migrationLog[] = "User {$old_user->id}: Skipped columns: " . implode(', ', $skipped) . " (not compatible with new schema)";
                }
            } catch (Exception $e) {
                $this->migrationLog[] = "Failed to migrate user {$old_user->id}: " . $e->getMessage();
            }
        }

        $this->command->info("Migrated {$count} users");
    }

    protected function migrateCategories()
    {
        $this->command->info('Migrating categories...');
        $old_categories = DB::connection('faq_main')->table('categories')->orderBy('parent_id', 'asc')->get();
        $count = 0;

        foreach ($old_categories as $old_category) {
            try {
                $activity = DB::connection('faq_main')
                    ->table('categories_activity')
                    ->where('category_id', $old_category->id)
                    ->first();

                // Handle parent_id conversion - old database uses varchar, validate if numeric
                $parentId = null;
                if ($old_category->parent_id && $old_category->parent_id !== '0' && is_numeric($old_category->parent_id)) {
                    $parentId = (int) $old_category->parent_id;
                    // Validate parent exists
                    if ($parentId && !Category::find($parentId)) {
                        $this->migrationLog[] = "Category {$old_category->id}: Parent category {$parentId} does not exist, setting parent to null";
                        $parentId = null;
                    }
                }

                Category::updateOrCreate(
                    ['id' => $old_category->id],
                    [
                        'name' => $old_category->name,
                        'slug' => str_replace(' ', '-', $old_category->name), // Generate slug from name
                        'parent_id' => $parentId,
                        'last_activity' => $activity ? $activity->last_activity : null,
                        'created_at' => $old_category->created_at,
                        'updated_at' => $old_category->updated_at,
                    ]
                );
                $count++;

                // Log skipped columns
                if (isset($old_category->status) || isset($old_category->old_id)) {
                    $this->migrationLog[] = "Category {$old_category->id}: Skipped columns: status, old_id (not compatible with new schema)";
                }
            } catch (Exception $e) {
                $this->migrationLog[] = "Failed to migrate category {$old_category->id}: " . $e->getMessage();
            }
        }

        $this->command->info("Migrated {$count} categories");
    }

    protected function migrateTags()
    {
        $this->command->info('Migrating tags...');
        $old_tags = DB::connection('faq_main')->table('tags')->get();
        $count = 0;

        foreach ($old_tags as $old_tag) {
            try {
                Tag::updateOrCreate(
                    ['id' => $old_tag->id],
                    [
                        'name' => $old_tag->name,
                        'slug' => str_replace(' ', '-', $old_tag->name), // Generate slug from name
                        'created_at' => $old_tag->created_at,
                        'updated_at' => $old_tag->updated_at,
                    ]
                );
                $count++;
            } catch (Exception $e) {
                $this->migrationLog[] = "Failed to migrate tag {$old_tag->id}: " . $e->getMessage();
            }
        }

        $this->command->info("Migrated {$count} tags");
    }

    protected function migrateQuestions()
    {
        $this->command->info('Migrating questions...');
        $old_questions = DB::connection('faq_main')->table('questions')->get();
        $count = 0;

        foreach ($old_questions as $old_question) {
            try {
                $activity = DB::connection('faq_main')
                    ->table('questions_activity')
                    ->where('question_id', $old_question->id)
                    ->first();

                // Handle user_id = 0 case
                $userId = $old_question->user_id == 0 ? $this->defaultUserId : $old_question->user_id;

                // Ensure user exists
                if (!User::find($userId)) {
                    $userId = $this->defaultUserId;
                }

                // Ensure category exists
                if (!Category::find($old_question->category_id)) {
                    $this->migrationLog[] = "Skipping question {$old_question->id}: Category {$old_question->category_id} does not exist";
                    continue;
                }

                // Map status to published (1 = published, others = unpublished)
                $published = (int) $old_question->status === 1;

                // Map is_pinned to featured
                $featured = (bool) ($old_question->is_pinned ?? false);

                Question::updateOrCreate(
                    ['id' => $old_question->id],
                    [
                        'category_id' => $old_question->category_id,
                        'user_id' => $userId,
                        'title' => $old_question->title,
                        'content' => $old_question->content,
                        'featured' => $featured,
                        'views' => $old_question->views ?? 0,
                        'published' => $published,
                        'published_at' => $published ? $old_question->created_at : null,
                        'published_by' => $userId,
                        'last_activity' => $activity ? $activity->last_activity : $old_question->updated_at,
                        'created_at' => $old_question->created_at,
                        'updated_at' => $old_question->updated_at,
                    ]
                );
                $count++;

                // Log skipped columns
                $skipped = [];
                if (isset($old_question->slug)) $skipped[] = 'slug';
                if (isset($old_question->allow_comments)) $skipped[] = 'allow_comments';
                if (isset($old_question->old_id)) $skipped[] = 'old_id';

                if (!empty($skipped)) {
                    $this->migrationLog[] = "Question {$old_question->id}: Skipped columns: " . implode(', ', $skipped) . " (not compatible with new schema)";
                }
            } catch (Exception $e) {
                $this->migrationLog[] = "Failed to migrate question {$old_question->id}: " . $e->getMessage();
            }
        }

        $this->command->info("Migrated {$count} questions");
    }

    protected function migrateAnswers()
    {
        $this->command->info('Migrating answers...');
        $old_answers = DB::connection('faq_main')->table('answers')->get();
        $count = 0;

        foreach ($old_answers as $old_answer) {
            try {
                // Handle user_id = 0 case
                $userId = $old_answer->user_id == 0 ? $this->defaultUserId : $old_answer->user_id;

                // Ensure user exists
                if (!User::find($userId)) {
                    $userId = $this->defaultUserId;
                }

                // Ensure question exists
                if (!Question::find($old_answer->question_id)) {
                    $this->migrationLog[] = "Skipping answer {$old_answer->id}: Question {$old_answer->question_id} does not exist";
                    continue;
                }

                // Map is_accepted to published
                $published = (bool) ($old_answer->is_accepted ?? false);

                // Map is_correct_answer to is_correct
                $isCorrect = (bool) ($old_answer->is_correct_answer ?? false);

                Answer::updateOrCreate(
                    ['id' => $old_answer->id],
                    [
                        'question_id' => $old_answer->question_id,
                        'user_id' => $userId,
                        'content' => $old_answer->content,
                        'is_correct' => $isCorrect,
                        'published' => $published,
                        'published_at' => $published ? $old_answer->created_at : null,
                        'published_by' => $userId,
                        'created_at' => $old_answer->created_at,
                        'updated_at' => $old_answer->updated_at,
                    ]
                );
                $count++;

                // Log skipped columns
                $skipped = [];
                if (isset($old_answer->parent_id) && $old_answer->parent_id != 0) $skipped[] = 'parent_id';
                if (isset($old_answer->old_question_id)) $skipped[] = 'old_question_id';
                if (isset($old_answer->old_answer_id)) $skipped[] = 'old_answer_id';

                if (!empty($skipped)) {
                    $this->migrationLog[] = "Answer {$old_answer->id}: Skipped columns: " . implode(', ', $skipped) . " (not compatible with new schema)";
                }
            } catch (Exception $e) {
                $this->migrationLog[] = "Failed to migrate answer {$old_answer->id}: " . $e->getMessage();
            }
        }

        $this->command->info("Migrated {$count} answers");
    }

    protected function migrateQuestionTags()
    {
        $this->command->info('Migrating question tags...');
        $old_question_tags = DB::connection('faq_main')->table('question_tags')->get();
        $count = 0;

        foreach ($old_question_tags as $old_question_tag) {
            try {
                // Validate that question and tag exist
                if (!Question::find($old_question_tag->question_id)) {
                    $this->migrationLog[] = "Skipping question_tag: Question {$old_question_tag->question_id} does not exist";
                    continue;
                }

                if (!Tag::find($old_question_tag->tag_id)) {
                    $this->migrationLog[] = "Skipping question_tag: Tag {$old_question_tag->tag_id} does not exist";
                    continue;
                }

                DB::table('question_tag')->updateOrInsert(
                    [
                        'question_id' => $old_question_tag->question_id,
                        'tag_id' => $old_question_tag->tag_id
                    ],
                    [
                        'created_at' => $old_question_tag->created_at,
                        'updated_at' => $old_question_tag->updated_at,
                    ]
                );
                $count++;
            } catch (Exception $e) {
                $this->migrationLog[] = "Failed to migrate question_tag for question {$old_question_tag->question_id} and tag {$old_question_tag->tag_id}: " . $e->getMessage();
            }
        }

        $this->command->info("Migrated {$count} question tags");
    }

    protected function migrateComments()
    {
        $this->command->info('Migrating comments...');
        $old_comments = DB::connection('faq_main')->table('comments')->get();
        $count = 0;

        foreach ($old_comments as $old_comment) {
            try {
                // Handle user_id as varchar - validate if numeric
                $userId = is_numeric($old_comment->user_id) ? (int) $old_comment->user_id : $this->defaultUserId;
                if ($userId == 0) $userId = $this->defaultUserId;

                // Ensure user exists
                if (!User::find($userId)) {
                    $userId = $this->defaultUserId;
                }

                // Validate commentable_id as numeric
                if (!is_numeric($old_comment->commentable_id)) {
                    $this->migrationLog[] = "Skipping comment {$old_comment->id}: Invalid commentable_id format";
                    continue;
                }

                $commentableId = (int) $old_comment->commentable_id;
                $commentableType = $old_comment->commentable_type;

                // Validate that the commentable model exists
                if ($commentableType === 'App\Models\Question' && !Question::find($commentableId)) {
                    $this->migrationLog[] = "Skipping comment {$old_comment->id}: Question {$commentableId} does not exist";
                    continue;
                }

                if ($commentableType === 'App\Models\Answer' && !Answer::find($commentableId)) {
                    $this->migrationLog[] = "Skipping comment {$old_comment->id}: Answer {$commentableId} does not exist";
                    continue;
                }

                // Map status to published (approved = true)
                $published = ($old_comment->status === 'approved');

                Comment::updateOrCreate(
                    ['id' => $old_comment->id],
                    [
                        'user_id' => $userId,
                        'commentable_type' => $commentableType,
                        'commentable_id' => $commentableId,
                        'content' => $old_comment->content,
                        'published' => $published,
                        'published_at' => $published ? $old_comment->created_at : null,
                        'published_by' => $userId,
                        'created_at' => $old_comment->created_at,
                        'updated_at' => $old_comment->updated_at,
                    ]
                );
                $count++;

                // Log skipped columns
                if (isset($old_comment->old_question_id)) {
                    $this->migrationLog[] = "Comment {$old_comment->id}: Skipped column: old_question_id (not compatible with new schema)";
                }
            } catch (Exception $e) {
                $this->migrationLog[] = "Failed to migrate comment {$old_comment->id}: " . $e->getMessage();
            }
        }

        $this->command->info("Migrated {$count} comments");
    }

    protected function migrateVotes()
    {
        $this->command->info('Migrating votes...');
        $old_votes = DB::connection('faq_main')->table('votes')->get();
        $count = 0;

        foreach ($old_votes as $old_vote) {
            try {
                // Handle user_id = 0 case
                $userId = $old_vote->user_id == 0 ? $this->defaultUserId : $old_vote->user_id;

                // Ensure user exists
                if (!User::find($userId)) {
                    $userId = $this->defaultUserId;
                }

                // Validate voteable_id as numeric (old uses varchar)
                if (!is_numeric($old_vote->voteable_id)) {
                    $this->migrationLog[] = "Skipping vote {$old_vote->id}: Invalid voteable_id format";
                    continue;
                }

                $voteableId = (int) $old_vote->voteable_id;
                $voteableType = $old_vote->voteable_type;

                // Validate that the voteable model exists
                if ($voteableType === 'App\Models\Question' && !Question::find($voteableId)) {
                    $this->migrationLog[] = "Skipping vote {$old_vote->id}: Question {$voteableId} does not exist";
                    continue;
                }

                if ($voteableType === 'App\Models\Answer' && !Answer::find($voteableId)) {
                    $this->migrationLog[] = "Skipping vote {$old_vote->id}: Answer {$voteableId} does not exist";
                    continue;
                }

                // Map vote_type: various formats in old database to 'up'/'down'
                $voteType = 'up'; // default
                if (isset($old_vote->vote_type)) {
                    if ($old_vote->vote_type == '0' || $old_vote->vote_type === 0 || strtolower($old_vote->vote_type) === 'down') {
                        $voteType = 'down';
                    } elseif ($old_vote->vote_type == '1' || $old_vote->vote_type === 1 || strtolower($old_vote->vote_type) === 'up') {
                        $voteType = 'up';
                    }
                }

                Vote::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'votable_type' => $voteableType,
                        'votable_id' => $voteableId,
                    ],
                    [
                        'type' => $voteType,
                        'created_at' => $old_vote->created_at,
                        'updated_at' => $old_vote->updated_at,
                    ]
                );
                $count++;
            } catch (Exception $e) {
                $this->migrationLog[] = "Failed to migrate vote {$old_vote->id}: " . $e->getMessage();
            }
        }

        $this->command->info("Migrated {$count} votes");
    }
}

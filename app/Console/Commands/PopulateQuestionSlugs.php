<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

class PopulateQuestionSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questions:populate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate slugs for all questions based on their titles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to populate question slugs...');

        $totalQuestions = Question::count();
        $this->info("Found {$totalQuestions} questions.");

        $bar = $this->output->createProgressBar($totalQuestions);
        $bar->start();

        $processed = 0;

        // Use chunk to process questions in batches for memory efficiency
        Question::chunk(100, function ($questions) use ($bar, &$processed) {
            foreach ($questions as $question) {
                try {
                    $slug = Question::generateSlug($question->title);
                    $question->update(['slug' => $slug]);
                    $processed++;
                    $bar->advance();
                } catch (\Exception $e) {
                    $this->error("Error processing question ID {$question->id}: " . $e->getMessage());
                }
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Successfully processed {$processed} questions.");
    }
}

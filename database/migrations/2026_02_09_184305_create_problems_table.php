<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('platform_id')->constrained()->onDelete('cascade');
            $table->foreignId('contest_id')->nullable()->constrained()->onDelete('set null');

            // Problem Identification
            $table->string('platform_problem_id', 150)->comment('Unique problem ID from platform');
            $table->string('slug', 255)->nullable()->unique()->comment('URL-friendly problem identifier');
            $table->string('name', 255);
            $table->string('code', 50)->nullable()->comment('Problem code (e.g., A, B, C for contests)');

            // Problem Details
            $table->text('description')->nullable();
            $table->string('difficulty', 50)->nullable();
            $table->integer('rating')->nullable()->comment('Problem rating/difficulty score');
            $table->float('points', 8, 2)->nullable()->comment('Points awarded for solving');
            $table->float('accuracy', 5, 2)->nullable()->comment('Success rate percentage');

            // Constraints
            $table->integer('time_limit_ms')->nullable()->comment('Time limit in milliseconds');
            $table->integer('memory_limit_mb')->nullable()->comment('Memory limit in MB');

            // Statistics
            $table->integer('total_submissions')->default(0);
            $table->integer('accepted_submissions')->default(0);
            $table->integer('solved_count')->default(0)->comment('Number of users who solved it');

            // Categorization
            $table->json('tags')->nullable()->comment('Problem tags (e.g., dp, graphs, math)');
            $table->json('topics')->nullable()->comment('Problem topics/categories');

            // Additional Information
            $table->string('url', 500);
            $table->string('editorial_url', 500)->nullable();
            $table->json('raw')->nullable()->comment('Raw data from platform API');

            // Status
            $table->string('status', 50)->default('active')->comment('active, inactive, archived');
            $table->boolean('is_premium')->default(false);

            // Indexes
            $table->unique(['platform_id', 'platform_problem_id'], 'unique_platform_problem');
            $table->index('platform_id');
            $table->index('contest_id');
            $table->index('difficulty');
            $table->index('rating');
            $table->index('status');
            $table->index(['platform_id', 'difficulty'], 'platform_difficulty_index');
            $table->index(['platform_id', 'rating'], 'platform_rating_index');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};

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
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('display_name', 100);
            $table->string('base_url')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->dateTime('last_contest_sync_at')->nullable()->comment('Last time contests were synced');
            $table->dateTime('last_problem_sync_at')->nullable()->comment('Last time problems were synced');
            $table->integer('contest_sync_count')->default(0)->comment('Total contests synced');
            $table->integer('problem_sync_count')->default(0)->comment('Total problems synced');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};

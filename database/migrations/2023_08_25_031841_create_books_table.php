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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('page_number')->default(0);
            $table->longText('synopsis');
            $table->year('publication_year');
            $table->string('publisher');
            $table->string('language');
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->integer('availability')->default(1)->comment('0 => borrowed, 1 => available, 2 => lost, 3 => broken');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

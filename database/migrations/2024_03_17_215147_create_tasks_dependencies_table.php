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
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            // $table->integer('task_id');
            $table->foreignId('task_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('dependency_id')->constrained('tasks','id')->onUpdate('cascade')->onDelete('cascade');
            // $table->integer('dependency_id');
            $table->timestamps();
            $table->unique(['task_id', 'dependency_id']);
            $table->unique(['dependency_id', 'task_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
    }
};

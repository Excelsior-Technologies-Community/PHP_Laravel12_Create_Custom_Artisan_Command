<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('command_logs', function (Blueprint $table) {
            $table->id();

            $table->string('command');

            $table->text('arguments')->nullable();

            $table->text('options')->nullable();

            $table->integer('exit_code')->default(0);

            $table->string('status')->default('success');

            $table->longText('output')->nullable();

            $table->longText('error')->nullable();

            $table->decimal('duration', 10, 3)->nullable();

            $table->timestamp('executed_at')->nullable();

            $table->timestamps();

            $table->index('command');
            $table->index('status');
            $table->index('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('command_logs');
    }
};
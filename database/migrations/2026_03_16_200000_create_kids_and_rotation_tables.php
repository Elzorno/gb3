<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kids', function (Blueprint $table): void {
            $table->id();
            $table->string('display_name')->unique();
            $table->string('pin_hash')->default('');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('chore_slots', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('rotation_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('default');
            $table->text('kids_json');
            $table->text('slots_json');
            $table->date('anchor_monday');
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table): void {
            $table->date('day');
            $table->foreignId('kid_id')->constrained('kids')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('chore_slots')->cascadeOnDelete();
            $table->string('status')->default('open');
            $table->unsignedBigInteger('submission_id')->nullable();
            $table->timestamps();

            $table->primary(['day', 'kid_id']);
            $table->index(['day', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('rotation_rules');
        Schema::dropIfExists('chore_slots');
        Schema::dropIfExists('kids');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bonus_defs', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->boolean('active')->default(true);
            $table->integer('reward_cents')->default(0);
            $table->integer('reward_phone_min')->default(0);
            $table->integer('reward_games_min')->default(0);
            $table->integer('max_per_week')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('bonus_instances', function (Blueprint $table): void {
            $table->id();
            $table->date('week_start');
            $table->foreignId('bonus_def_id')->constrained('bonus_defs')->cascadeOnDelete();
            $table->string('status')->default('available'); // available|claimed|pending|approved|rejected
            $table->foreignId('claimed_by_kid_id')->nullable()->constrained('kids')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->unsignedBigInteger('submission_id')->nullable();
            $table->timestamps();

            $table->unique(['week_start', 'bonus_def_id']);
            $table->index(['week_start', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_instances');
        Schema::dropIfExists('bonus_defs');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payout_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kid_id')->constrained('kids')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, approved, denied
            $table->integer('requested_cents')->default(0);
            $table->integer('requested_phone_min')->default(0);
            $table->integer('requested_games_min')->default(0);
            $table->integer('requested_other_min')->default(0);
            $table->timestamp('requested_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->string('reviewed_by_actor_type', 20)->nullable();
            $table->unsignedBigInteger('reviewed_by_actor_id')->default(0);
            $table->text('review_note')->nullable();

            $table->index(['kid_id', 'status']);
            $table->index(['status', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};

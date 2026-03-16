<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('privileges', function (Blueprint $table): void {
            $table->foreignId('kid_id')->primary()->constrained('kids')->cascadeOnDelete();
            $table->boolean('phone_locked')->default(false);
            $table->boolean('games_locked')->default(false);
            $table->boolean('other_locked')->default(false);
            $table->integer('bank_phone_min')->default(0);
            $table->integer('bank_games_min')->default(0);
            $table->integer('bank_other_min')->default(0);
            $table->timestamp('phone_locked_until')->nullable();
            $table->timestamp('games_locked_until')->nullable();
            $table->timestamp('other_locked_until')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('infraction_defs', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->boolean('active')->default(true);
            $table->string('mode')->default('set'); // set|add
            $table->unsignedInteger('days')->default(0);
            $table->text('ladder_json')->default('[]');
            $table->text('blocks_json')->default('{}');
            $table->text('repairs_json')->default('[]');
            $table->unsignedInteger('review_days')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['active', 'sort_order']);
        });

        Schema::create('infraction_strikes', function (Blueprint $table): void {
            $table->foreignId('kid_id')->constrained('kids')->cascadeOnDelete();
            $table->foreignId('infraction_def_id')->constrained('infraction_defs')->cascadeOnDelete();
            $table->unsignedInteger('strike_count')->default(0);
            $table->timestamp('updated_at')->nullable();

            $table->primary(['kid_id', 'infraction_def_id']);
        });

        Schema::create('infraction_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kid_id')->constrained('kids')->cascadeOnDelete();
            $table->foreignId('infraction_def_id')->constrained('infraction_defs')->cascadeOnDelete();
            $table->timestamp('ts');
            $table->string('actor_type');
            $table->unsignedBigInteger('actor_id')->default(0);
            $table->unsignedInteger('strike_before')->default(0);
            $table->unsignedInteger('strike_after')->default(0);
            $table->unsignedInteger('days_applied')->default(0);
            $table->string('mode');
            $table->text('blocks_json');
            $table->text('computed_until_json');
            $table->date('review_on')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('reviewed_by_actor_type')->nullable();
            $table->unsignedBigInteger('reviewed_by_actor_id')->default(0);
            $table->text('review_note')->nullable();
            $table->string('review_action')->nullable();
            $table->text('review_resolved_until_json')->default('{}');

            $table->index(['kid_id', 'ts']);
            $table->index(['review_on', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infraction_events');
        Schema::dropIfExists('infraction_strikes');
        Schema::dropIfExists('infraction_defs');
        Schema::dropIfExists('privileges');
    }
};

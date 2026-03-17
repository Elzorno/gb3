<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('privileges', function (Blueprint $table): void {
            $table->integer('bank_cents')->default(0)->after('bank_other_min');
        });

        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kid_id')->constrained('kids')->cascadeOnDelete();
            $table->string('type'); // credit, debit, adjustment
            $table->string('source'); // bonus_approved, infraction, manual, etc.
            $table->unsignedBigInteger('source_id')->nullable(); // submission_id, event_id, etc.
            $table->integer('cents')->default(0);
            $table->integer('phone_min')->default(0);
            $table->integer('games_min')->default(0);
            $table->integer('other_min')->default(0);
            $table->text('note')->nullable();
            $table->string('actor_type')->default('system'); // system, admin
            $table->unsignedBigInteger('actor_id')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['kid_id', 'created_at']);
            $table->index(['source', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');

        if (Schema::hasColumn('privileges', 'bank_cents')) {
            Schema::table('privileges', function (Blueprint $table): void {
                $table->dropColumn('bank_cents');
            });
        }
    }
};

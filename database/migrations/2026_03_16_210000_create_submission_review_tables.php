<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table): void {
            $table->id();
            $table->string('kind'); // base|bonus
            $table->date('day')->nullable();
            $table->date('week_start')->nullable();
            $table->foreignId('kid_id')->constrained('kids')->cascadeOnDelete();
            $table->foreignId('slot_id')->nullable()->constrained('chore_slots')->nullOnDelete();
            $table->unsignedBigInteger('bonus_instance_id')->nullable();
            $table->string('proof_path');
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by_admin_id')->default(0);
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'submitted_at']);
            $table->index(['kid_id', 'status']);
        });

        // Use conditional add because assignments table already exists from earlier migration.
        if (Schema::hasTable('assignments') && !Schema::hasColumn('assignments', 'submission_id')) {
            Schema::table('assignments', function (Blueprint $table): void {
                $table->unsignedBigInteger('submission_id')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'submission_id')) {
            Schema::table('assignments', function (Blueprint $table): void {
                $table->dropColumn('submission_id');
            });
        }

        Schema::dropIfExists('submissions');
    }
};

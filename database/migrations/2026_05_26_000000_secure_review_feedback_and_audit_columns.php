<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table): void {
            $table->text('kid_note')->nullable()->after('review_note');
            $table->text('admin_note')->nullable()->after('kid_note');
            $table->string('reviewed_by_session_key', 64)->nullable()->after('reviewed_by_admin_id');
        });

        Schema::table('payout_requests', function (Blueprint $table): void {
            $table->string('reviewed_by_session_key', 64)->nullable()->after('reviewed_by_actor_id');
        });

        DB::table('submissions')
            ->whereNotNull('review_note')
            ->whereNull('kid_note')
            ->update(['kid_note' => DB::raw('review_note')]);
    }

    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table): void {
            $table->dropColumn('reviewed_by_session_key');
        });

        Schema::table('submissions', function (Blueprint $table): void {
            $table->dropColumn(['kid_note', 'admin_note', 'reviewed_by_session_key']);
        });
    }
};

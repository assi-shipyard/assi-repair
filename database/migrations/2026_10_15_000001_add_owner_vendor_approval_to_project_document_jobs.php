<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_document_jobs', function (Blueprint $table): void {
            $table->string('owner_vendor_approval_status', 20)->default('not_required')->after('responsible_kind');
            $table->foreignId('owner_vendor_decided_by_user_id')->nullable()->after('owner_vendor_approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('owner_vendor_decided_at')->nullable()->after('owner_vendor_decided_by_user_id');
            $table->text('owner_vendor_decision_note')->nullable()->after('owner_vendor_decided_at');
            $table->decimal('owner_vendor_surcharge', 18, 2)->default(0)->after('est_price');
        });
    }

    public function down(): void
    {
        Schema::table('project_document_jobs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_vendor_decided_by_user_id');
            $table->dropColumn([
                'owner_vendor_approval_status',
                'owner_vendor_decided_at',
                'owner_vendor_decision_note',
                'owner_vendor_surcharge',
            ]);
        });
    }
};

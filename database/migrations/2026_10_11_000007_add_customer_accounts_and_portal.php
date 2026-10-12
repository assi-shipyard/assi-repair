<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('employee_id', 64)->nullable()->change();
            $table->uuid('unique_id')->nullable()->unique();
            $table->string('account_type', 20)->default('internal')->index();
            $table->string('username', 64)->nullable()->unique();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->foreignId('company_id')->nullable()->constrained('companies')->restrictOnDelete();
        });

        DB::table('users')->orderBy('id')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update(['unique_id' => (string) Str::uuid()]);
            }
        });

        $admin_ids = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'admin')
            ->where('model_has_roles.model_type', User::class)
            ->select('model_has_roles.model_id');

        DB::table('users')->whereIn('id', $admin_ids)->update([
            'account_type' => 'admin',
            'username' => DB::raw('employee_id'),
        ]);

        Schema::create('customer_project_access', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->uuid('access_version');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('view_prices')->default(false);
            $table->boolean('approve_initial_boq')->default(false);
            $table->boolean('approve_satisfaction_notes')->default(false);
            $table->boolean('approve_final_boq')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'project_id']);
        });

        Schema::create('customer_documents', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('project_job_document_id')->unique()->constrained('project_job_documents')->restrictOnDelete();
            $table->foreignId('approver_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('published_by')->constrained('users')->restrictOnDelete();
            $table->string('document_type', 50);
            $table->unsignedInteger('revision_no');
            $table->json('public_snapshot');
            $table->text('financial_snapshot');
            $table->string('status', 30)->default('pending');
            $table->text('decision_comment')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['project_id', 'document_type', 'status']);
        });

        Schema::create('customer_feedback', function (Blueprint $table): void {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('kind', 20);
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('message');
            $table->string('status', 20)->default('submitted');
            $table->text('response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'user_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_feedback');
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('customer_project_access');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('company_id');
            $table->dropUnique(['unique_id']);
            $table->dropUnique(['username']);
            $table->dropIndex(['account_type']);
            $table->dropColumn(['unique_id', 'account_type', 'username', 'name', 'is_active', 'must_change_password']);
        });
    }
};

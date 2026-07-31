<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Project model.
 *
 * Key concept:
 * - `id` is the database primary key.
 * - `unique_id` is a UUID used by routes and external references.
 */
class Project extends Model
{
	protected $table = 'projects';

	protected $fillable = [
		'unique_id',
		'project_code',
		'ship_id',
		'project_leader_employee_id',
		'project_ppc_employee_id',
		'project_type',
		'start_date_estimation',
		'end_date_estimation',
		'start_date_actual',
		'end_date_actual',
		'progress',
		'status',
		'comment',
		'created_by'
	];

	protected $casts = [
		'start_date_estimation' => 'date',
		'end_date_estimation' => 'date',
		'start_date_actual' => 'date',
		'end_date_actual' => 'date',
		'progress' => 'decimal:2',
	];

	protected static function booted(): void
	{
		// Ensure every new project has UUID for public-facing identifiers.
		static::creating(function (Project $project): void {
			if (empty($project->unique_id)) {
				$project->unique_id = (string) Str::uuid();
			}
		});
	}

	public function ship(): BelongsTo
	{
		return $this->belongsTo(Ship::class, 'ship_id');
	}

	public function leader(): BelongsTo
	{
		return $this->belongsTo(Employee::class, 'project_leader_employee_id');
	}

	public function ppc(): BelongsTo
	{
		return $this->belongsTo(Employee::class, 'project_ppc_employee_id');
	}

	public function divisions(): BelongsToMany
	{
		return $this->belongsToMany(OrganizationalUnit::class, 'project_divisions')
			->withTimestamps();
	}

	public function owner_surveyors(): HasMany
	{
		return $this->hasMany(ProjectOwnerSurveyor::class, 'project_id');
	}

	public function created_by(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function job_documents(): HasMany
	{
		return $this->hasMany(ProjectJobDocument::class, 'project_id');
	}
}

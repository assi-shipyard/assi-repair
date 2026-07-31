<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectOwnerSurveyor extends Model
{
	protected $table = 'project_owner_surveyors';

	protected $fillable = [
		'project_id',
		'name',
		'company',
		'position',
		'email',
		'phone',
	];

	public function project(): BelongsTo
	{
		return $this->belongsTo(Project::class, 'project_id');
	}
}

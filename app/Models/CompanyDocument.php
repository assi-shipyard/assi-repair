<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDocument extends Model
{
    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $table = 'company_documents';

	protected $fillable = [
		'company_id',
		'document_name',
		'document_type',
		'document_path',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'company_id' => 'integer',
			'document_name' => 'string',
			'document_type' => 'string',
			'document_path' => 'string',
		];
	}

	public function company()
	{
		return $this->belongsTo(Company::class);
	}
}

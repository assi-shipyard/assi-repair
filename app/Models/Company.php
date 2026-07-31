<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'unique_id',
		'name',
		'address',
		'phone',
		'email',
		'ceo_name',
		'ceo_phone',
		'ceo_email',
		'pic_name',
		'pic_phone',
		'pic_email',
		'registration_number',
		'tax_id',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'unique_id' => 'string',
			'name' => 'string',
			'address' => 'string',
			'phone' => 'string',
			'email' => 'string',
			'ceo_name' => 'string',
			'ceo_phone' => 'string',
			'ceo_email' => 'string',
			'pic_name' => 'string',
			'pic_phone' => 'string',
			'pic_email' => 'string',
			'registration_number' => 'string',
			'tax_id' => 'string',
		];
	}

	public function documents()
	{
		return $this->hasMany(CompanyDocument::class);
	}

	public function ships()
	{
		return $this->hasMany(Ship::class);
	}
}

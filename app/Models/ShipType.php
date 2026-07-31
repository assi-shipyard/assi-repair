<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipType extends Model
{
    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $table = 'ship_types';

	protected $fillable = [
		'name',
	];

	public function ships()
	{
		return $this->hasMany(Ship::class, 'ship_type_id');
	}
}

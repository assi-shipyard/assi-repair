<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipClass extends Model
{
    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $table = 'ship_classes';

	protected $fillable = [
		'name', 'abbreviation',
	];

	public function ships()
	{
		return $this->hasMany(Ship::class, 'ship_class_id');
	}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ship extends Model
{
    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $table = 'ships';

	protected $fillable = [
		'unique_id', 'name',
		'company_id', 'ship_type_id', 'ship_class_id',
		'length_overall', 'breadth', 'height', 'empty_draft', 'loaded_draft', 'gross_tonnage', 'net_tonnage',
		'engine_brand', 'engine_model', 'engine_power', 'engine_type', 'engine_rpm', 'engine_fuel_type', 'engine_fuel_capacity', 'engine_fuel_consumption',
		'imo_number', 'call_sign', 'flag', 'build_year', 'comment',
	];


	/**
	 * Define relationships to other models.
	 */
	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function type()
	{
		return $this->belongsTo(ShipType::class, 'ship_type_id');
	}

	public function classification()
	{
		return $this->belongsTo(ShipClass::class, 'ship_class_id');
	}

	public function docking_requests(): HasMany
	{
		return $this->hasMany(ProjectDockingRequest::class, 'ship_id');
	}

	public function docking_occupancies(): HasMany
	{
		return $this->hasMany(DockingOccupancy::class, 'ship_id');
	}

	public function floating_repair_histories(): HasMany
	{
		return $this->hasMany(FloatingRepairHistory::class, 'ship_id');
	}
}

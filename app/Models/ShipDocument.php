<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipDocument extends Model
{
	use HasPublicUniqueId;

	protected $table = 'ship_documents';

	protected $fillable = [
		'unique_id',
		'ship_id',
		'document_name',
		'document_type',
		'document_path',
	];

	public function ship(): BelongsTo
	{
		return $this->belongsTo(Ship::class);
	}
}

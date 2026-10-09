<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DockingRequestDocument extends Model
{
    use HasPublicUniqueId;

    public const TYPE_LABELS = [
        'ship_particular' => 'Ship Particular',
        'general_arrangement' => 'General Arrangement',
        'docking_plan' => 'Docking Plan',
        'stability_booklet' => 'Stability Booklet',
        'other' => 'Dokumen Lainnya',
    ];

    protected $fillable = [
        'unique_id',
        'project_docking_request_id',
        'document_type',
        'document_name',
        'document_path',
        'uploaded_by',
    ];

    public function docking_request(): BelongsTo
    {
        return $this->belongsTo(ProjectDockingRequest::class, 'project_docking_request_id');
    }
}

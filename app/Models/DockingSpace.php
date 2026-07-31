<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DockingSpace extends Model
{
    protected $table = 'docking_spaces';

    protected $fillable = [
        'name',
        'max_draft',
        'max_tonnage',
        'max_breadth',
        'max_capacity',
    ];
}

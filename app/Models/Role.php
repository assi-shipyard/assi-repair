<?php
namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'position_role', 'role_id', 'position_id');
    }
}

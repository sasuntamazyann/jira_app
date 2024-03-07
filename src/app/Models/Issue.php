<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory;

    protected $table = [
        'key', 'external_id', 'summary', 'description', 'project_id', 'type_id', 'reporter_external_id'
    ];
}

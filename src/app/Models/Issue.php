<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'external_id', 'summary', 'description', 'project_id', 'type_id', 'reporter_external_id', 'parent_id'
    ];

    public function type()
    {
        return $this->belongsTo(IssueType::class, 'type_id');
    }

    public function parent()
    {
        return $this->belongsTo(Issue::class, 'parent_id');
    }
}

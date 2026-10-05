<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFile extends Model
{
    use HasFactory;

    protected $fillable = ['issue_id', 'uploaded_by', 'name', 'category', 'size', 'path', 'description'];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}

<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KnowledgeArticle extends Model
{
    use HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'content',
        'author_id',
        'is_published',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (KnowledgeArticle $article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title).'-'.Str::random(5);
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WikiPage extends Model
{
    use HasFactory;

    protected $fillable = ['issue_id', 'author_id', 'number', 'title', 'body', 'star_count'];

    /**
     * 階層番号(1, 1.1, 1.2.1 など)を桁数の影響なく昇順比較するためのソートキー。
     * 番号未設定のページは末尾に並ぶ。
     */
    public function numberSortKey(): string
    {
        if ($this->number === null || $this->number === '') {
            return '~';
        }

        return implode('.', array_map(
            fn (string $segment) => str_pad($segment, 5, '0', STR_PAD_LEFT),
            explode('.', $this->number)
        ));
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}

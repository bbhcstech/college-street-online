<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublisherActivity extends Model
{
    protected $fillable = [
        'publisher_id', 'book_id', 'actor_id', 'type', 'subject', 'description', 'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function publisher() { return $this->belongsTo(Publisher::class); }
    public function book() { return $this->belongsTo(Book::class)->withTrashed(); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }

    public static function record(Publisher $publisher, string $type, string $subject, string $description, ?Book $book = null, array $metadata = []): self
    {
        return static::create([
            'publisher_id' => $publisher->id,
            'book_id' => $book?->id,
            'actor_id' => auth()->id(),
            'type' => $type,
            'subject' => $subject,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }
}

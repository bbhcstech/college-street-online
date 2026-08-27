<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Services\TransliterationService;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'publisher_id', 'category_id', 'author_id', 'title', 'title_transliterated',
        'isbn', 'price', 'mrp', 'description', 'cover_image_url', 'status',
    ];
    protected $casts = ['price' => 'decimal:2', 'mrp' => 'decimal:2'];

    protected static function booted(): void
    {
        // FR-4: re-run transliteration on every save, not just at creation,
        // so the Bengali search index never drifts from the current title.
        static::saving(function (Book $book) {
            if ($book->isDirty('title')) {
                $book->title_transliterated = app(TransliterationService::class)->transliterate($book->title);
            }
        });
    }

    public function publisher() { return $this->belongsTo(Publisher::class); }
    public function category() { return $this->belongsTo(Category::class)->withTrashed(); }
    public function author() { return $this->belongsTo(Author::class)->withTrashed(); }
    public function inventory() { return $this->hasOne(Inventory::class); }
    public function inventoryTransactions() { return $this->hasMany(InventoryTransaction::class); }
    public function reviews() { return $this->hasMany(BookReview::class); }

    public function getCoverUrlAttribute(): ?string
    {
        if (! $this->cover_image_url) return null;

        return str_starts_with($this->cover_image_url, 'http')
            ? $this->cover_image_url
            : Storage::disk('public')->url($this->cover_image_url);
    }

    public function scopeActive($q) { return $q->where('status', 'active'); }

    /** FR-4: search by book, ISBN, author, or publisher name. */
    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function ($sub) use ($term) {
            $sub->where('title', 'like', "%{$term}%")
                ->orWhere('title_transliterated', 'like', "%{$term}%")
                ->orWhere('isbn', 'like', "%{$term}%")
                ->orWhereHas('author', fn ($author) => $author->where('name', 'like', "%{$term}%"))
                ->orWhereHas('publisher', function ($publisher) use ($term) {
                    $publisher->where('business_name', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$term}%"));
                });
        });
    }
}

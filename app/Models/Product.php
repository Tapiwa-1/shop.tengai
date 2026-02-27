<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'brand',
        'currency',
        'price',
        'category_id',
        'rating',
        'review_count',
        'availability',
        'source_url',
        'attributes',
        'bullet_points',
        'images',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $product->currency = 'USD';
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'bullet_points' => 'array',
            'images' => 'array',
            'price' => 'decimal:2',
            'rating' => 'decimal:2',
        ];
    }
}

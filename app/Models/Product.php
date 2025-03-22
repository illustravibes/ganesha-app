<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'category_id', 'price', 'stock', 'size', 'color', 'image_url'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'price' => 'decimal:2',
        'attachment' => 'array',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}

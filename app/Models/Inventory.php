<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Inventory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['product_id', 'type', 'quantity', 'note'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'quantity' => 'integer',
        'type' => 'string',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

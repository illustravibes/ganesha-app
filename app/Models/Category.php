<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

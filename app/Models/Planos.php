<?php

namespace App\Models;

use Database\Factories\PlanosFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $nome
 * @property string $slug
 * @property string $price_id
 * @property string $product_id
 *
 * @method static PlanosFactory factory(...$parameters)
 */

class Planos extends Model
{
    /** @use HasFactory<\Database\Factories\PlanosFactory> */
    use HasFactory;

    protected $fillable = ['nome', 'slug', 'price_id', 'product_id'];

    protected static function newFactory(): PlanosFactory
    {
        return PlanosFactory::new();
    }
}

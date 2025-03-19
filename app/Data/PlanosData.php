<?php

namespace App\Data;

use App\Models\Planos;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Stripe\Price;
use Stripe\Product;

class PlanosData extends Data
{
    public ?string $id;
    #[MapName('name')]
    public string $nome;
    public ?string $slug;
    public float $amount;
    public string $interval;

    public static function rules(ValidationContext $context): array
    {
        return [
            'id' => [
                'nullable',
                'string',
                Rule::exists(Planos::class, 'product_id')->withoutTrashed(),
                ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Planos::class, 'nome')
            ],
            'amount' => 'required|numeric|min:0.01',
            'interval' => 'required|in:month,quarter,year',
        ];
    }

    public function criarPlanos()
    {

        // Cria o produto (Plano)
        $product = Product::create([
            'name' => $this->nome,
            'type' => 'service',
        ]);

        [$count, $interval] = match($this->interval) {
            'month' => [1, 'month'],
            'quarter' => [3, 'month'],
            'year' => [1, 'year']
        };

        // Cria o preço associado ao produto
        $price = Price::create([
            'unit_amount' => $this->amount * 100,
            'currency' => 'brl',
            'recurring' => [
                'interval' => $interval,
                'interval_count' => $count,
            ],
            'product' => $product->id,
        ]);


       $plano = Planos::make([
           'nome' => $this->nome,
           'slug' => $this->slug ? Str::slug($this->slug) : Str::slug($this->nome),
           'price_id' => $price->id,
           'product_id' => $product->id,
       ]);


       $plano->save();

       $this->id = $product->id;
        $this->slug = $plano->slug;

    }

    public function atualizarPlano(Planos $plano)
    {
        $plano->update([
            'nome' => $this->nome,
            'slug' => $this->slug ? Str::slug($this->slug) : Str::slug($this->nome),
        ]);

        $product = Product::retrieve($plano->product_id);
        Product::update($product->id, [
            'name' => $this->nome
        ]);

        $this->id = $product->id;
        $this->slug = $plano->slug;

    }

    public function response(string $key = 'data', string $text = "Sucesso"): array
    {
        return [
            $key => [
                'message' => $text,
                'id' => $this->id,
            ],
        ];
    }
}

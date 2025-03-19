<?php

    namespace App\Data;

    use App\Models\Planos;
    use Illuminate\Validation\Rule;
    use Spatie\LaravelData\Data;
    use Spatie\LaravelData\Support\Validation\ValidationContext;

    class ConteudoData extends Data
    {
        public string $plano;

        public static function rules(ValidationContext $context): array
        {
            return [
                'plano' => [
                    'require',
                    'string',
                    Rule::exists(Planos::class, 'slug')->withoutTrashed(),
                ]
            ];
        }

        public static function attributes(...$args): array
        {
            return [
                'plano' => 'Plano'
            ];
        }
    }

<?php

    namespace Tests\Feature;

    use App\Models\Planos;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Mockery;
    use PHPUnit\Framework\Attributes\Test;
    use PHPUnit\Framework\Attributes\TestDox;
    use Stripe\Price;
    use Stripe\Product;
    use Stripe\StripeClient;
    use Tests\TestCase;

    class PlanosControllerTest extends TestCase
    {
        use RefreshDatabase;

        protected function setUp(): void
        {
            parent::setUp();
            // Mock do Stripe
            $this->stripeMock = Mockery::mock(StripeClient::class);
            $this->app->instance(StripeClient::class, $this->stripeMock);
        }

        #[Test]
        #[TestDox('Deve Retornar Todos os Planos no formato Json')]
        /** @test */
        public function deveRetornarTodosPlanosNoFormatoJson()
        {
            $this->loginPassport();

            $this->mockStripe();

            $response = $this->getJson(route('planos.index'), [
                'Accept' => 'application/json'
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        [
                            'id' => 'prod_123',
                            'nome' => 'Plano Mensal',
                            'prices' => [
                                [
                                    'price_id' => 'price_123',
                                    'amount' => 10,
                                    'currency' => 'BRL',
                                    'recorrencia' => 'Mensal',
                                    'interval' => 'month',
                                ],
                            ],
                        ],
                    ],
                ]);
        }

        /** @test */
        public function deveCriarUmNovoPlano()
        {

            $this->loginPassport();

            $payload = [
                'name' => 'Plano Trimestral',
                'amount' => 10,
                'interval' => 'quarter'
            ];

            $response = $this->postJson(route('planos.store'), $payload);

            $response->assertStatus(201)
                ->assertExactJsonStructure([
                    'data' => [
                        "message",
                        "id"
                    ]
                ]);

            $this->assertDatabaseHas('planos', ['nome' => 'Plano Trimestral']);
        }

        /** @test */
        public function deveAtualizarUmPlano()
        {
            $this->loginPassport();

            $plano = Planos::factory()->create([
                'product_id' => 'prod_123',
            ]);


            $mockProduct = Mockery::mock('overload:'.Product::class);
            $mockProduct->shouldReceive('retrieve')
                ->andReturn((object) ['id' => 'prod_123']);

            $mockPrice = Mockery::mock('overload:'.Price::class);
            $mockPrice->shouldReceive('all')
                ->with(['product' => 'prod_123'])
                ->andReturn((object) [
                    'data' => [
                        (object) [
                            'id' => 'price_123',
                        ],
                    ],
                ]);

            // Mock de Product::update()
            $mockProduct->shouldReceive('update')
                ->andReturn((object) [
                    'id' => 'prod_123',
                    'name' => 'Plano Atualizado'
                ]);

            $response = $this->putJson('/api/planos/prod_123', [
                'name' => 'Plano Atualizado',
                'amount' => 10,
                'interval' => 'month'
            ]);

            // Verificar a resposta
            $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => 'prod_123',
                        'message' => "Plano atualizado com sucesso!"
                    ]
                ]);

            $this->assertDatabaseHas('planos', ['nome' => 'Plano Atualizado']);

        }

        /** @test */
        public function deveDeletarUmPlano()
        {
            $this->loginPassport();

            $plano = Planos::factory()->create([
                'product_id' => 'prod_123',
            ]);

            $mockProduct = Mockery::mock('overload:'.Product::class);
            $mockProduct->shouldReceive('retrieve')
                ->andReturn((object) ['id' => 'prod_123']);

            $mockPrice = Mockery::mock('overload:'.Price::class);
            $mockPrice->shouldReceive('all')
                ->with(['product' => 'prod_123'])
                ->andReturn((object) [
                    'data' => [
                        (object) [
                            'id' => 'price_123',
                        ],
                    ],
                ]);

            // Mock de Product::update()
            $mockProduct->shouldReceive('update')
                ->andReturn((object) [
                    'id' => 'prod_123',
                    'active' => false,
                ]);

            // Mock de Price::update()
            $mockPrice->shouldReceive('update')
                ->andReturn((object) [
                    'id' => 'price_123',
                    'active' => false,
                ]);

            $response = $this->deleteJson(route('planos.destroy', $plano->product_id));

            $response->assertStatus(200)
                ->assertJson(['message' => 'Plano deletado com sucesso!']);

            $this->assertDatabaseMissing('planos', ['product_id' => 'prod_123']);
        }

        private function mockStripe()
        {
            // Mock do Product
            $mockProduct = Mockery::mock('alias:'.Product::class);
            $mockProduct->shouldReceive('all')
                ->with(['active' => true])
                ->andReturn((object) [
                    'data' => [
                        (object) [
                            'id' => 'prod_123',
                            'name' => 'Plano Mensal',
                            'active' => true,
                        ],
                    ],
                ]);

            // Mock do Price
            $mockPrice = Mockery::mock('alias:'.Price::class);
            $mockPrice->shouldReceive('all')
                ->with(['product' => 'prod_123', 'active' => true])
                ->andReturn((object) [
                    'data' => [
                        (object) [
                            'id' => 'price_123',
                            'unit_amount' => 1000,
                            'currency' => 'brl',
                            'recurring' => (object) [
                                'interval' => 'month',
                                'interval_count' => 1,
                            ],
                        ],
                    ],
                ]);
        }

    }


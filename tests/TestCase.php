<?php

    namespace Tests;

    use App\Models\User;
    use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
    use Laravel\Passport\Passport;

    abstract class TestCase extends BaseTestCase
    {
        protected function loginPassport(bool $isAdmin = true)
        {
            // Criar um usuário e autenticar com Passport
            $user = User::factory()->create([
                'is_admin' => $isAdmin
            ]);
            Passport::actingAs($user);
        }

        protected function login(bool $isAdmin = true)
        {
            $user = User::factory()->create([
                'is_admin' => $isAdmin
            ]);

            $this->actingAs($user);
        }
    }

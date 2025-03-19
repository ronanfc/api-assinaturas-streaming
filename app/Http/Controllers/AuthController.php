<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\Models\User;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Validation\ValidationException;

    class AuthController extends Controller
    {
        /**
         * Registra um novo usuário.
         *
         * @OA\Post(
         *     path="/api/register",
         *     summary="Registrar um novo usuário",
         *     tags={"Autenticação"},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"name","email","password","password_confirmation"},
         *             @OA\Property(property="name", type="string", example="João da Silva"),
         *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
         *             @OA\Property(property="password", type="string", example="12345678"),
         *             @OA\Property(property="password_confirmation", type="string", example="12345678"),
         *             @OA\Property(property="is_admin", type="boolean", example=false)
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Usuário registrado com sucesso",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Usuário registrado com sucesso!")
         *         )
         *     ),
         *     @OA\Response(
         *         response=406,
         *         description="Requisição inválida",
         *         @OA\JsonContent(
         *             @OA\Property(property="error", type="string", example="Requisição inválida")
         *         )
         *     )
         * )
         */
        public function register(Request $request)
        {

            if (!$request->expectsJson()) {
                return response()->json(['error' => 'Requisição inválida'], 406);
            }

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'email' => [
                    'required',
                    'email',
                    'unique:users,email'
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed'
                ],
            ]);


            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_admin' => $validated['is_admin'] ?? 0,
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json(['message' => 'Usuário registrado com sucesso!'], 201);
        }

        /**
         * Realiza login e retorna um token de acesso.
         *
         * @OA\Post(
         *     path="/api/login",
         *     summary="Login do usuário",
         *     tags={"Autenticação"},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"email", "password"},
         *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
         *             @OA\Property(property="password", type="string", example="12345678")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Login bem-sucedido",
         *         @OA\JsonContent(
         *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1...")
         *         )
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Credenciais inválidas",
         *         @OA\JsonContent(
         *             @OA\Property(property="error", type="string", example="Credenciais inválidas")
         *         )
         *     ),
         *     @OA\Response(
         *         response=406,
         *         description="Requisição inválida",
         *         @OA\JsonContent(
         *             @OA\Property(property="error", type="string", example="Requisição inválida")
         *         )
         *     )
         * )
         */
        public function login(Request $request)
        {
            if (!$request->acceptsJson()) {
                return response()->json(['error' => 'Requisição inválida'], 406);
            }

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            /* @var User $user */
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciais inválidas.'],
                ]);
            }

            $token = $user->createToken('access-token')->accessToken;

            return response()->json(['token' => $token], 200);
        }

        /**
         * Realiza o logout do usuário e revoga o token.
         *
         * @OA\Post(
         *     path="/api/logout",
         *     summary="Logout do usuário",
         *     tags={"Autenticação"},
         *     security={{"bearerAuth": {}}},
         *     @OA\Response(
         *         response=200,
         *         description="Logout realizado com sucesso",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Desconectado com sucesso")
         *         )
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Não autorizado",
         *         @OA\JsonContent(
         *             @OA\Property(property="error", type="string", example="Token inválido ou ausente")
         *         )
         *     )
         * )
         */
        public function logout(Request $request)
        {
            $request->user()->token()->revoke();

            return response()->json(['message' => 'Desconectado com sucesso']);
        }
    }

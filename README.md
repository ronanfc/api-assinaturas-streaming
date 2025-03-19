# API Assinatura Streaming


## Passo a passo para rodar o projeto
Clone o projeto
```sh
git clone https://github.com/ronanfc/api-assinaturas-streaming
```
```sh
cd api-assinaturas-streaming/
```


Crie o Arquivo .env
```sh
cp .env.example .env
```

Atualize essas variáveis de ambiente no arquivo .env
```dosini
STRIPE_KEY=sk_test_sua_chave_secreta
STRIPE_SECRET=sk_test_sua_chave_secreta

STRIPE_WEBHOOK_SECRET=whsec_test_sua_chave_secreta 
```


Suba os containers do projeto
```sh
docker-compose up -d
```

Acesse o container
```sh
docker-compose exec app bash
```

Instale as dependências do projeto
```sh
composer install
```

Gere a key do projeto Laravel
```sh
php artisan key:generate
```

Rodar a migrate do projeto
```sh
php artisan migrate
```

Rodar a seeder do projeto
```sh
php artisan db:seed
```

Instalar dependencias do projeto
```sh
npm install
```

Gerar build do projeto
```sh
npm run build
```

Rodar o comando acesso Stripe CLI  
```sh
stripe login 
```
Clicar no link gerado para conceder acesso Stripe CLI

Configurar webhook stripe para teste de retorno
```sh
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

Subir worker 
```sh
php artisan schedule:work
```

Acesse o projeto
[http://localhost:8000](http://localhost:8000)

Login e senha de administrador
```text
usuário: admin@api.com 
senha: admin
```

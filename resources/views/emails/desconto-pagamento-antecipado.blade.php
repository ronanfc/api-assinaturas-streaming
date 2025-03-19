<h1>Olá, {{ $user->name }}!</h1>

<p>Sua assinatura irá vencer em <strong>{{ $dueDate }}</strong>.</p>

<p>Quer economizar? Aproveite <strong>10% de desconto</strong> no pagamento antecipado!</p>

<a href="{{ $discountUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
    Aproveitar Desconto
</a>

<p>Se você já realizou o pagamento, desconsidere este aviso.</p>

<p>Atenciosamente,<br>Equipe {{ config('app.name') }}</p>

<?php /** @var string $necessaria */ ?><!doctype html>
<html lang="pt-PT">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sem permissão · Pulso</title><link rel="stylesheet" href="/assets/style.css"></head>
<body class="centro">
  <section class="cartao estreito">
    <p class="mono vermelho">Erro 403</p>
    <h1>Sem permissão</h1>
    <p>O teu perfil (<strong><?= e(utilizador()['perfil'] ?? '') ?></strong>) não tem a permissão
       <code><?= e($necessaria) ?></code> necessária para abrir esta página.</p>
    <p><a class="botao" href="<?= e(inicio_do_perfil()) ?>">Voltar ao início</a></p>
  </section>
</body>
</html>

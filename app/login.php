<?php
require __DIR__ . '/inc/bootstrap.php';

$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    if (login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        $voltar = $_GET['voltar'] ?? '';
        // só aceita caminhos internos, para não redirecionar para outro site
        $destino = (str_starts_with($voltar, '/') && !str_starts_with($voltar, '//')) ? $voltar : inicio_do_perfil();
        header('Location: ' . $destino);
        exit;
    }
    $erro = 'Email ou password incorretos.';
}
if (utilizador()) { header('Location: ' . inicio_do_perfil()); exit; }
$msg = flash();
?><!doctype html>
<html lang="pt-PT">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar · Pulso</title><link rel="stylesheet" href="/assets/style.css"></head>
<body class="centro">
  <form class="cartao estreito" method="post" aria-labelledby="t">
    <p class="eyebrow">Pulso · Gestão de Ginásio</p>
    <h1 id="t">Entrar</h1>
    <p style="color:var(--mudo);margin-top:0">Um só login para sócios e equipa. O que vês depois depende do teu perfil.</p>
    <?php if ($msg): ?><p class="aviso ok" role="status"><?= e($msg) ?></p><?php endif; ?>
    <?php if ($erro): ?><p class="aviso erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
    <?= csrf_campo() ?>
    <div class="campo"><label for="email">Email</label>
      <input id="email" name="email" type="email" autocomplete="username" required value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="campo"><label for="password">Password</label>
      <input id="password" name="password" type="password" autocomplete="current-password" required></div>
    <button class="botao" type="submit" style="width:100%">Entrar</button>
    <p class="demo" style="text-align:center">Ainda não és sócio? <a href="/registo.php">Criar conta</a> · <a href="/index.php">Ver o site</a></p>
    <div class="demo">
      <p><strong>Contas de demonstração</strong> (só para testes):</p>
      <p><code>gestor@pulso.test</code> · <code>Gestor123!</code> — Gestor<br>
         <code>rececao@pulso.test</code> · <code>Rececao123!</code> — Rececionista<br>
         <code>treinador@pulso.test</code> · <code>Treinador123!</code> — Treinador<br>
         <code>socio@pulso.test</code> · <code>Socio123!</code> — Sócio</p>
    </div>
  </form>
</body>
</html>

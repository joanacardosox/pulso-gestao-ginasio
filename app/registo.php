<?php
require __DIR__ . '/inc/bootstrap.php';

// Quem já tem sessão não precisa de criar conta
if (utilizador()) { header('Location: ' . inicio_do_perfil()); exit; }

// Registo de sócio: só o perfil Visitante tem a permissão conta.criar
exigir('conta.criar');

$planos = db()->query('SELECT id, nome, preco FROM planos WHERE arquivado_em IS NULL ORDER BY duracao_meses')->fetchAll();
$erros = [];
$v = ['nome' => '', 'email' => '', 'plano' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $v['nome']  = trim($_POST['nome'] ?? '');
    $v['email'] = mb_strtolower(trim($_POST['email'] ?? ''));
    $v['plano'] = (int) ($_POST['plano'] ?? 0);
    $pass = $_POST['password'] ?? '';
    $plano = null;
    foreach ($planos as $p) if ((int) $p['id'] === $v['plano']) $plano = $p;

    if (mb_strlen($v['nome']) < 3 || mb_strlen($v['nome']) > 100) $erros[] = 'O nome deve ter entre 3 e 100 caracteres.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'O email não é válido.';
    if (!$plano) $erros[] = 'Escolhe um plano.';
    if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) $erros[] = 'A password deve ter pelo menos 8 caracteres, com letras e números.';
    if ($pass !== ($_POST['password2'] ?? '')) $erros[] = 'As duas passwords não coincidem.';
    if (!$erros) {
        $st = db()->prepare('SELECT COUNT(*) FROM utilizadores WHERE email = ?');
        $st->execute([$v['email']]);
        if ($st->fetchColumn()) $erros[] = 'Já existe uma conta com esse email.';
    }

    if (!$erros) {
        $pdo = db();
        $pdo->beginTransaction();
        // 1) conta de acesso com o perfil Sócio
        $pdo->prepare('INSERT INTO utilizadores (nome, email, password_hash, perfil_id) VALUES (?, ?, ?, ?)')
            ->execute([$v['nome'], $v['email'], password_hash($pass, PASSWORD_DEFAULT), perfil_id('Sócio')]);
        $uid = (int) $pdo->lastInsertId();
        // 2) ficha de sócio com o número seguinte
        $num = str_pad((string) ((int) $pdo->query('SELECT COALESCE(MAX(CAST(numero AS UNSIGNED)), 0) FROM socios')->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
        $pdo->prepare('INSERT INTO socios (numero, utilizador_id, nome, plano_id, data_inscricao) VALUES (?, ?, ?, ?, CURDATE())')
            ->execute([$num, $uid, $v['nome'], $plano['id']]);
        $sid = (int) $pdo->lastInsertId();
        // 3) primeira mensalidade, a pagar na receção, por MB Way ou com cartão
        $pdo->prepare("INSERT INTO mensalidades (socio_id, referencia, valor, data_limite, estado) VALUES (?, '1.ª mensalidade', ?, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Pendente')")
            ->execute([$sid, $plano['preco']]);
        $pdo->commit();
        flash('Conta criada (sócio nº ' . $num . '). Já podes entrar e pagar a 1.ª mensalidade na receção, por MB Way ou com cartão.');
        header('Location: /login.php');
        exit;
    }
}
?><!doctype html>
<html lang="pt-PT">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Criar conta · Pulso</title><link rel="stylesheet" href="/assets/style.css"></head>
<body class="centro">
  <form class="cartao estreito" method="post" aria-labelledby="t"><?= csrf_campo() ?>
    <p class="eyebrow">Visitante · <code>conta.criar</code></p>
    <h1 id="t">Criar conta</h1>
    <?php if ($erros): ?><ul class="erros" role="alert"><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <div class="campo"><label for="nome">Nome</label><input id="nome" name="nome" type="text" required minlength="3" maxlength="100" value="<?= e($v['nome']) ?>"></div>
    <div class="campo"><label for="email">Email</label><input id="email" name="email" type="email" required maxlength="150" autocomplete="email" value="<?= e($v['email']) ?>"></div>
    <div class="campo"><label for="plano">Plano</label>
      <select id="plano" name="plano" required><option value="">Escolher…</option>
        <?php foreach ($planos as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int) $p['id'] === $v['plano'] ? 'selected' : '' ?>><?= e($p['nome']) ?> · <?= number_format((float) $p['preco'], 0, ',', ' ') ?> €</option><?php endforeach; ?>
      </select></div>
    <div class="campo"><label for="pw">Password</label><input id="pw" name="password" type="password" required minlength="8" autocomplete="new-password">
      <p class="ajuda">Mínimo 8 caracteres, com letras e números.</p></div>
    <div class="campo"><label for="pw2">Repetir password</label><input id="pw2" name="password2" type="password" required minlength="8" autocomplete="new-password"></div>
    <button class="botao" type="submit" style="width:100%">Criar conta</button>
    <p class="demo" style="text-align:center">Já tens conta? <a href="/login.php">Entrar</a> · <a href="/index.php">Ver o site</a></p>
  </form>
</body>
</html>

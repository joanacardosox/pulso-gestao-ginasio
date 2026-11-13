<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

$u = exigir('utilizadores.ver');

// Perfis que o gestor pode dar a uma conta da equipa.
// "Sócio" fica de fora (os sócios criam a conta no site, com ficha de sócio) e "Visitante" é quem não tem conta.
$perfis = db()->query("SELECT id, nome FROM perfis WHERE nome <> 'Visitante' ORDER BY id")->fetchAll();
$perfis_equipa = array_values(array_filter($perfis, fn ($p) => $p['nome'] !== 'Sócio'));

$erros = [];
$novo = ['nome' => '', 'email' => '', 'perfil' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $acao = $_POST['acao'] ?? 'perfil';

    if ($acao === 'criar') {
        // ---------- Criar utilizador da equipa ----------
        exigir('utilizadores.criar');
        $novo['nome']   = trim($_POST['nome'] ?? '');
        $novo['email']  = mb_strtolower(trim($_POST['email'] ?? ''));
        $novo['perfil'] = (int) ($_POST['perfil'] ?? 0);
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';

        if (mb_strlen($novo['nome']) < 3 || mb_strlen($novo['nome']) > 100) $erros[] = 'O nome deve ter entre 3 e 100 caracteres.';
        if (!filter_var($novo['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'O email não é válido.';
        if (!in_array($novo['perfil'], array_map(fn ($p) => (int) $p['id'], $perfis_equipa), true)) $erros[] = 'Escolhe um perfil da equipa.';
        if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) $erros[] = 'A password deve ter pelo menos 8 caracteres, com letras e números.';
        if ($pass !== $pass2) $erros[] = 'As duas passwords não coincidem.';

        if (!$erros) {
            $existe = db()->prepare('SELECT COUNT(*) FROM utilizadores WHERE email = ?');
            $existe->execute([$novo['email']]);
            if ($existe->fetchColumn()) $erros[] = 'Já existe um utilizador com esse email.';
        }

        if (!$erros) {
            db()->prepare('INSERT INTO utilizadores (nome, email, password_hash, perfil_id) VALUES (?, ?, ?, ?)')
                ->execute([$novo['nome'], $novo['email'], password_hash($pass, PASSWORD_DEFAULT), $novo['perfil']]);
            flash('Utilizador "' . $novo['nome'] . '" criado. Já pode entrar com o email e a password definidos.');
            header('Location: /backoffice/utilizadores.php');
            exit;
        }
    } elseif ($acao === 'ativo') {
        // ---------- Desativar / reativar (a conta não é apagada) ----------
        exigir('utilizadores.desativar');
        $alvo = (int) ($_POST['utilizador'] ?? 0);
        if ($alvo === (int) $u['id']) {
            flash('Não podes desativar a tua própria conta.');
        } else {
            $ativo = ($_POST['ativo'] ?? '') === '1' ? 1 : 0;
            db()->prepare('UPDATE utilizadores SET ativo = ? WHERE id = ?')->execute([$ativo, $alvo]);
            flash($ativo ? 'Conta reativada.' : 'Conta desativada: esta pessoa já não consegue entrar.');
        }
        header('Location: /backoffice/utilizadores.php');
        exit;
    } else {
        // ---------- Mudar o perfil de um utilizador existente ----------
        exigir('utilizadores.editar');
        $alvo = (int) ($_POST['utilizador'] ?? 0);
        $perfil = (int) ($_POST['perfil'] ?? 0);
        if ($alvo === (int) $u['id']) {
            flash('Não podes mudar o teu próprio perfil (evita ficares sem acesso).');
        } elseif (in_array($perfil, array_map(fn ($p) => (int) $p['id'], $perfis), true)) {
            db()->prepare('UPDATE utilizadores SET perfil_id = ? WHERE id = ?')->execute([$perfil, $alvo]);
            flash('Perfil atualizado. A alteração aplica-se no próximo pedido desse utilizador.');
        }
        header('Location: /backoffice/utilizadores.php');
        exit;
    }
}

$lista = db()->query('SELECT u.id, u.nome, u.email, u.perfil_id, u.ativo, p.nome AS perfil FROM utilizadores u JOIN perfis p ON p.id = u.perfil_id ORDER BY u.ativo DESC, u.perfil_id, u.nome')->fetchAll();

layout_inicio('Equipa', 'utilizadores');
?>
<p class="eyebrow">Equipa · <code>utilizadores.ver</code></p>
<h1>Utilizadores e perfis</h1>
<p class="nota">Cada utilizador tem um perfil. As permissões vêm do perfil (ver <a href="/backoffice/perfis.php">Perfis e permissões</a>). As passwords estão guardadas com <code>password_hash()</code>.<br>
  <?= etiqueta_permissao('Criar', 'utilizadores.criar') ?> &nbsp;
  <?= etiqueta_permissao('Mudar perfil', 'utilizadores.editar') ?> &nbsp;
  <?= etiqueta_permissao('Desativar', 'utilizadores.desativar') ?></p>

<?php if (pode('utilizadores.criar')): ?>
<section class="cartao" id="novo" aria-labelledby="novo-t">
  <h2 id="novo-t" style="margin-top:0">Novo utilizador da equipa</h2>
  <p class="ajuda" style="margin:-6px 0 16px">Só quem tem a permissão <code>utilizadores.criar</code> pode criar contas e escolher o perfil. Os sócios criam a conta no site.</p>
  <?php if ($erros): ?>
    <ul class="erros" role="alert"><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
  <?php endif; ?>
  <form method="post" action="/backoffice/utilizadores.php#novo"><?= csrf_campo() ?>
    <input type="hidden" name="acao" value="criar">
    <div class="form-grelha">
      <div class="campo"><label for="n-nome">Nome</label>
        <input id="n-nome" name="nome" type="text" required minlength="3" maxlength="100" autocomplete="off" value="<?= e($novo['nome']) ?>"></div>
      <div class="campo"><label for="n-email">Email</label>
        <input id="n-email" name="email" type="email" required maxlength="150" autocomplete="off" value="<?= e($novo['email']) ?>"></div>
      <div class="campo"><label for="n-perfil">Perfil</label>
        <select id="n-perfil" name="perfil" required>
          <option value="">Escolher…</option>
          <?php foreach ($perfis_equipa as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int) $p['id'] === $novo['perfil'] ? 'selected' : '' ?>><?= e($p['nome']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="campo"><label for="n-pass">Password</label>
        <input id="n-pass" name="password" type="password" required minlength="8" autocomplete="new-password">
        <p class="ajuda">Mínimo 8 caracteres, com letras e números.</p></div>
      <div class="campo"><label for="n-pass2">Repetir password</label>
        <input id="n-pass2" name="password2" type="password" required minlength="8" autocomplete="new-password"></div>
    </div>
    <button class="botao" type="submit">Criar utilizador</button>
  </form>
</section>
<?php endif; ?>

<section class="cartao"><div class="tabela"><table>
  <thead><tr><th>Nome</th><th>Email</th><th>Perfil</th><th>Conta</th></tr></thead>
  <tbody>
  <?php foreach ($lista as $l): $eu = (int) $l['id'] === (int) $u['id']; ?>
    <tr><td><strong><?= e($l['nome']) ?></strong></td><td><?= e($l['email']) ?></td>
      <td>
        <?php if ($eu): ?><?= e($l['perfil']) ?> (tu)
        <?php elseif (!pode('utilizadores.editar')): ?><?= e($l['perfil']) ?>
        <?php else: ?>
          <form method="post" style="display:flex;gap:8px;align-items:center"><?= csrf_campo() ?>
            <input type="hidden" name="acao" value="perfil">
            <input type="hidden" name="utilizador" value="<?= (int) $l['id'] ?>">
            <select name="perfil" aria-label="Perfil de <?= e($l['nome']) ?>">
              <?php foreach ($perfis as $p): ?><option value="<?= (int) $p['id'] ?>" <?= $p['id'] == $l['perfil_id'] ? 'selected' : '' ?>><?= e($p['nome']) ?></option><?php endforeach; ?>
            </select>
            <button class="botao sec" type="submit">Guardar</button>
          </form>
        <?php endif; ?>
      </td>
      <td style="white-space:nowrap">
        <?= $l['ativo'] ? '<span class="tag verde">Ativa</span>' : '<span class="tag cinza">Desativada</span>' ?>
        <?php if (!$eu && pode('utilizadores.desativar')): ?>
          <form method="post" style="display:inline;margin-left:6px"><?= csrf_campo() ?>
            <input type="hidden" name="acao" value="ativo">
            <input type="hidden" name="utilizador" value="<?= (int) $l['id'] ?>">
            <button class="botao sec" name="ativo" value="<?= $l['ativo'] ? '0' : '1' ?>" type="submit"><?= $l['ativo'] ? 'Desativar' : 'Reativar' ?></button>
          </form>
        <?php endif; ?>
      </td></tr>
  <?php endforeach; ?>
  </tbody>
</table></div></section>
<?php layout_fim();

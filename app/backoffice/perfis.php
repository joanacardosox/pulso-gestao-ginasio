<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

exigir('perfis.ver');
$podeEditar = pode('perfis.editar');

$perfis = db()->query('SELECT id, nome, bloqueado FROM perfis ORDER BY id')->fetchAll();
$perms  = db()->query('SELECT id, codigo, descricao, grupo, tem_ambito FROM permissoes ORDER BY id')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    exigir('perfis.editar');
    $marcadas = $_POST['p'] ?? [];        // p[perfil_id][permissao_id] = 'todas' | 'proprias'
    $pdo = db();
    $pdo->beginTransaction();
    $del = $pdo->prepare('DELETE FROM perfil_permissao WHERE perfil_id = ?');
    $ins = $pdo->prepare('INSERT INTO perfil_permissao (perfil_id, permissao_id, ambito) VALUES (?, ?, ?)');
    $validas = array_column($perms, 'tem_ambito', 'id');
    foreach ($perfis as $pf) {
        if ($pf['bloqueado']) continue;   // o Gestor mantém sempre as suas permissões
        $del->execute([$pf['id']]);
        foreach (($marcadas[$pf['id']] ?? []) as $permId => $amb) {
            $permId = (int) $permId;
            if (!array_key_exists($permId, $validas) || $amb === '') continue; // '' = "Sem acesso"
            $amb = ($validas[$permId] && $amb === 'proprias') ? 'proprias' : 'todas';
            $ins->execute([$pf['id'], $permId, $amb]);
        }
    }
    $pdo->commit();
    flash('Permissões guardadas. Aplicam-se de imediato a todos os utilizadores desses perfis.');
    header('Location: /backoffice/perfis.php');
    exit;
}

$atual = [];
foreach (db()->query('SELECT perfil_id, permissao_id, ambito FROM perfil_permissao') as $r) {
    $atual[$r['perfil_id']][$r['permissao_id']] = $r['ambito'];
}

layout_inicio('Perfis e permissões', 'perfis');
?>
<p class="eyebrow">RBAC · <code>perfis.ver</code></p>
<h1>O que cada perfil pode fazer</h1>
<p class="nota">Marca as permissões de cada perfil. Nas permissões de aulas e presenças podes limitar a <strong>só as suas</strong>
  (o treinador só vê e gere as aulas em que é responsável). O <strong>Gestor</strong> está bloqueado: as permissões dele não se alteram, para ninguém ficar sem acesso à gestão.<br>
  O <strong>Visitante</strong> é quem usa o site sem fazer login. <?= etiqueta_permissao('Alterar permissões', 'perfis.editar') ?></p>
<form class="cartao" method="post"><?= csrf_campo() ?>
  <div class="tabela"><table class="matriz">
    <thead><tr><th>Permissão</th>
      <?php foreach ($perfis as $pf): ?><th><?= e($pf['nome']) ?><br><span class="demo"><?= count($atual[$pf['id']] ?? []) ?> de <?= count($perms) ?></span></th><?php endforeach; ?>
    </tr></thead>
    <tbody>
    <?php $grupo = null; foreach ($perms as $pe): ?>
      <?php if ($pe['grupo'] !== $grupo): $grupo = $pe['grupo']; ?><tr class="grupo"><td colspan="<?= count($perfis) + 1 ?>"><?= e($grupo) ?></td></tr><?php endif; ?>
      <tr>
        <td><strong><?= e($pe['descricao']) ?></strong><br><code><?= e($pe['codigo']) ?></code></td>
        <?php foreach ($perfis as $pf):
            $tem = isset($atual[$pf['id']][$pe['id']]);
            $amb = $atual[$pf['id']][$pe['id']] ?? 'todas';
            $nome = "p[{$pf['id']}][{$pe['id']}]"; ?>
          <td>
            <?php if ($pf['bloqueado']): ?>
              <?= $tem ? '<span class="tag verde" title="bloqueado">✓</span>' : '<span class="demo" title="bloqueado">—</span>' ?><?= $tem && $amb === 'proprias' ? ' só as suas' : '' ?>
            <?php elseif ($pe['tem_ambito']): ?>
              <select name="<?= e($nome) ?>" <?= $podeEditar ? '' : 'disabled' ?> aria-label="<?= e($pe['codigo'] . ' — ' . $pf['nome']) ?>">
                <option value="" <?= !$tem ? 'selected' : '' ?>>Sem acesso</option>
                <option value="todas" <?= $tem && $amb === 'todas' ? 'selected' : '' ?>>Todas</option>
                <option value="proprias" <?= $tem && $amb === 'proprias' ? 'selected' : '' ?>>Só as suas</option>
              </select>
            <?php else: ?>
              <input type="checkbox" name="<?= e($nome) ?>" value="todas" <?= $tem ? 'checked' : '' ?> <?= $podeEditar ? '' : 'disabled' ?> aria-label="<?= e($pe['codigo'] . ' — ' . $pf['nome']) ?>">
            <?php endif; ?>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php if ($podeEditar): ?><p><button class="botao" type="submit">Guardar permissões</button></p><?php endif; ?>
</form>
<?php layout_fim();

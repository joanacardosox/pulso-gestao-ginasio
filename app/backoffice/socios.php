<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

exigir('socios.ver');

// Arquivar / reativar exige socios.arquivar. Nada é apagado: as mensalidades e as presenças ficam no histórico.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    exigir('socios.arquivar');
    $id = (int) ($_POST['socio'] ?? 0);
    $arquivar = ($_POST['acao'] ?? '') === 'arquivar';
    $pdo = db();
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE socios SET arquivado_em = ' . ($arquivar ? 'NOW()' : 'NULL') . ' WHERE id = ?')->execute([$id]);
    // a conta de acesso do sócio acompanha: arquivado não entra na área do sócio
    $pdo->prepare('UPDATE utilizadores SET ativo = ? WHERE id = (SELECT utilizador_id FROM socios WHERE id = ?)')->execute([$arquivar ? 0 : 1, $id]);
    $pdo->commit();
    flash($arquivar ? 'Sócio arquivado. As mensalidades e as presenças continuam guardadas no histórico.' : 'Sócio reativado.');
    header('Location: /backoffice/socios.php' . ($arquivar ? '' : '?ver=arquivados'));
    exit;
}

$verArquivados = ($_GET['ver'] ?? '') === 'arquivados';
$socios = db()->query("SELECT s.id, s.numero, s.nome, s.arquivado_em, p.nome AS plano,
    (SELECT estado FROM mensalidades m WHERE m.socio_id = s.id ORDER BY m.data_limite DESC LIMIT 1) AS estado
    FROM socios s JOIN planos p ON p.id = s.plano_id
    WHERE s.arquivado_em IS " . ($verArquivados ? 'NOT NULL' : 'NULL') . " ORDER BY s.numero")->fetchAll();
$total = db()->query('SELECT SUM(arquivado_em IS NULL) AS ativos, SUM(arquivado_em IS NOT NULL) AS arquivados FROM socios')->fetch();

layout_inicio('Sócios', 'socios');
?>
<p class="eyebrow">Sócios · <code>socios.ver</code></p>
<h1>Lista de sócios</h1>
<p class="nota">
  <?= etiqueta_permissao('Criar', 'socios.criar') ?> &nbsp;
  <?= etiqueta_permissao('Editar', 'socios.editar') ?> &nbsp;
  <?= etiqueta_permissao('Arquivar', 'socios.arquivar') ?>
</p>
<p>
  <a class="botao<?= $verArquivados ? ' sec' : '' ?>" href="/backoffice/socios.php">Ativos (<?= (int) $total['ativos'] ?>)</a>
  <a class="botao<?= $verArquivados ? '' : ' sec' ?>" href="/backoffice/socios.php?ver=arquivados">Arquivados (<?= (int) $total['arquivados'] ?>)</a>
</p>
<section class="cartao"><div class="tabela"><table>
  <thead><tr><th>Nº</th><th>Nome</th><th>Plano</th><th><?= $verArquivados ? 'Arquivado em' : 'Mensalidade' ?></th><th></th></tr></thead>
  <tbody>
  <?php foreach ($socios as $s): ?>
    <tr>
      <td class="mono"><?= e($s['numero']) ?></td>
      <td><strong><?= e($s['nome']) ?></strong></td>
      <td><?= e($s['plano']) ?></td>
      <td><?php if ($verArquivados): ?><?= e(date('d/m/Y', strtotime($s['arquivado_em']))) ?>
          <?php else: ?><?= $s['estado'] ? etiqueta_estado($s['estado']) : '—' ?><?php endif; ?></td>
      <td style="text-align:right">
        <?php if (pode('socios.arquivar')): ?>
          <form method="post" style="display:inline"><?= csrf_campo() ?>
            <input type="hidden" name="socio" value="<?= (int) $s['id'] ?>">
            <button class="botao sec" name="acao" value="<?= $verArquivados ? 'reativar' : 'arquivar' ?>" type="submit"><?= $verArquivados ? 'Reativar' : 'Arquivar' ?></button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$socios): ?><tr><td colspan="5"><?= $verArquivados ? 'Não há sócios arquivados.' : 'Não há sócios ativos.' ?></td></tr><?php endif; ?>
  </tbody>
</table></div></section>
<?php layout_fim();

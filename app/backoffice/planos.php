<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

exigir('planos.ver');

// Um plano arquivado deixa de aparecer no registo de novos sócios; os sócios que já o têm mantêm-no.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    exigir('planos.arquivar');
    $arquivar = ($_POST['acao'] ?? '') === 'arquivar';
    db()->prepare('UPDATE planos SET arquivado_em = ' . ($arquivar ? 'NOW()' : 'NULL') . ' WHERE id = ?')->execute([(int) ($_POST['plano'] ?? 0)]);
    flash($arquivar ? 'Plano arquivado: já não aparece para novos sócios.' : 'Plano reativado.');
    header('Location: /backoffice/planos.php');
    exit;
}

$planos = db()->query('SELECT p.*, (SELECT COUNT(*) FROM socios s WHERE s.plano_id = p.id AND s.arquivado_em IS NULL) AS socios
                       FROM planos p ORDER BY p.arquivado_em IS NOT NULL, p.duracao_meses')->fetchAll();

layout_inicio('Planos', 'planos');
?>
<p class="eyebrow">Planos · <code>planos.ver</code></p>
<h1>Planos e preços</h1>
<p class="nota">
  <?= etiqueta_permissao('Criar', 'planos.criar') ?> &nbsp;
  <?= etiqueta_permissao('Editar', 'planos.editar') ?> &nbsp;
  <?= etiqueta_permissao('Arquivar', 'planos.arquivar') ?>
</p>
<section class="cartao"><div class="tabela"><table>
  <thead><tr><th>Plano</th><th>Duração</th><th>Preço</th><th>Sócios ativos</th><th>Estado</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($planos as $p): $arq = $p['arquivado_em'] !== null; ?>
    <tr><td><strong><?= e($p['nome']) ?></strong></td><td><?= (int) $p['duracao_meses'] ?> <?= $p['duracao_meses'] == 1 ? 'mês' : 'meses' ?></td>
        <td><?= number_format((float) $p['preco'], 0, ',', ' ') ?> €</td><td><?= (int) $p['socios'] ?></td>
        <td><?= $arq ? '<span class="tag cinza">Arquivado</span>' : '<span class="tag verde">Ativo</span>' ?></td>
        <td style="text-align:right">
          <?php if (pode('planos.arquivar')): ?>
            <form method="post" style="display:inline"><?= csrf_campo() ?>
              <input type="hidden" name="plano" value="<?= (int) $p['id'] ?>">
              <button class="botao sec" name="acao" value="<?= $arq ? 'reativar' : 'arquivar' ?>" type="submit"><?= $arq ? 'Reativar' : 'Arquivar' ?></button>
            </form>
          <?php endif; ?>
        </td></tr>
  <?php endforeach; ?>
  </tbody>
</table></div></section>
<?php layout_fim();

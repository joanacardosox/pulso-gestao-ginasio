<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

$u = exigir('mensalidades.ver');

// Registar um pagamento: escolhe-se o método e fica guardado quem o registou
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $u = exigir('mensalidades.registar');
    $metodo = $_POST['metodo'] ?? '';
    if (!in_array($metodo, METODOS_PAGAMENTO, true)) {
        flash('Escolhe o método de pagamento.');
    } else {
        db()->prepare("UPDATE mensalidades SET estado = 'Paga', data_pagamento = CURDATE(), metodo = ?, registado_por = ?
                       WHERE id = ? AND estado <> 'Paga'")
            ->execute([$metodo, $u['id'], (int) ($_POST['pagar'] ?? 0)]);
        flash('Pagamento registado (' . $metodo . ').');
    }
    header('Location: /backoffice/mensalidades.php');
    exit;
}

$linhas = db()->query("SELECT m.*, s.nome, s.arquivado_em, r.nome AS registou
                       FROM mensalidades m JOIN socios s ON s.id = m.socio_id LEFT JOIN utilizadores r ON r.id = m.registado_por
                       ORDER BY FIELD(m.estado,'Em atraso','Pendente','Paga'), m.data_limite")->fetchAll();

layout_inicio('Mensalidades', 'mensalidades');
?>
<p class="eyebrow">Mensalidades · <code>mensalidades.ver</code></p>
<h1>Pagamentos dos sócios</h1>
<p class="nota">
  <?= etiqueta_permissao('Criar', 'mensalidades.criar') ?> &nbsp;
  <?= etiqueta_permissao('Registar pagamento', 'mensalidades.registar') ?>
  <br>O sócio pode pagar <strong>na receção</strong>, por <strong>MB Way</strong> ou com <strong>cartão</strong>.
</p>
<section class="cartao"><div class="tabela"><table>
  <thead><tr><th>Sócio</th><th>Período</th><th>Valor</th><th>Vencimento</th><th>Estado</th><th>Pagamento</th></tr></thead>
  <tbody>
  <?php foreach ($linhas as $m): ?>
    <tr>
      <td><strong><?= e($m['nome']) ?></strong><?= $m['arquivado_em'] ? ' <span class="tag cinza">arquivado</span>' : '' ?></td><td><?= e($m['referencia']) ?></td>
      <td><?= number_format((float) $m['valor'], 0, ',', ' ') ?> €</td>
      <td><?= e(date('d/m/Y', strtotime($m['data_limite']))) ?></td>
      <td><?= etiqueta_estado($m['estado']) ?></td>
      <td style="text-align:right">
        <?php if ($m['estado'] !== 'Paga' && pode('mensalidades.registar')): ?>
          <form method="post" style="display:flex;gap:8px;justify-content:flex-end"><?= csrf_campo() ?>
            <select name="metodo" aria-label="Método de pagamento de <?= e($m['nome']) ?>" style="width:auto" required>
              <?php foreach (METODOS_PAGAMENTO as $mt): ?><option><?= e($mt) ?></option><?php endforeach; ?>
            </select>
            <button class="botao" name="pagar" value="<?= (int) $m['id'] ?>">Registar</button>
          </form>
        <?php elseif ($m['estado'] === 'Paga'): ?>
          <span class="demo"><?= e(date('d/m/Y', strtotime((string) $m['data_pagamento']))) ?> · <strong><?= e($m['metodo'] ?? '—') ?></strong><?= $m['registou'] ? ' · ' . e($m['registou']) : '' ?></span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></section>
<?php layout_fim();

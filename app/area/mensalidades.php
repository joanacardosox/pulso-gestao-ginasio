<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

$u = exigir('mensalidades.proprias');

// Pagar online (MB Way ou cartão). Pagamento simulado: não há ligação a um banco.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $u = exigir('mensalidades.pagar');
    $metodo = $_POST['metodo'] ?? '';
    if (!in_array($metodo, ['MB Way', 'Cartão'], true)) {
        flash('Escolhe MB Way ou cartão. Na receção também podes pagar.');
    } else {
        // só paga se a mensalidade for mesmo deste sócio (âmbito: as suas)
        $st = db()->prepare("UPDATE mensalidades m JOIN socios s ON s.id = m.socio_id
                             SET m.estado = 'Paga', m.data_pagamento = CURDATE(), m.metodo = ?, m.registado_por = ?
                             WHERE m.id = ? AND s.utilizador_id = ? AND m.estado <> 'Paga'");
        $st->execute([$metodo, $u['id'], (int) ($_POST['pagar'] ?? 0), $u['id']]);
        flash($st->rowCount() ? 'Pagamento feito por ' . $metodo . '. Obrigado!' : 'Não foi possível pagar esta mensalidade.');
    }
    header('Location: /area/mensalidades.php');
    exit;
}

// Só as mensalidades do próprio sócio (ligadas ao utilizador da sessão)
$st = db()->prepare('SELECT m.* FROM mensalidades m JOIN socios s ON s.id = m.socio_id WHERE s.utilizador_id = ? ORDER BY m.data_limite DESC');
$st->execute([$u['id']]);
$linhas = $st->fetchAll();

layout_inicio('As minhas mensalidades', 'mensalidades', 'area');
?>
<p class="eyebrow">Mensalidades · <code>mensalidades.proprias</code></p>
<h1>Os teus pagamentos</h1>
<p class="nota">Podes pagar aqui por <strong>MB Way</strong> ou <strong>cartão</strong>, ou na <strong>receção</strong> do ginásio.
  <span class="demo">(No protótipo o pagamento online é simulado.)</span></p>
<section class="cartao"><div class="tabela"><table>
  <thead><tr><th>Período</th><th>Valor</th><th>Vencimento</th><th>Estado</th><th>Pagamento</th></tr></thead>
  <tbody>
  <?php foreach ($linhas as $m): ?>
    <tr><td><?= e($m['referencia']) ?></td><td><?= number_format((float) $m['valor'], 0, ',', ' ') ?> €</td>
        <td><?= e(date('d/m/Y', strtotime($m['data_limite']))) ?></td><td><?= etiqueta_estado($m['estado']) ?></td>
        <td style="text-align:right">
          <?php if ($m['estado'] === 'Paga'): ?>
            <span class="demo"><?= e(date('d/m/Y', strtotime((string) $m['data_pagamento']))) ?> · <strong><?= e($m['metodo'] ?? '—') ?></strong></span>
          <?php elseif (pode('mensalidades.pagar')): ?>
            <form method="post" style="display:flex;gap:8px;justify-content:flex-end"><?= csrf_campo() ?>
              <select name="metodo" aria-label="Como queres pagar" style="width:auto">
                <option>MB Way</option><option>Cartão</option>
              </select>
              <button class="botao" name="pagar" value="<?= (int) $m['id'] ?>">Pagar</button>
            </form>
          <?php endif; ?>
        </td></tr>
  <?php endforeach; ?>
  <?php if (!$linhas): ?><tr><td colspan="5">Sem mensalidades.</td></tr><?php endif; ?>
  </tbody>
</table></div></section>
<?php layout_fim();

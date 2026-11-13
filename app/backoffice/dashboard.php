<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

// Dashboard do Gestor: indicadores do ginásio
exigir('dashboard.ver');

$ativos   = db()->query('SELECT COUNT(*) FROM socios WHERE arquivado_em IS NULL')->fetchColumn();
$atraso   = db()->query("SELECT COUNT(*) FROM mensalidades WHERE estado = 'Em atraso'")->fetchColumn();
$recebido = db()->query("SELECT COALESCE(SUM(valor), 0) FROM mensalidades WHERE estado = 'Paga'")->fetchColumn();

// Melhores sócios: mais presenças nas aulas
$melhores = db()->query("SELECT s.numero, s.nome, COUNT(*) AS presencas
    FROM inscricoes i JOIN socios s ON s.id = i.socio_id
    WHERE i.presente = 1 AND s.arquivado_em IS NULL
    GROUP BY s.id ORDER BY presencas DESC, s.nome LIMIT 5")->fetchAll();

// Pagamentos recebidos por método
$porMetodo = db()->query("SELECT metodo, COUNT(*) AS n, SUM(valor) AS total
    FROM mensalidades WHERE estado = 'Paga' AND metodo IS NOT NULL
    GROUP BY metodo ORDER BY total DESC")->fetchAll();

// Aulas mais procuradas
$aulas = db()->query("SELECT m.nome AS modalidade, a.dia_semana, TIME_FORMAT(a.hora_inicio,'%H:%i') AS hora, a.lotacao_max,
    (SELECT COUNT(*) FROM inscricoes i WHERE i.aula_id = a.id) AS inscritos
    FROM aulas a JOIN modalidades m ON m.id = a.modalidade_id
    WHERE a.arquivado_em IS NULL ORDER BY inscritos DESC LIMIT 3")->fetchAll();

$euros = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' €';

layout_inicio('Dashboard', 'dashboard');
?>
<p class="eyebrow">Dashboard · <code>dashboard.ver</code></p>
<h1>Como está o ginásio</h1>

<div class="grelha">
  <div class="cartao kpi"><p>Sócios ativos</p><strong><?= (int) $ativos ?></strong><p>os arquivados não contam</p></div>
  <div class="cartao kpi"><p>Mensalidades em atraso</p><strong><?= (int) $atraso ?></strong><p>bloqueiam novas inscrições</p></div>
  <div class="cartao kpi"><p>Pagamentos recebidos</p><strong><?= $euros($recebido) ?></strong><p>total pago</p></div>
</div>

<div class="grelha">
  <section class="cartao">
    <h2>Melhores sócios</h2>
    <p class="demo">Quem foi a mais aulas.</p>
    <div class="tabela"><table>
      <thead><tr><th>#</th><th>Sócio</th><th>Presenças</th></tr></thead>
      <tbody>
      <?php foreach ($melhores as $i => $s): ?>
        <tr><td class="mono"><?= $i + 1 ?></td><td><strong><?= e($s['nome']) ?></strong> <span class="demo">nº <?= e($s['numero']) ?></span></td><td><?= (int) $s['presencas'] ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$melhores): ?><tr><td colspan="3">Ainda sem presenças marcadas.</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </section>

  <section class="cartao">
    <h2>Pagamentos por método</h2>
    <p class="demo">Receção, MB Way ou cartão.</p>
    <div class="tabela"><table>
      <thead><tr><th>Método</th><th>Pagamentos</th><th>Total</th></tr></thead>
      <tbody>
      <?php foreach ($porMetodo as $m): ?>
        <tr><td><strong><?= e($m['metodo']) ?></strong></td><td><?= (int) $m['n'] ?></td><td><?= $euros($m['total']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </section>

  <section class="cartao">
    <h2>Aulas mais procuradas</h2>
    <p class="demo">Inscritos face à lotação.</p>
    <div class="tabela"><table>
      <thead><tr><th>Aula</th><th>Quando</th><th>Inscritos</th></tr></thead>
      <tbody>
      <?php foreach ($aulas as $a): ?>
        <tr><td><strong><?= e($a['modalidade']) ?></strong></td><td><?= e($a['dia_semana']) ?> <span class="mono"><?= e($a['hora']) ?></span></td>
            <td><?= (int) $a['inscritos'] ?>/<?= (int) $a['lotacao_max'] ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </section>
</div>
<?php layout_fim();

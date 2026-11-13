<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

// O painel é para a equipa: basta ter uma permissão de back office.
$u = exigir('socios.ver', 'mensalidades.ver', 'aulas.ver', 'presencas.marcar', 'planos.ver', 'dashboard.ver', 'utilizadores.ver', 'perfis.ver');

$kpis = [];
if (pode('socios.ver')) {
    $kpis[] = ['Sócios', db()->query('SELECT COUNT(*) FROM socios WHERE arquivado_em IS NULL')->fetchColumn(), 'ativos'];
}
if (pode('mensalidades.ver')) {
    $kpis[] = ['Mensalidades em atraso', db()->query("SELECT COUNT(*) FROM mensalidades WHERE estado='Em atraso'")->fetchColumn(), 'bloqueiam novas inscrições'];
}
if (pode('aulas.ver')) {
    if (ambito('aulas.ver') === 'proprias') {
        $st = db()->prepare('SELECT COUNT(*) FROM aulas WHERE treinador_id = ? AND arquivado_em IS NULL');
        $st->execute([$u['id']]);
        $kpis[] = ['As minhas aulas', $st->fetchColumn(), 'por semana'];
    } else {
        $kpis[] = ['Aulas', db()->query('SELECT COUNT(*) FROM aulas WHERE arquivado_em IS NULL')->fetchColumn(), 'por semana'];
    }
}

layout_inicio('Painel', 'painel');
?>
<p class="eyebrow">Painel</p>
<h1>Olá, <?= e($u['nome']) ?></h1>
<p class="nota">Perfil <strong><?= e($u['perfil']) ?></strong>: o menu à esquerda e esta página mostram apenas o que este perfil pode usar.</p>

<div class="grelha">
  <?php foreach ($kpis as [$rot, $valor, $dica]): ?>
    <div class="cartao kpi"><p><?= e($rot) ?></p><strong><?= e((string) $valor) ?></strong><p><?= e($dica) ?></p></div>
  <?php endforeach; ?>
</div>

<section class="cartao">
  <h2>As tuas permissões</h2>
  <div class="tabela"><table>
    <thead><tr><th>Código</th><th>Âmbito</th></tr></thead>
    <tbody>
    <?php foreach ($u['permissoes'] as $codigo => $amb): ?>
      <tr><td><code><?= e($codigo) ?></code></td><td><?= $amb === 'proprias' ? '<span class="tag amarelo">só as suas</span>' : '<span class="tag verde">todas</span>' ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
<?php layout_fim();

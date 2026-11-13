<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

// Área do sócio: só para quem tem permissões de sócio no site
$u = exigir('mensalidades.proprias', 'aulas.inscrever');

$st = db()->prepare('SELECT s.*, p.nome AS plano FROM socios s JOIN planos p ON p.id = s.plano_id WHERE s.utilizador_id = ?');
$st->execute([$u['id']]);
$socio = $st->fetch();

layout_inicio('Área do sócio', 'area', 'area');
?>
<p class="eyebrow">Área do sócio</p>
<h1>Olá, <?= e($u['nome']) ?></h1>
<?php if ($socio): ?>
  <div class="grelha">
    <div class="cartao kpi"><p>Sócio nº</p><strong><?= e($socio['numero']) ?></strong><p>desde <?= e(date('d/m/Y', strtotime($socio['data_inscricao']))) ?></p></div>
    <div class="cartao kpi"><p>Plano</p><strong><?= e($socio['plano']) ?></strong><p>&nbsp;</p></div>
  </div>
<?php else: ?>
  <p class="nota">Esta conta ainda não está associada a uma ficha de sócio.</p>
<?php endif; ?>
<p class="nota">Perfil <strong><?= e($u['perfil']) ?></strong>: só vês as tuas mensalidades e as aulas onde te podes inscrever.
  Experimenta abrir <a href="/backoffice/socios.php">/backoffice/socios.php</a> — o servidor responde 403.</p>
<?php layout_fim();

<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

$u = exigir('aulas.inscrever');

$st = db()->prepare('SELECT id FROM socios WHERE utilizador_id = ?');
$st->execute([$u['id']]);
$socioId = (int) $st->fetchColumn();

/** Regra de negócio: mensalidade em atraso bloqueia novas inscrições. */
function em_atraso(int $socioId): bool
{
    $st = db()->prepare("SELECT COUNT(*) FROM mensalidades WHERE socio_id = ? AND estado = 'Em atraso'");
    $st->execute([$socioId]);
    return $st->fetchColumn() > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $socioId) {
    csrf_validar();
    $aulaId = (int) ($_POST['aula'] ?? 0);
    if (em_atraso($socioId)) {
        flash('Tens uma mensalidade em atraso: regulariza na receção para te inscreveres.');
    } else {
        $l = db()->prepare('SELECT a.lotacao_max, (SELECT COUNT(*) FROM inscricoes i WHERE i.aula_id = a.id) AS n FROM aulas a WHERE a.id = ? AND a.arquivado_em IS NULL');
        $l->execute([$aulaId]);
        $a = $l->fetch();
        if (!$a) {
            flash('Aula inexistente.');
        } elseif ($a['n'] >= $a['lotacao_max']) {
            flash('Esta aula está cheia.');
        } else {
            db()->prepare('INSERT IGNORE INTO inscricoes (aula_id, socio_id) VALUES (?, ?)')->execute([$aulaId, $socioId]);
            flash('Inscrição feita.');
        }
    }
    header('Location: /area/aulas.php');
    exit;
}

$st = db()->prepare("SELECT a.id, a.dia_semana, TIME_FORMAT(a.hora_inicio,'%H:%i') AS hora, a.sala, a.lotacao_max, m.nome AS modalidade,
        (SELECT COUNT(*) FROM inscricoes i WHERE i.aula_id = a.id) AS n,
        (SELECT COUNT(*) FROM inscricoes i WHERE i.aula_id = a.id AND i.socio_id = ?) AS minha
        FROM aulas a JOIN modalidades m ON m.id = a.modalidade_id
        WHERE a.arquivado_em IS NULL
        ORDER BY FIELD(a.dia_semana,'Segunda','Terça','Quarta','Quinta','Sexta','Sábado'), a.hora_inicio");
$st->execute([$socioId]);
$aulas = $st->fetchAll();
$bloqueado = $socioId && em_atraso($socioId);

layout_inicio('Inscrever em aulas', 'inscrever', 'area');
?>
<p class="eyebrow">Aulas · <code>aulas.inscrever</code></p>
<h1>Inscrever em aulas</h1>
<?php if ($bloqueado): ?><p class="aviso erro">Tens uma mensalidade em atraso: as inscrições estão bloqueadas.</p><?php endif; ?>
<section class="cartao"><div class="tabela"><table>
  <thead><tr><th>Dia</th><th>Hora</th><th>Aula</th><th>Sala</th><th>Lugares</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($aulas as $a): $cheia = $a['n'] >= $a['lotacao_max']; ?>
    <tr><td><?= e($a['dia_semana']) ?></td><td class="mono"><?= e($a['hora']) ?></td><td><strong><?= e($a['modalidade']) ?></strong></td><td><?= e($a['sala']) ?></td>
      <td><span class="tag <?= $cheia ? 'vermelho' : 'cinza' ?>"><?= (int) $a['n'] ?>/<?= (int) $a['lotacao_max'] ?></span></td>
      <td style="text-align:right">
        <?php if ($a['minha']): ?><span class="tag verde">Inscrito</span>
        <?php elseif (!$cheia && !$bloqueado && $socioId): ?>
          <form method="post"><?= csrf_campo() ?><button class="botao" name="aula" value="<?= (int) $a['id'] ?>">Inscrever</button></form>
        <?php endif; ?>
      </td></tr>
  <?php endforeach; ?>
  </tbody>
</table></div></section>
<?php layout_fim();

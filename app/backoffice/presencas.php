<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

$u = exigir('presencas.marcar');
$soAsMinhas = ambito('presencas.marcar') === 'proprias';

/** A aula pode ser gerida por este utilizador? (âmbito "só as suas" verificado no servidor) */
function aula_permitida(int $aulaId, array $u, bool $soAsMinhas): ?array
{
    $st = db()->prepare("SELECT a.id, a.treinador_id, a.dia_semana, TIME_FORMAT(a.hora_inicio,'%H:%i') AS hora, m.nome AS modalidade
                         FROM aulas a JOIN modalidades m ON m.id = a.modalidade_id WHERE a.id = ?");
    $st->execute([$aulaId]);
    $a = $st->fetch();
    if (!$a) return null;
    if ($soAsMinhas && (int) $a['treinador_id'] !== (int) $u['id']) return null;
    return $a;
}

$aulaId = (int) ($_GET['aula'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $aula = aula_permitida($aulaId, $u, $soAsMinhas);
    if (!$aula) { http_response_code(403); $necessaria = 'presencas.marcar (só as suas aulas)'; require __DIR__ . '/../inc/403.php'; exit; }
    $presentes = array_map('intval', $_POST['presente'] ?? []);
    $st = db()->prepare('UPDATE inscricoes SET presente = ? WHERE aula_id = ? AND socio_id = ?');
    $ins = db()->prepare('SELECT socio_id FROM inscricoes WHERE aula_id = ?');
    $ins->execute([$aulaId]);
    foreach ($ins->fetchAll(PDO::FETCH_COLUMN) as $sid) {
        $st->execute([in_array((int) $sid, $presentes, true) ? 1 : 0, $aulaId, $sid]);
    }
    flash('Presenças guardadas.');
    header('Location: /backoffice/presencas.php?aula=' . $aulaId);
    exit;
}

layout_inicio('Presenças', 'presencas');

if ($aulaId) {
    $aula = aula_permitida($aulaId, $u, $soAsMinhas);
    if (!$aula) {
        echo '<p class="aviso erro">Esta aula não existe ou não é tua: o perfil Treinador só marca presenças nas suas aulas.</p>';
        $aulaId = 0;
    }
}

if ($aulaId):
    $st = db()->prepare('SELECT s.id, s.numero, s.nome, i.presente FROM inscricoes i JOIN socios s ON s.id = i.socio_id WHERE i.aula_id = ? ORDER BY s.nome');
    $st->execute([$aulaId]);
    $inscritos = $st->fetchAll();
?>
  <p class="eyebrow"><a href="/backoffice/presencas.php">← Presenças</a></p>
  <h1><?= e($aula['modalidade']) ?> · <?= e($aula['dia_semana']) ?>, <?= e($aula['hora']) ?></h1>
  <form class="cartao" method="post"><?= csrf_campo() ?>
    <div class="tabela"><table>
      <thead><tr><th>Presente</th><th>Nº</th><th>Sócio</th></tr></thead>
      <tbody>
      <?php foreach ($inscritos as $i): ?>
        <tr><td><input type="checkbox" name="presente[]" value="<?= (int) $i['id'] ?>" aria-label="Presença de <?= e($i['nome']) ?>" <?= $i['presente'] ? 'checked' : '' ?>></td>
            <td class="mono"><?= e($i['numero']) ?></td><td><?= e($i['nome']) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$inscritos): ?><tr><td colspan="3">Ainda não há inscritos.</td></tr><?php endif; ?>
      </tbody>
    </table></div>
    <p><button class="botao" type="submit">Guardar presenças</button></p>
  </form>
<?php else:
    $sql = "SELECT a.id, a.dia_semana, TIME_FORMAT(a.hora_inicio,'%H:%i') AS hora, m.nome AS modalidade, u.nome AS treinador
            FROM aulas a JOIN modalidades m ON m.id = a.modalidade_id JOIN utilizadores u ON u.id = a.treinador_id
            WHERE a.arquivado_em IS NULL";
    if ($soAsMinhas) { $st = db()->prepare($sql . ' AND a.treinador_id = ? ORDER BY a.id'); $st->execute([$u['id']]); }
    else { $st = db()->query($sql . ' ORDER BY a.id'); }
?>
  <p class="eyebrow">Presenças · <code>presencas.marcar</code><?= $soAsMinhas ? ' · só as suas' : '' ?></p>
  <h1>Escolhe a aula</h1>
  <section class="cartao"><div class="tabela"><table>
    <thead><tr><th>Aula</th><th>Dia</th><th>Hora</th><th>Treinador</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($st->fetchAll() as $a): ?>
      <tr><td><strong><?= e($a['modalidade']) ?></strong></td><td><?= e($a['dia_semana']) ?></td><td class="mono"><?= e($a['hora']) ?></td>
          <td><?= e($a['treinador']) ?></td><td style="text-align:right"><a href="?aula=<?= (int) $a['id'] ?>">Marcar presenças</a></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div></section>
<?php endif;
layout_fim();

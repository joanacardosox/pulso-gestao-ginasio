<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/layout.php';

$u = exigir('aulas.ver');

// Arquivar / reativar aulas (exige aulas.arquivar). As inscrições e presenças ficam no histórico.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    exigir('aulas.arquivar');
    $arquivar = ($_POST['acao'] ?? '') === 'arquivar';
    db()->prepare('UPDATE aulas SET arquivado_em = ' . ($arquivar ? 'NOW()' : 'NULL') . ' WHERE id = ?')->execute([(int) ($_POST['aula'] ?? 0)]);
    flash($arquivar ? 'Aula arquivada: sai do horário, mas as inscrições e presenças ficam guardadas.' : 'Aula reativada.');
    header('Location: /backoffice/aulas.php' . ($arquivar ? '' : '?ver=arquivadas'));
    exit;
}
$verArquivadas = ($_GET['ver'] ?? '') === 'arquivadas' && pode('aulas.arquivar');
$soAsMinhas = ambito('aulas.ver') === 'proprias';

// Âmbito "só as suas": o filtro é aplicado na consulta SQL, não apenas escondido no ecrã.
$sql = "SELECT a.id, a.dia_semana, TIME_FORMAT(a.hora_inicio,'%H:%i') AS hora, a.sala, a.lotacao_max,
               m.nome AS modalidade, u.nome AS treinador,
               (SELECT COUNT(*) FROM inscricoes i WHERE i.aula_id = a.id) AS inscritos
        FROM aulas a JOIN modalidades m ON m.id = a.modalidade_id JOIN utilizadores u ON u.id = a.treinador_id
        WHERE a.arquivado_em IS " . ($verArquivadas ? 'NOT NULL' : 'NULL');
if ($soAsMinhas) {
    $st = db()->prepare($sql . ' AND a.treinador_id = ? ORDER BY FIELD(a.dia_semana,"Segunda","Terça","Quarta","Quinta","Sexta","Sábado"), a.hora_inicio');
    $st->execute([$u['id']]);
} else {
    $st = db()->query($sql . ' ORDER BY FIELD(a.dia_semana,"Segunda","Terça","Quarta","Quinta","Sexta","Sábado"), a.hora_inicio');
}
$aulas = $st->fetchAll();

layout_inicio('Aulas', 'aulas');
?>
<p class="eyebrow">Aulas · <code>aulas.ver</code><?= $soAsMinhas ? ' · só as suas' : '' ?></p>
<h1><?= $soAsMinhas ? 'As minhas aulas' : 'Horário semanal' ?></h1>
<?php if ($soAsMinhas): ?>
  <p class="nota">O teu perfil tem <code>aulas.ver</code> com âmbito <strong>só as suas</strong>: só aparecem as aulas em que és o treinador responsável.</p>
<?php endif; ?>
<?php if (pode('aulas.arquivar')): ?>
<p>
  <a class="botao<?= $verArquivadas ? ' sec' : '' ?>" href="/backoffice/aulas.php">No horário</a>
  <a class="botao<?= $verArquivadas ? '' : ' sec' ?>" href="/backoffice/aulas.php?ver=arquivadas">Arquivadas</a>
</p>
<?php endif; ?>
<p class="nota">
  <?= etiqueta_permissao('Criar', 'aulas.criar') ?> &nbsp;
  <?= etiqueta_permissao('Editar', 'aulas.editar') ?><?= ambito('aulas.editar') === 'proprias' ? ' (só as suas)' : '' ?> &nbsp;
  <?= etiqueta_permissao('Arquivar', 'aulas.arquivar') ?>
</p>
<section class="cartao"><div class="tabela"><table>
  <thead><tr><th>Dia</th><th>Hora</th><th>Aula</th><th>Sala</th><th>Treinador</th><th>Lotação</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($aulas as $a): $cheia = $a['inscritos'] >= $a['lotacao_max']; ?>
    <tr>
      <td><?= e($a['dia_semana']) ?></td><td class="mono"><?= e($a['hora']) ?></td>
      <td><strong><?= e($a['modalidade']) ?></strong></td><td><?= e($a['sala']) ?></td><td><?= e($a['treinador']) ?></td>
      <td><span class="tag <?= $cheia ? 'vermelho' : 'cinza' ?>"><?= (int) $a['inscritos'] ?>/<?= (int) $a['lotacao_max'] ?></span></td>
      <td style="text-align:right;white-space:nowrap">
        <?php if (pode('presencas.marcar') && !$verArquivadas): ?><a href="/backoffice/presencas.php?aula=<?= (int) $a['id'] ?>">Presenças</a><?php endif; ?>
        <?php if (pode('aulas.arquivar')): ?>
          <form method="post" style="display:inline;margin-left:8px"><?= csrf_campo() ?>
            <input type="hidden" name="aula" value="<?= (int) $a['id'] ?>">
            <button class="botao sec" name="acao" value="<?= $verArquivadas ? 'reativar' : 'arquivar' ?>" type="submit"><?= $verArquivadas ? 'Reativar' : 'Arquivar' ?></button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$aulas): ?><tr><td colspan="7"><?= $verArquivadas ? 'Não há aulas arquivadas.' : 'Sem aulas atribuídas.' ?></td></tr><?php endif; ?>
  </tbody>
</table></div></section>
<?php layout_fim();

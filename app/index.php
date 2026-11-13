<?php
require __DIR__ . '/inc/bootstrap.php';

// Quem já tem sessão vai para a sua área
if (utilizador()) { header('Location: ' . inicio_do_perfil()); exit; }

// Site público: o perfil Visitante (sem login) tem a permissão publico.ver
exigir('publico.ver');

$planos = db()->query('SELECT nome, duracao_meses, preco FROM planos WHERE arquivado_em IS NULL ORDER BY duracao_meses')->fetchAll();
$aulas  = db()->query("SELECT a.dia_semana, TIME_FORMAT(a.hora_inicio,'%H:%i') AS hora, m.nome AS modalidade, a.lotacao_max,
                       (SELECT COUNT(*) FROM inscricoes i WHERE i.aula_id = a.id) AS n
                       FROM aulas a JOIN modalidades m ON m.id = a.modalidade_id WHERE a.arquivado_em IS NULL
                       ORDER BY FIELD(a.dia_semana,'Segunda','Terça','Quarta','Quinta','Sexta','Sábado'), a.hora_inicio")->fetchAll();
$msg = flash();
?><!doctype html>
<html lang="pt-PT">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pulso · Ginásio</title><link rel="stylesheet" href="/assets/style.css"></head>
<body>
<main class="conteudo" style="max-width:980px;margin:0 auto">
  <?php if ($msg): ?><p class="aviso ok" role="status"><?= e($msg) ?></p><?php endif; ?>
  <p class="eyebrow">Site público · perfil Visitante · <code>publico.ver</code></p>
  <h1>Pulso<span style="color:var(--acento)">.</span> Treina ao teu ritmo.</h1>
  <p class="nota">Estás a ver o site como <strong>Visitante</strong> (sem login): vês os planos e o horário.
    Para te inscreveres nas aulas precisas de conta.</p>
  <p>
    <?php if (pode('conta.criar')): ?><a class="botao" href="/registo.php">Criar conta</a><?php endif; ?>
    <a class="botao sec" href="/login.php">Entrar</a>
  </p>

  <section class="cartao">
    <h2 style="margin-top:0">Planos</h2>
    <div class="grelha">
      <?php foreach ($planos as $p): ?>
        <div class="cartao kpi" style="margin:0"><p><?= e($p['nome']) ?></p><strong><?= number_format((float) $p['preco'], 0, ',', ' ') ?> €</strong>
          <p><?= (int) $p['duracao_meses'] ?> <?= $p['duracao_meses'] == 1 ? 'mês' : 'meses' ?></p></div>
      <?php endforeach; ?>
    </div>
    <p class="demo">Pagamento na receção, por MB Way ou com cartão.</p>
  </section>

  <section class="cartao"><h2 style="margin-top:0">Horário</h2><div class="tabela"><table>
    <thead><tr><th>Dia</th><th>Hora</th><th>Aula</th><th>Lugares</th></tr></thead>
    <tbody>
    <?php foreach ($aulas as $a): $cheia = $a['n'] >= $a['lotacao_max']; ?>
      <tr><td><?= e($a['dia_semana']) ?></td><td class="mono"><?= e($a['hora']) ?></td><td><strong><?= e($a['modalidade']) ?></strong></td>
          <td><span class="tag <?= $cheia ? 'vermelho' : 'cinza' ?>"><?= $cheia ? 'Cheia' : ((int) $a['lotacao_max'] - (int) $a['n']) . ' livres' ?></span></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div></section>
</main>
</body>
</html>

<?php
// Pulso · moldura comum das páginas autenticadas.
// Uso: layout_inicio('Título', 'chave-ativa'); ... layout_fim();

/** Itens do menu do back office e a permissão que cada um exige. */
function menu_backoffice(): array
{
    return [
        ['painel',       'Painel',              '/backoffice/index.php',        null],
        ['dashboard',    'Dashboard',           '/backoffice/dashboard.php',    'dashboard.ver'],
        ['socios',       'Sócios',              '/backoffice/socios.php',       'socios.ver'],
        ['planos',       'Planos',              '/backoffice/planos.php',       'planos.ver'],
        ['mensalidades', 'Mensalidades',        '/backoffice/mensalidades.php', 'mensalidades.ver'],
        ['aulas',        ambito('aulas.ver') === 'proprias' ? 'As minhas aulas' : 'Aulas', '/backoffice/aulas.php', 'aulas.ver'],
        ['presencas',    'Presenças',           '/backoffice/presencas.php',    'presencas.marcar'],
        ['utilizadores', 'Equipa',              '/backoffice/utilizadores.php', 'utilizadores.ver'],
        ['perfis',       'Perfis e permissões', '/backoffice/perfis.php',       'perfis.ver'],
    ];
}

function menu_area(): array
{
    return [
        ['area',         'Início',        '/area/index.php',        null],
        ['inscrever',    'Inscrever',     '/area/aulas.php',        'aulas.inscrever'],
        ['mensalidades', 'Mensalidades',  '/area/mensalidades.php', 'mensalidades.proprias'],
    ];
}

function layout_inicio(string $titulo, string $ativo, string $zona = 'backoffice'): void
{
    $u = utilizador();
    $itens = $zona === 'area' ? menu_area() : menu_backoffice();
    $msg = flash();
    ?><!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo) ?> · Pulso</title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="app">
  <aside class="menu" aria-label="Menu">
    <a class="logo" href="<?= $zona === 'area' ? '/area/index.php' : '/backoffice/index.php' ?>">Pulso<span>.</span></a>
    <p class="mono zona"><?= $zona === 'area' ? 'Área do sócio' : 'Back office' ?></p>
    <nav>
      <?php foreach ($itens as [$chave, $rotulo, $url, $perm]):
          if ($perm !== null && !pode($perm)) continue; // o menu só mostra o que o perfil pode usar ?>
        <a href="<?= e($url) ?>"<?= $chave === $ativo ? ' class="ativo" aria-current="page"' : '' ?>><?= e($rotulo) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="quem">
      <div><strong><?= e($u['nome']) ?></strong><span><?= e($u['perfil']) ?></span></div>
      <form method="post" action="/logout.php"><?= csrf_campo() ?><button type="submit">Sair</button></form>
    </div>
  </aside>
  <main class="conteudo">
    <?php if ($msg): ?><p class="aviso ok" role="status"><?= e($msg) ?></p><?php endif; ?>
<?php
}

function layout_fim(): void
{
    ?>
  </main>
</div>
</body>
</html>
<?php
}

/** Etiqueta "permitido / sem permissão" para mostrar o que o perfil pode fazer numa página. */
function etiqueta_permissao(string $rotulo, string $codigo): string
{
    $ok = pode($codigo);
    return e($rotulo) . ': <span class="tag ' . ($ok ? 'verde' : 'cinza') . '" title="' . e($codigo) . '">'
         . ($ok ? 'permitido' : 'sem permissão') . '</span>';
}

/** Etiqueta colorida para estados de mensalidade. */
function etiqueta_estado(string $estado): string
{
    $cls = ['Paga' => 'verde', 'Pendente' => 'amarelo', 'Em atraso' => 'vermelho'][$estado] ?? 'cinza';
    return '<span class="tag ' . $cls . '">' . e($estado) . '</span>';
}

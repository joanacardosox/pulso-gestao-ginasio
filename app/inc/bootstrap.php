<?php
// Pulso · arranque comum: sessão, base de dados e funções de RBAC.
declare(strict_types=1);

session_start();

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST') ?: 'db', getenv('DB_NAME') ?: 'pulso');
        $pdo = new PDO($dsn, getenv('DB_USER') ?: 'pulso', getenv('DB_PASS') ?: 'pulso', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/** Escapa texto para HTML. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// ---------------- Autenticação ----------------

/** Utilizador autenticado (ou null), com o perfil e as permissões lidos da base de dados em cada pedido. */
function utilizador(): ?array
{
    static $u = false;
    if ($u !== false) return $u;
    $u = null;
    if (empty($_SESSION['uid'])) return null;

    $st = db()->prepare('SELECT u.id, u.nome, u.email, u.perfil_id, p.nome AS perfil
                         FROM utilizadores u JOIN perfis p ON p.id = u.perfil_id
                         WHERE u.id = ? AND u.ativo = 1');
    $st->execute([$_SESSION['uid']]);
    $row = $st->fetch();
    if (!$row) { logout(); return null; }

    // Permissões do perfil: [codigo => 'todas' | 'proprias']
    $st = db()->prepare('SELECT pe.codigo, pp.ambito FROM perfil_permissao pp
                         JOIN permissoes pe ON pe.id = pp.permissao_id WHERE pp.perfil_id = ?');
    $st->execute([$row['perfil_id']]);
    $row['permissoes'] = array_column($st->fetchAll(), 'ambito', 'codigo');
    return $u = $row;
}

function login(string $email, string $password): bool
{
    $st = db()->prepare('SELECT id, password_hash FROM utilizadores WHERE email = ? AND ativo = 1');
    $st->execute([trim($email)]);
    $row = $st->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);      // evita fixação de sessão
    $_SESSION['uid'] = (int) $row['id'];
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ---------------- Autorização (RBAC) ----------------

/**
 * Permissões de quem está a usar o site: as do perfil do utilizador autenticado
 * ou, sem login, as do perfil "Visitante" (também lidas da base de dados).
 * Formato: [codigo => 'todas' | 'proprias']
 */
function permissoes_atuais(): array
{
    $u = utilizador();
    if ($u !== null) return $u['permissoes'];

    static $visitante = null;
    if ($visitante === null) {
        $st = db()->query("SELECT pe.codigo, pp.ambito FROM perfil_permissao pp
                           JOIN permissoes pe ON pe.id = pp.permissao_id
                           JOIN perfis p ON p.id = pp.perfil_id WHERE p.nome = 'Visitante'");
        $visitante = array_column($st->fetchAll(), 'ambito', 'codigo');
    }
    return $visitante;
}

/** Quem está a usar o site (utilizador ou visitante) tem a permissão? */
function pode(string $codigo): bool
{
    return array_key_exists($codigo, permissoes_atuais());
}

/** Âmbito da permissão: 'todas' ou 'proprias' (só os registos do próprio utilizador). */
function ambito(string $codigo): ?string
{
    return permissoes_atuais()[$codigo] ?? null;
}

/** Exige sessão iniciada; senão manda para o login. */
function exigir_login(): array
{
    $u = utilizador();
    if ($u === null) {
        header('Location: /login.php?voltar=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        exit;
    }
    return $u;
}

/**
 * Exige uma permissão; sem ela responde 403. Aceita várias (basta uma).
 * Se o Visitante tiver a permissão, a página abre sem login (devolve null).
 * Se não tiver e não houver sessão, manda para o login.
 */
function exigir(string ...$codigos): ?array
{
    foreach ($codigos as $c) {
        if (pode($c)) return utilizador();
    }
    exigir_login();
    http_response_code(403);
    $titulo = 'Sem permissão';
    $necessaria = implode(' ou ', $codigos);
    require __DIR__ . '/403.php';
    exit;
}

// ---------------- CSRF ----------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    $esperado = $_SESSION['csrf'] ?? '';
    // token vazio nunca é válido (senão uma sessão sem token aceitaria um pedido com token vazio)
    if ($esperado === '' || !hash_equals($esperado, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Pedido inválido (CSRF).');
    }
}

/** Mensagem curta para mostrar depois de um redirecionamento. */
function flash(?string $msg = null): ?string
{
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

/** Página inicial de cada perfil depois do login. */
function inicio_do_perfil(): string
{
    if (utilizador() === null) return '/index.php';
    return pode('mensalidades.proprias') && !pode('socios.ver') && !pode('aulas.ver')
        ? '/area/index.php'
        : '/backoffice/index.php';
}

/** Id de um perfil pelo nome (ex.: 'Sócio'). */
function perfil_id(string $nome): int
{
    $st = db()->prepare('SELECT id FROM perfis WHERE nome = ?');
    $st->execute([$nome]);
    return (int) $st->fetchColumn();
}

/** Métodos de pagamento aceites (iguais ao ENUM da coluna mensalidades.metodo). */
const METODOS_PAGAMENTO = ['Receção', 'MB Way', 'Cartão'];

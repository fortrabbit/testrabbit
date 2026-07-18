<?php
// load-sql.php — DB-bound CONTRAST for the PHP-plan-scaling test. NOT isolated.
header('Content-Type: application/json');
$env = fn(string ...$k) => (function() use ($k){ foreach($k as $x){ $v=getenv($x); if($v!==false&&$v!=='') return $v; } return null; })();
$host = $env('MYSQL_HOST','DB_HOST','APP_DATABASE_HOST') ?? '127.0.0.1';
$db   = $env('MYSQL_DATABASE','DB_DATABASE','APP_DATABASE_NAME') ?? 'testrabbit';
$user = $env('MYSQL_USER','DB_USERNAME','APP_DATABASE_USER') ?? 'root';
$pass = $env('MYSQL_PASSWORD','DB_PASSWORD','APP_DATABASE_PASSWORD') ?? '';
$port = $env('MYSQL_PORT','DB_PORT','APP_DATABASE_PORT') ?? '3306';

$t0 = microtime(true);
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if (isset($_GET['seed'])) {
        $n = max(0, (int) $_GET['seed']);
        $pdo->exec("CREATE TABLE IF NOT EXISTS loadtest (id INT PRIMARY KEY AUTO_INCREMENT, payload VARCHAR(255))");
        $have = (int) $pdo->query("SELECT COUNT(*) FROM loadtest")->fetchColumn();
        $ins = $pdo->prepare("INSERT INTO loadtest (payload) VALUES (?)");
        for ($i = $have; $i < $n; $i++) { $ins->execute([bin2hex(random_bytes(64))]); }
        echo json_encode(['seeded' => max($n, $have), 'added' => max(0, $n - $have)]); exit;
    }
    $rows = max(1, (int) ($_GET['rows'] ?? 1000));
    $data = $pdo->query("SELECT * FROM loadtest LIMIT $rows")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['rows' => $rows, 'fetched' => count($data), 'host' => gethostname(),
        'elapsed_ms' => round((microtime(true) - $t0) * 1000.0, 1)]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

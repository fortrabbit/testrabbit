<?php

/**
 * /synth/db-read.php — indexed reads on a seeded table: buffer-pool/engine speed.
 *
 * Seeds `synth_read` once (deterministic content, outside the timed section;
 * `seeded` + `seed_ms` in the output say when that happened), then on one open
 * connection measures:
 *   point_ms — reps × primary-key lookup at pseudo-random ids (fixed seed, so
 *              runs are comparable) via one prepared statement
 *   range_ms — range_reps × `SELECT SUM(n)` over a random 1000-row PK window
 *
 * Subtract db-query's SELECT-1 median (pure RTT + dispatch) to get engine time.
 * Caveat: repeated runs keep the table's ~few MB warm in the buffer pool — this
 * measures warm-engine speed and eviction-induced tail spikes, not cold misses.
 *
 * Params: ?rows=10000 (1k–100k, table size — reseeds when changed)
 *         ?reps=200 (1–2000) ?range_reps=10 (0–100) ?seed=1337 ?reset=1
 */

declare(strict_types=1);

require __DIR__.'/_lib.php';

$config = synth_db_config();
$rows = synth_param('rows', 10000, 1000, 100000);
$reps = synth_param('reps', 200, 1, 2000);
$rangeReps = synth_param('range_reps', 10, 0, 100);
$seed = synth_param('seed', 1337, 0, PHP_INT_MAX);
$start = hrtime(true);

try {
    $pdo = synth_connect($config, $config['host']);
} catch (PDOException $e) {
    synth_fail("connect failed: {$e->getMessage()}");
}

$pdo->exec('CREATE TABLE IF NOT EXISTS synth_read (
    id INT UNSIGNED NOT NULL PRIMARY KEY,
    n INT NOT NULL,
    pad VARCHAR(255) NOT NULL,
    KEY synth_read_n (n)
) ENGINE=InnoDB');

$count = (int) $pdo->query('SELECT COUNT(*) FROM synth_read')->fetchColumn();
$seedMs = null;
if (isset($_GET['reset']) || $count !== $rows) {
    $t = hrtime(true);
    $pdo->exec('TRUNCATE TABLE synth_read');
    for ($offset = 1; $offset <= $rows; $offset += 500) {
        $values = [];
        for ($id = $offset; $id < $offset + 500 && $id <= $rows; $id++) {
            $pad = substr(str_repeat(md5((string) $id), 7), 0, 200);
            $values[] = sprintf("(%d, %d, '%s')", $id, crc32((string) $id) % 100000, $pad);
        }
        $pdo->exec('INSERT INTO synth_read (id, n, pad) VALUES '.implode(',', $values));
    }
    $seedMs = round((hrtime(true) - $t) / 1e6, 1);
}

mt_srand($seed);

$stmt = $pdo->prepare('SELECT pad FROM synth_read WHERE id = ?');
for ($i = 0; $i < 5; $i++) {
    $stmt->execute([mt_rand(1, $rows)]);
    $stmt->fetchColumn();
}

$point = [];
for ($i = 0; $i < $reps; $i++) {
    $id = mt_rand(1, $rows);
    $t = hrtime(true);
    $stmt->execute([$id]);
    $stmt->fetchColumn();
    $point[] = (hrtime(true) - $t) / 1e6;
}

$range = [];
if ($rangeReps > 0) {
    $rangeStmt = $pdo->prepare('SELECT SUM(n) FROM synth_read WHERE id BETWEEN ? AND ?');
    for ($i = 0; $i < $rangeReps; $i++) {
        $lo = mt_rand(1, max(1, $rows - 1000));
        $t = hrtime(true);
        $rangeStmt->execute([$lo, $lo + 999]);
        $rangeStmt->fetchColumn();
        $range[] = (hrtime(true) - $t) / 1e6;
    }
}

synth_json([
    'test' => 'db-read',
    'php' => PHP_VERSION,
    'db_host' => $config['host'],
    'credential_source' => $config['source'],
    'table_rows' => $rows,
    'seeded' => $seedMs !== null,
    'seed_ms' => $seedMs,
    'point_ms' => synth_stats($point),
    'range_ms' => $range ? synth_stats($range) : null,
    'total_ms' => round((hrtime(true) - $start) / 1e6, 1),
]);

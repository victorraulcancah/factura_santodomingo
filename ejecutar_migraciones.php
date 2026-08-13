<?php
/**
 * EJECUTOR DE MIGRACIONES SQL desde la web
 *
 * Ejecuta TODOS los archivos .sql en el orden correcto. Es idempotente:
 * si una sentencia ya se aplico antes (columna ya existe, tabla ya creada),
 * se marca como "ya aplicado" y continua.
 *
 * Ejecutar:
 *   http://localhost/Santo/factura_santodomingo/ejecutar_migraciones.php
 *
 * Si quieres ejecutar uno solo:
 *   http://localhost/Santo/factura_santodomingo/ejecutar_migraciones.php?archivo=planilla.sql
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . '/utils/config.php';
require_once __DIR__ . '/config/Conexion.php';

$conn = (new Conexion())->getConexion();

// ORDEN obligatorio de los SQL (los nuevos al final)
$ORDEN = [
    'actualizacion_clientes.sql',       // 4 cols a clientes (ubigeo)
    'planilla.sql',                      // 5 cols a usuarios (sueldo/comision)
    'migracion_unidades_caja.sql',       // 2 cols a productos (cajas)
    'migracion_unidades_derivadas.sql',  // tabla unidades_derivadas + 1 col a productos
    'migracion_planilla_pagos.sql',      // tabla planilla_pagos
];

// Errores que significan "ya aplicado" (no son fallas reales)
$ERRORES_IGNORABLES = [
    'Duplicate column',
    'Duplicate key name',
    'Duplicate foreign key',
    'already exists',
    'Duplicate entry',
];

function esIgnorable($error, $ignorables) {
    foreach ($ignorables as $ig) {
        if (stripos($error, $ig) !== false) return true;
    }
    return false;
}

/**
 * Divide un SQL en sentencias respetando bloques delimitados.
 * Maneja PREPARE/EXECUTE y SELECT de verificacion.
 */
function partirSQL($sql) {
    // Quitar comentarios de linea (--)
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    // Quitar comentarios multilinea
    $sql = preg_replace('!/\*.*?\*/!s', '', $sql);

    // Partir por ; pero NO dentro de strings
    $sentencias = [];
    $buffer = '';
    $enString = false;
    $delimitador = '';
    for ($i = 0; $i < strlen($sql); $i++) {
        $c = $sql[$i];
        if (!$enString && ($c === "'" || $c === '"')) {
            $enString = true;
            $delimitador = $c;
        } elseif ($enString && $c === $delimitador && ($i === 0 || $sql[$i - 1] !== '\\')) {
            $enString = false;
        }
        if (!$enString && $c === ';') {
            $s = trim($buffer);
            if ($s !== '') $sentencias[] = $s;
            $buffer = '';
        } else {
            $buffer .= $c;
        }
    }
    if (trim($buffer) !== '') $sentencias[] = trim($buffer);
    return $sentencias;
}

function ejecutarArchivo($conn, $ruta, $ignorables) {
    $res = ['archivo' => basename($ruta), 'ok' => 0, 'ya_aplicado' => 0, 'error' => 0, 'detalles' => []];
    if (!file_exists($ruta)) {
        $res['error'] = 1;
        $res['detalles'][] = ['estado' => 'ERROR', 'msg' => 'Archivo no existe', 'sql' => ''];
        return $res;
    }
    $sql = file_get_contents($ruta);
    $sentencias = partirSQL($sql);

    foreach ($sentencias as $s) {
        // Saltar SELECTs de verificacion que solo imprimen avisos
        $sUpper = strtoupper(trim($s));
        if (preg_match('/^SELECT\s+\'.*\'\s+AS\s+\w+\s*$/i', $s)) {
            $res['detalles'][] = ['estado' => 'SKIP', 'msg' => 'Aviso informativo', 'sql' => substr($s, 0, 80)];
            continue;
        }
        if (preg_match('/^SHOW\s+/i', $s)) {
            $res['detalles'][] = ['estado' => 'SKIP', 'msg' => 'SHOW (informativo)', 'sql' => substr($s, 0, 80)];
            continue;
        }

        $r = @$conn->query($s);
        if ($r === false) {
            $err = $conn->error;
            if (esIgnorable($err, $ignorables)) {
                $res['ya_aplicado']++;
                $res['detalles'][] = ['estado' => 'YA', 'msg' => 'Ya aplicado: ' . $err, 'sql' => substr($s, 0, 80)];
            } else {
                $res['error']++;
                $res['detalles'][] = ['estado' => 'ERROR', 'msg' => $err, 'sql' => substr($s, 0, 200)];
            }
        } else {
            $res['ok']++;
            $res['detalles'][] = ['estado' => 'OK', 'msg' => '', 'sql' => substr($s, 0, 80)];
            // Si era un SELECT con resultados, consumirlos
            if ($r instanceof mysqli_result) $r->free();
            // Drenar resultados extra (multi_query / PREPARE)
            while ($conn->more_results() && $conn->next_result()) {
                $extra = $conn->store_result();
                if ($extra) $extra->free();
            }
        }
    }
    return $res;
}

// ====================================================================
// ENTRADA / FRONTEND HTML
// ====================================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ejecutor de Migraciones SQL</title>
<style>
    body { font-family: monospace; background:#f4f4f4; padding:20px; max-width:1200px; margin:0 auto; }
    h1 { color:#333; border-bottom:2px solid #0866c6; padding-bottom:10px; }
    h2 { background:#0866c6; color:white; padding:8px 12px; border-radius:4px; margin-top:30px; }
    .archivo { background:white; padding:15px; border-radius:8px; margin:10px 0; box-shadow:0 2px 4px #0001; }
    table { width:100%; border-collapse:collapse; margin-top:10px; font-size:13px; }
    th { background:#eee; padding:6px; text-align:left; }
    td { padding:6px; border-bottom:1px solid #eee; vertical-align:top; }
    .OK    { color:green; font-weight:bold; }
    .YA    { color:#888; font-weight:bold; }
    .ERROR { color:red; font-weight:bold; }
    .SKIP  { color:#aaa; }
    code { background:#f0f0f0; padding:2px 4px; border-radius:3px; word-break:break-word; }
    .resumen { background:#fff; padding:20px; border-radius:8px; margin-top:30px; font-size:16px; border:2px solid #0866c6; }
    .badge { padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold; color:white; }
    .badge.ok { background:#10b981; }
    .badge.ya { background:#999; }
    .badge.error { background:#ef4444; }
    a.btn { background:#0866c6; color:white; padding:10px 20px; text-decoration:none; border-radius:4px; }
</style>
</head>
<body>

<h1>🛠️ Ejecutor de Migraciones SQL</h1>

<?php
$archivoUnico = $_GET['archivo'] ?? null;
$archivos = $archivoUnico ? [$archivoUnico] : $ORDEN;

$totalOk = 0; $totalYa = 0; $totalErr = 0;

foreach ($archivos as $arch) {
    $ruta = __DIR__ . '/' . $arch;
    echo "<div class='archivo'>";
    echo "<h2>📄 $arch</h2>";

    $r = ejecutarArchivo($conn, $ruta, $ERRORES_IGNORABLES);
    $totalOk += $r['ok'];
    $totalYa += $r['ya_aplicado'];
    $totalErr += $r['error'];

    echo "<p>";
    echo "<span class='badge ok'>{$r['ok']} ejecutadas</span> ";
    echo "<span class='badge ya'>{$r['ya_aplicado']} ya aplicadas</span> ";
    if ($r['error'] > 0) echo "<span class='badge error'>{$r['error']} errores</span> ";
    echo "</p>";

    echo "<table><thead><tr><th style='width:80px'>Estado</th><th>SQL</th><th>Mensaje</th></tr></thead><tbody>";
    foreach ($r['detalles'] as $d) {
        echo "<tr>";
        echo "<td class='{$d['estado']}'>{$d['estado']}</td>";
        echo "<td><code>" . htmlspecialchars(preg_replace('/\s+/', ' ', $d['sql'])) . "</code></td>";
        echo "<td>" . htmlspecialchars($d['msg']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "</div>";
    @ob_flush(); @flush();
}

echo "<div class='resumen'>";
echo "<h2 style='margin-top:0'>📊 RESUMEN TOTAL</h2>";
echo "<p>";
echo "<span class='badge ok'>$totalOk ejecutadas</span> ";
echo "<span class='badge ya'>$totalYa ya aplicadas</span> ";
if ($totalErr > 0) {
    echo "<span class='badge error'>$totalErr ERRORES</span> ";
    echo "</p><p style='color:red'><b>⚠ Revisa los errores arriba.</b></p>";
} else {
    echo "</p><p style='color:green; font-size:18px'><b>✅ Todo OK — base de datos sincronizada.</b></p>";
}
echo "</div>";
?>

<p style="margin-top:30px;">
    <a class="btn" href="?">▶ Re-ejecutar todo</a>
    &nbsp;&nbsp;
    <a href="/Santo/factura_santodomingo/" style="color:#0866c6">← Volver al sistema</a>
</p>

<p style="color:#888; font-size:12px; margin-top:30px;">
    <b>⚠ Este script ejecuta SQL directamente en tu BD. No lo dejes accesible en producción públicamente.</b>
    Después de subir a producción y aplicar la migración, recomendamos <b>borrar este archivo</b>.
</p>

</body>
</html>

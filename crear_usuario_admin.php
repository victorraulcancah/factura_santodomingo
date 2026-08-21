<?php
/**
 * CREAR USUARIO ADMIN (para pruebas en produccion)
 *
 * Crea un usuario con rol ADMIN (id_rol = 1) en la tabla `usuarios`, listo para
 * iniciar sesion en /login con usuario (o email) + clave.
 *
 * Desde el navegador (igual que ejecutar_migraciones.php):
 *   http://TU-DOMINIO/crear_usuario_admin.php
 *   -> muestra un formulario; elige la empresa, escribe usuario y clave, y guarda.
 *
 * Desde consola:
 *   php crear_usuario_admin.php --listar
 *   php crear_usuario_admin.php --usuario=admin_prueba --clave=MiClave123 --empresa=12
 *   php crear_usuario_admin.php --usuario=admin_prueba --clave=Otra456 --empresa=12 --reset
 *
 *   Opcionales: --email= --nombres= --apellidos= --num_doc=
 *   --reset   : si el usuario ya existe, le cambia la clave, lo pone ADMIN y lo activa.
 *   --empresa : puede omitirse si solo hay una empresa registrada.
 *
 * SEGURIDAD: si defines $SECRET abajo, el script exigira esa clave (campo "Clave del
 * script" en el formulario o --secret= en consola). Borra este archivo del servidor
 * apenas termines de usarlo.
 */

$SECRET = ''; // ej. 'x9Kp-2026'. Vacio = sin proteccion (solo para uso momentaneo).

ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . '/utils/config.php';
require_once __DIR__ . '/config/Conexion.php';

$conn = (new Conexion())->getConexion();
if ($conn->connect_error) {
    die('Error de conexion a la BD: ' . $conn->connect_error);
}
$conn->set_charset('utf8');

const ROL_ADMIN = 1;

// ====================================================================
// LOGICA
// ====================================================================

function listarEmpresas(mysqli $conn) {
    $lista = [];
    $r = $conn->query("SELECT id_empresa, ruc, razon_social, comercial FROM empresas ORDER BY id_empresa");
    if ($r) while ($row = $r->fetch_assoc()) $lista[] = $row;
    return $lista;
}

function listarAdmins(mysqli $conn) {
    $lista = [];
    $r = $conn->query("SELECT usuario_id, id_empresa, usuario, email, nombres, apellidos, estado, fecha_create
                       FROM usuarios WHERE id_rol = " . ROL_ADMIN . " ORDER BY usuario_id");
    if ($r) while ($row = $r->fetch_assoc()) $lista[] = $row;
    return $lista;
}

function buscarUsuario(mysqli $conn, $usuario, $email) {
    $stmt = $conn->prepare("SELECT usuario_id, id_empresa, id_rol, estado FROM usuarios
                            WHERE usuario = ? OR (? <> '' AND email = ?) LIMIT 1");
    $stmt->bind_param('sss', $usuario, $email, $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/**
 * @return array ['ok' => bool, 'msg' => string, 'id' => int|null, 'accion' => 'creado'|'actualizado'|null]
 */
function crearAdmin(mysqli $conn, array $d, $reset = false) {
    $usuario   = trim($d['usuario'] ?? '');
    $clave     = (string)($d['clave'] ?? '');
    $empresa   = (int)($d['empresa'] ?? 0);
    $email     = trim($d['email'] ?? '');
    $nombres   = trim($d['nombres'] ?? '');
    $apellidos = trim($d['apellidos'] ?? '');
    $num_doc   = trim($d['num_doc'] ?? '');

    if (strlen($usuario) < 3 || preg_match('/\s/', $usuario)) {
        return ['ok' => false, 'msg' => 'El usuario debe tener al menos 3 caracteres y no llevar espacios.'];
    }
    if (strlen($clave) < 4) {
        return ['ok' => false, 'msg' => 'La clave debe tener al menos 4 caracteres.'];
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'msg' => 'El email no es valido.'];
    }

    $empresas = listarEmpresas($conn);
    if ($empresa <= 0 && count($empresas) === 1) {
        $empresa = (int)$empresas[0]['id_empresa'];
    }
    $empresaOk = false;
    foreach ($empresas as $e) if ((int)$e['id_empresa'] === $empresa) $empresaOk = true;
    if (!$empresaOk) {
        return ['ok' => false, 'msg' => "La empresa $empresa no existe. Empresas disponibles: "
            . implode(', ', array_map(fn($e) => $e['id_empresa'] . ' (' . $e['razon_social'] . ')', $empresas))];
    }

    $claveHash = sha1($clave); // mismo hash que usa Usuario::login()
    $existente = buscarUsuario($conn, $usuario, $email);

    if ($existente) {
        if (!$reset) {
            return ['ok' => false, 'msg' => "Ya existe el usuario '$usuario' (id {$existente['usuario_id']}, rol {$existente['id_rol']}, estado {$existente['estado']}). "
                . "Usa la opcion 'reset' para cambiarle la clave y dejarlo como ADMIN activo."];
        }
        $stmt = $conn->prepare("UPDATE usuarios SET clave = ?, id_rol = " . ROL_ADMIN . ", estado = 1, id_empresa = ? WHERE usuario_id = ?");
        $id = (int)$existente['usuario_id'];
        $stmt->bind_param('sii', $claveHash, $empresa, $id);
        $ok = $stmt->execute();
        $err = $conn->error;
        $stmt->close();
        if (!$ok) return ['ok' => false, 'msg' => 'Error al actualizar: ' . $err];
        return ['ok' => true, 'id' => $id, 'accion' => 'actualizado',
            'msg' => "Usuario '$usuario' (id $id) actualizado: nueva clave, rol ADMIN, estado activo, empresa $empresa."];
    }

    $nombresApellidos = trim($nombres . ' ' . $apellidos);
    $stmt = $conn->prepare("INSERT INTO usuarios
        (id_empresa, id_rol, num_doc, usuario, clave, email, nombres_apellidos, nombres, apellidos,
         direccion, sucursal, telefono, estado, tipo_sueldo)
        VALUES (?, " . ROL_ADMIN . ", ?, ?, ?, ?, ?, ?, ?, '', 1, '', 1, 1)");
    $stmt->bind_param('isssssss', $empresa, $num_doc, $usuario, $claveHash, $email, $nombresApellidos, $nombres, $apellidos);
    $ok = $stmt->execute();
    $err = $conn->error;
    $id = $conn->insert_id;
    $stmt->close();
    if (!$ok) return ['ok' => false, 'msg' => 'Error al insertar: ' . $err];
    return ['ok' => true, 'id' => $id, 'accion' => 'creado',
        'msg' => "Usuario ADMIN '$usuario' creado con id $id en la empresa $empresa."];
}

function verificarSecret($SECRET, $dado) {
    return $SECRET === '' || hash_equals($SECRET, (string)$dado);
}

// ====================================================================
// MODO CONSOLA
// ====================================================================
if (php_sapi_name() === 'cli') {
    $opts = getopt('', ['usuario:', 'clave:', 'empresa:', 'email:', 'nombres:', 'apellidos:', 'num_doc:', 'secret:', 'reset', 'listar', 'help']);

    if (isset($opts['help']) || (empty($opts) && $argc > 0)) {
        echo "Uso:\n";
        echo "  php crear_usuario_admin.php --listar\n";
        echo "  php crear_usuario_admin.php --usuario=admin_prueba --clave=MiClave123 [--empresa=ID] [--email=] [--nombres=] [--apellidos=] [--num_doc=] [--reset]\n";
        exit(0);
    }
    if (!verificarSecret($SECRET, $opts['secret'] ?? '')) {
        fwrite(STDERR, "Clave del script incorrecta (--secret=...).\n");
        exit(1);
    }
    if (isset($opts['listar'])) {
        echo "EMPRESAS:\n";
        foreach (listarEmpresas($conn) as $e) {
            echo "  [{$e['id_empresa']}] RUC {$e['ruc']} - {$e['razon_social']}\n";
        }
        echo "USUARIOS ADMIN:\n";
        foreach (listarAdmins($conn) as $u) {
            echo "  id {$u['usuario_id']} | empresa {$u['id_empresa']} | {$u['usuario']} | {$u['email']} | {$u['nombres']} {$u['apellidos']} | estado {$u['estado']}\n";
        }
        exit(0);
    }

    $res = crearAdmin($conn, $opts, isset($opts['reset']));
    echo ($res['ok'] ? 'OK: ' : 'ERROR: ') . $res['msg'] . "\n";
    if ($res['ok']) {
        echo "Ingresa en /login con usuario '{$opts['usuario']}' y la clave que indicaste.\n";
        echo "Recuerda BORRAR crear_usuario_admin.php del servidor cuando termines.\n";
    }
    exit($res['ok'] ? 0 : 1);
}

// ====================================================================
// MODO WEB
// ====================================================================
$resultado = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificarSecret($SECRET, $_POST['secret'] ?? '')) {
        $resultado = ['ok' => false, 'msg' => 'Clave del script incorrecta.'];
    } else {
        $resultado = crearAdmin($conn, $_POST, !empty($_POST['reset']));
    }
}
$empresas = listarEmpresas($conn);
$admins = listarAdmins($conn);
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear usuario ADMIN</title>
<style>
    body { font-family: monospace; background:#f4f4f4; padding:20px; max-width:900px; margin:0 auto; }
    h1 { color:#333; border-bottom:2px solid #0866c6; padding-bottom:10px; }
    h2 { background:#0866c6; color:white; padding:8px 12px; border-radius:4px; margin-top:30px; font-size:15px; }
    .card { background:white; padding:15px 20px; border-radius:8px; margin:10px 0; box-shadow:0 2px 4px #0001; }
    label { display:block; margin-top:12px; font-weight:bold; }
    input[type=text], input[type=password], select { width:100%; padding:8px; margin-top:4px; box-sizing:border-box; font-family:monospace; }
    button { background:#0866c6; color:white; border:0; padding:10px 20px; border-radius:4px; margin-top:18px; cursor:pointer; font-size:14px; }
    table { width:100%; border-collapse:collapse; margin-top:10px; font-size:13px; }
    th { background:#eee; padding:6px; text-align:left; }
    td { padding:6px; border-bottom:1px solid #eee; }
    .ok    { background:#e6f9ef; border:2px solid #10b981; color:#065f46; padding:14px; border-radius:8px; }
    .error { background:#fde8e8; border:2px solid #ef4444; color:#7f1d1d; padding:14px; border-radius:8px; }
    .aviso { color:#b45309; background:#fff7ed; border:1px solid #f59e0b; padding:10px; border-radius:6px; font-size:13px; }
    small { color:#666; font-weight:normal; }
</style>
</head>
<body>

<h1>👤 Crear usuario ADMIN</h1>

<?php if ($SECRET === ''): ?>
<p class="aviso">⚠ Este script NO tiene clave de proteccion ($SECRET vacio). Cualquiera que conozca la URL puede crear un admin. Usalo y <b>borralo del servidor</b> de inmediato.</p>
<?php endif; ?>

<?php if ($resultado): ?>
<div class="<?= $resultado['ok'] ? 'ok' : 'error' ?>">
    <b><?= $resultado['ok'] ? '✅ ' : '❌ ' ?></b><?= h($resultado['msg']) ?>
    <?php if ($resultado['ok']): ?>
        <br><br>Ya puedes ingresar en <a href="login">/login</a> con el usuario <b><?= h($_POST['usuario'] ?? '') ?></b> y la clave que escribiste.
        <br><b>Recuerda borrar <code>crear_usuario_admin.php</code> del servidor.</b>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
    <form method="post" autocomplete="off">
        <?php if ($SECRET !== ''): ?>
        <label>Clave del script <small>(la definida en $SECRET)</small></label>
        <input type="password" name="secret" required>
        <?php endif; ?>

        <label>Empresa</label>
        <select name="empresa" required>
            <?php foreach ($empresas as $e): ?>
                <option value="<?= (int)$e['id_empresa'] ?>" <?= (isset($_POST['empresa']) && (int)$_POST['empresa'] === (int)$e['id_empresa']) ? 'selected' : '' ?>>
                    [<?= (int)$e['id_empresa'] ?>] <?= h($e['razon_social']) ?> (RUC <?= h($e['ruc']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Usuario <small>(para iniciar sesion, sin espacios)</small></label>
        <input type="text" name="usuario" required minlength="3" value="<?= h($_POST['usuario'] ?? '') ?>">

        <label>Clave <small>(minimo 4 caracteres)</small></label>
        <input type="password" name="clave" required minlength="4">

        <label>Email <small>(opcional, tambien sirve para iniciar sesion)</small></label>
        <input type="text" name="email" value="<?= h($_POST['email'] ?? '') ?>">

        <label>Nombres <small>(opcional)</small></label>
        <input type="text" name="nombres" value="<?= h($_POST['nombres'] ?? '') ?>">

        <label>Apellidos <small>(opcional)</small></label>
        <input type="text" name="apellidos" value="<?= h($_POST['apellidos'] ?? '') ?>">

        <label>Nro documento <small>(opcional)</small></label>
        <input type="text" name="num_doc" value="<?= h($_POST['num_doc'] ?? '') ?>">

        <label><input type="checkbox" name="reset" value="1" <?= !empty($_POST['reset']) ? 'checked' : '' ?>>
            Si el usuario ya existe: cambiarle la clave, ponerlo ADMIN y activarlo</label>

        <button type="submit">▶ Crear usuario ADMIN</button>
    </form>
</div>

<h2>Usuarios ADMIN actuales</h2>
<div class="card">
    <table>
        <thead><tr><th>ID</th><th>Empresa</th><th>Usuario</th><th>Email</th><th>Nombre</th><th>Estado</th><th>Creado</th></tr></thead>
        <tbody>
        <?php if (!$admins): ?>
            <tr><td colspan="7">No hay usuarios con rol ADMIN.</td></tr>
        <?php endif; ?>
        <?php foreach ($admins as $u): ?>
            <tr>
                <td><?= (int)$u['usuario_id'] ?></td>
                <td><?= (int)$u['id_empresa'] ?></td>
                <td><?= h($u['usuario']) ?></td>
                <td><?= h($u['email']) ?></td>
                <td><?= h(trim($u['nombres'] . ' ' . $u['apellidos'])) ?></td>
                <td><?= (int)$u['estado'] === 1 ? 'Activo' : 'Inactivo' ?></td>
                <td><?= h($u['fecha_create']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p style="color:#888; font-size:12px; margin-top:30px;">
    <b>⚠ Este script crea usuarios administradores directamente en la BD. No lo dejes en produccion:</b>
    cuando termines de probar, <b>borra este archivo</b> del servidor.
</p>

</body>
</html>

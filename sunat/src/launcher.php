<?php

    // === Forzar UTF-8 globalmente (HTTP header + PHP internal) ===
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }
    ini_set('default_charset', 'UTF-8');
    mb_internal_encoding('UTF-8');
    if (function_exists('mb_http_output')) mb_http_output('UTF-8');
    // =============================================================

    session_start();
    date_default_timezone_set('America/Lima');
    require './utils/config.php';
    require './app/clases/funciones.php';
    require './app/models/models.php';
    require './src/Roots.php';
    require './utils/Tools.php';
    require_once PATH_APP . "http/controllers/Controller.php";
    require_once PATH_APP . "http/middleware/Middleware.php";
    require PATH_SRC . 'autoloader/Autoloader.php';
    require_once 'config/Conexion.php';

    Autoloader::registrar();

    define('_URL', URL::base());

    $rutas = scandir(PATH_ROUTES);

    foreach ($rutas as $archivo) {
        $rutaArchivo = realpath(PATH_ROUTES . $archivo);
        if (is_file($rutaArchivo)) {
            require $rutaArchivo;
        }
    }

    Route::submit();

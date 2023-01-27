<?php

/**
 * Reemplaza el dato recibido por el diccionario en el archivo de entrada
 * y devuelve el un archivo modificado
 */

// aumenta el tamaño de memoria de trabajo
ini_set('memory_limit', '1024M');

require_once 'diccionario.php';

// archivo de entrada
$template = file_get_contents('moodle.sql');

// recorremos el archivo
foreach ($diccionario as $clave => $valor) {
    $template = str_replace($clave, $valor, $template);
}

// archivo de salida
$file_out = 'moodle-salida-ahora.sql';
file_put_contents($file_out, $template);

if (file_exists($file_out)) {
    echo '<h1>El archivo se creo con éxito!</h1><br>';
} else {
    echo '<h1><em>No se creo un mierda</em></h1><br>';
}
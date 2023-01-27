<?php

/**
 * Reemplaza el dato recibido por el diccionario en el archivo de entrada
 * y devuelve el un archivo modificado
 */

// aumenta el tamaño de memoria de trabajo
ini_set('memory_limit', '1024M');

require_once 'diccionario.php';

$entrada = 'archivo.csv';
$salida = 'archivo.mrc';

$plantilla = file_get_contents($entrada);

// recorremos el archivo
foreach ($diccionario as $clave => $valor) {
    $plantilla = str_replace($clave, $valor, $plantilla);
}

// archivo de salida
file_put_contents($salida, $plantilla);

if (file_exists($salida)) {
    echo '<h1>El archivo se creo con éxito!</h1><br>';
} else {
    echo '<h1><em>No se creo un mierda</em></h1><br>';
}

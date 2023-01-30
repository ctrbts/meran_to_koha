<?php

/**
 * Reemplaza el dato recibido por el template en el archivo de entrada
 * y devuelve el un archivo modificado
 */

// aumenta el tamaño de memoria de trabajo
ini_set('memory_limit', '1024M');

/**
 * key = campo original en meran
 * value = campo destino en koha
 *
 * @return array
 */
$template = [
    '$f' => '$p',
    '$o' => '$7', // si dice Sala de lectura va 1 si dice domiciliario va 0
    '$t' => '$o',
];

$file_in = file_get_contents('entrada.csv');
$file_out = 'salida.mrc';

foreach ($template as $key => $value) {
    $file_in = str_replace($key, $value, $file_in);
}

file_put_contents($file_out, $file_in);

if (file_exists($file_out)) {
    echo '<h1>El archivo se creo con éxito!</h1><br>';
} else {
    echo '<h1><em>No se creo un mierda</em></h1><br>';
}

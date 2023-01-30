<?php

ini_set('memory_limit', '1024M');

$template = [
    '$f' => '$p',
    '$o' => '$7',
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

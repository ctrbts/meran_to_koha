<?php

$file = fopen("pasos.txt", "rb");

if ($file == false) {
    echo "Error al abrir el archivo";
} else {
    $string_1 = fread($file, 18);
    rewind($file);
    $string_2 = fread($file, filesize("pasos.txt"));
    if (($string_1 == false) || ($string_2 == false))
        echo "Error al leer el archivo";
    else {
        echo "<p>\$content1 es: [" . $string_1 . "]</p>";
        echo "<p>\$content2 es: [" . $string_2 . "]</p>";
    }
}
fclose($file);

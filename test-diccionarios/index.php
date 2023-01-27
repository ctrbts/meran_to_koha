<?php

require_once 'diccionario.php';

$template = file_get_contents('template.html');

foreach ($diccionario as $clave => $valor) {
    $template = str_replace('{' . $clave . '}', $valor, $template);
}

print $template;
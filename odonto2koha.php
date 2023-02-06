<?php

ini_set('memory_limit', '1024M');

echo "<h3><i>Ejecutando script...</i></h3><br>";
flush();

#------------------ GENERAMOS EL ARCHIVO - PASO 1 -------------------#

/**
 * Esta exportación directa desde la base de datos
 * genera un archivo CSV demasiado sucio, en comparación
 * con la salida manual desde phpMyAdmin.
 *
 * Falta agregar los flags necesarios sobre fputcsv
 * para mejorar esta salida.
 */

$file_name = 'entrada_indice_busqueda.csv';

// datos del servidor de meran
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db_meran";

// creamos la conexion
$conn = new mysqli($servername, $username, $password, $dbname);

// verifiacmos la conexion
if ($conn->connect_error) {
    echo "Error al conectar: " . $conn->connect_error;
}

// extraemos los datos del campo indice_busqueda
$sql  = "SELECT marc_record FROM indice_busqueda";
$result = $conn->query($sql);

// creamos el archivo
$file_dump = fopen($file_name, "w");

// guardamos los registros en el archivo
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($file_dump, $row);
    }
    echo 'Archivo ' . $file_name . ' creado exitosamente!';
} else {
    echo "No se encontraron datos para procesr";
}

// cerramos el archio y la conexion
fclose($file_dump);
$conn->close();

echo '<hr><br>';


#------------------ CONVERTIMOS EL ARCHIVO - PASO 2 -------------------#

/**
 * El archivo resultante d ela conversion arrastra los errores de la
 * exportación a CSV del paso anterior.
 *
 * Falta mejorar los filtros regex para eliminar la suciedad
 * y poder automatizar correctamente esta parte del proceso
 */

// archivo de salida convertido
$file_redeable = 'entrada_indice_busqueda-legible.txt';

// corremos el yaz
$command = 'yaz-marcdump -i marc -o line ' . $file_name;
exec($command, $output);

$output_arr = [];

foreach ($output as $line) {
    $output_arr[] = $line . PHP_EOL;
}

file_put_contents($file_redeable, $output_arr);
echo 'Archivo ' . $file_redeable . ' creado exitosamente!<hr><br>';


#------------------ LIMPIAMOS EL ARCHIVO - PASO 3 -------------------#

/**
 * Tomamos el archivo en el formato correcto que nos devuelve el yaz
 * y le quitamos los campos y subcampos vacios y las linesa vacías de mas
 *
 * Falta mejorar este proceso a partir del archivo exportado en el paso 1
 * eliminando lineas de error como: //<!-- Skipping bad byte
 *
 * aca trabajo con la exportacion limoia que hizo Mariana
 */

// archivo marc legible por humanos
$input = 'entrada_ejemploparadividir.txt';
// salida sin sub-campos vacíos
$output = 'salida-paso-3-intermedia.txt';
// salida sin campos ni subcampos vacíos
$file_clean = 'salida-paso-3.txt';

// abrimos el archivo para trabajar
$file  = fopen($input, "r") or exit("<h3>No existe el archivo!</h3>");
$lines = [];

// eliminar sub-campos vacíos:
// -> el caracter especial "$"
// -> seguido de 1 caracter alfanumérico
// -> seguido de 3 espacios en blanco
// ej.: "$a  " o "$7  "
$first_clean = '/[\$][a-z0-9]{1}[\s]{3}+/';

// eliminar campos vacíos:
// -> al menos 3 caracteres numéricos
// -> seguido de 4 espacios en blanco
// -> seguido de un salto de línea
// ej.: "111    " o "500    "
$second_clean = '/[0-9]{3}[\s]{4}\n+/';

while (($line = fgets($file)) !== false) {
    //echo preg_replace($first_clean, '', $line); // salida en pantalla
    $lines[] = preg_replace($first_clean, '', $line);
}

// cerramos el archivo
fclose($file);

// eliminamos desde el arreglo "$lines" los campos vacios
$result = preg_grep($second_clean, $lines, PREG_GREP_INVERT);

// guardamos la primera salida
file_put_contents($output, $lines);
// guardamos la segunda salida
file_put_contents($file_clean, $result);

echo 'Archivo ' . $output . ' creado exitosamente!<hr><br>';
echo 'Archivo ' . $file_clean . ' creado exitosamente!<hr><br>';


#------------------ SEPARANDO NIVELES - PASO 4 -------------------#

/**
 * Tomamos la salida limpia del paso anterior y lo metemos
 * en un arreglo para manejarlo y separarlo en la cantidad de
 * arreglos necesarios para crear los registros
 *
 * estoy trabajando con un solo registro que contien dos niveles
 * la salida son dos registros con el mismo encabezado
 *
 * falta crearfuncion recursiva para que trabaje sobre el archivo
 * que procesa el script de forma completa
 */

$file_filled = 'salida-paso-4.txt';
$file = file('salida.txt');
//$file = file($file_clean);

$record = []; // para trabajar con un registro
$header = []; // para guardar el encabezado
$levels = []; // para guarerar los niveles

// $file es un arreglo que contiene al archivo para leerlo linea a linea
foreach ($file as $file_line) {
    if ($file_line !== PHP_EOL) {
        // mientra no encontremos una linea en blanco (separacion de registros)
        // vamos almacentando las lineas en otro arreglo temporal
        $record[] = $file_line;
        file_put_contents('record.txt', $record);
    }/*  else {
        break;
    }  */
}

// trabajamos sobre el arreglo temporal y separamos en encabezado y niveles
$count_020 = 0; // contador para recurrencias de camos 020 o 041
foreach ($record as $record_line) {
    // si encontramos un campo 020/041 incrementamos el contador
    if (strpos($record_line, "020") === 0 || strpos($record_line, "041") === 0) {
        $count_020++;
    }

    // si todavia no llegaos a la 020 armamos el encabezado
    if ($count_020 === 0) {
        $header[] = $record_line;
    } else {
        $levels[] = $record_line; // armamos el encabezado
    }
}

file_put_contents('header.txt', $header);
file_put_contents('levels.txt', $levels);

// trabajamos con los niveles
$results = [];
foreach ($levels as $level_line) {
    if (strpos($level_line, "020") === 0 || strpos($level_line, "041") === 0) {
        $results[]= PHP_EOL;
        foreach ($header as $header_line) {
            $results[]= $header_line;// si todavia no llegaos a la 020 armamos el encabezado
        }
    }

    $results[] = $level_line;
}

file_put_contents('salida-final.txt', $results, FILE_APPEND);
echo 'Archivo ' . $file_filled . ': registro agregado!<hr><br>';
<?php

#------------------ GENERAMOS EL ARCHIVO - PASO 1 -------------------#

/**
 * NOTA: Esta exportación directa desde la base de datos
 * genera un archivo CSV demasiado sucio, en comparación
 * con la salida manual desde phpMyAdmin.
 *
 * TODO: Tengo que mejorar los filtros regex para poder automatizar
 * correctamente esta parte del proceso
 */

$file_name = 'dump_indice_busqueda.csv';

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
    echo "Archivo creado exitosamente.";
} else {
    echo "No se encontraron datos para procesr";
}

// cerramos el archio y la conexion
fclose($file_dump);
$conn->close();

echo "<br><hr><br>";


#------------------ CONVERTIMOS EL ARCHIVO - PASO 2 -------------------#

// corremos el yaz
$command = 'yaz-marcdump -i marc -o line ' . $file_name . ' > exportado-paso2.txt';
exec($command, $output, $return_var);

echo "Salida:<br>";
print_r($output) . "<br>";
if ($return_var == 0) {
    echo "Archivo convertido con éxito!";
} else {
    echo "no se convirtio el archivo";
}

echo "<br><hr><br>";


#------------------ LIMPIAMOS EL ARCHIVO -------------------#

/* $lines = file('entrada.txt');
foreach ($lines as $line_num => $line) {
    echo "Line #{$line_num}: " . $line . "<br>";
} */

/* $file = fopen("entrada.txt", "r");

while (!feof($file)) {
    $line = fgets($file);
    echo $line . "<br>";
}

fclose($file);
*/

//<!-- Skipping bad byte

// archivo marc legible
$input = 'entrada.txt';
// salida sin sub-campos vacío
$output1 = 'salida1.txt';
// salida sin campos vacíos
$output2 = 'salida2.txt';

// abrimos el archivo para trabajar
$file  = fopen($input, "r") or exit("<h3>No existe el archivo!</h3>");
$lines = [];

// eliminar sub-campos vacíos:
// -> el caracter especial "$"
// -> seguido de 1 caracter alfanumérico
// -> seguido de 3 espacios en blanco
// ej.: "$a  " o "$7  "
$clean_first = '/[\$][a-z0-9]{1}[\s]{3}+/';

// eliminar campos vacíos:
// -> al menos 3 caracteres numéricos
// -> seguido de 4 espacios en blanco
// -> seguido de un salto de línea
// ej.: "111    " o "500    "
$clean_second = '/[0-9]{3}[\s]{4}\n+/';

while (($line = fgets($file)) !== false) {
    //echo preg_replace($clean_first, '', $line); // salida en pantalla
    $lines[] = preg_replace($clean_first, '', $line);
}

// cerramos el archivo
fclose($file);

// eliminamos desde el arreglo "$lines" los campos vacios
$result = preg_grep($clean_second, $lines, PREG_GREP_INVERT);

// guardamos la primera salida
file_put_contents($output1, $lines);
// guardamos la segunda salida
file_put_contents($output2, $result);

echo 'Archivo ' . $output1 . ' creado con éxito! <hr><br>';
echo 'Archivo ' . $output2 . ' creado con éxito! <hr><br>';


#------------------ CREAMOS EL ARCHIVO -------------------#

/* $file_in  = 'entrada.txt';
$file_out = 'salida2.txt';

$lines_in  = file($file_in);
$lines_out = [];

$to_clean = '/[\$][a-z0-9]{1}[\s]{2}+/';

foreach ($lines_in as $line) {
    if (strpos($line, $to_clean) !== 0) {
        $lines_out[] = $line;
        //echo $line . "<br>"; // salida en pantalla
    }
}

file_put_contents($file_out, $lines_out);
echo 'Archivo ' . $file_out . ' creado con éxito!';
echo "<br><hr><br>";

file_put_contents('salida3.txt', implode("\n", $lines_out));
echo 'Archivo salida3.txt creado con éxito!'; */

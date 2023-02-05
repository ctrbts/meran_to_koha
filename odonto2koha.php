<?php

ini_set('memory_limit', '1024M');

echo "Ejecutando script...\n";
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

/* $file_name = 'entrada_indice_busqueda.csv';

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

echo '<hr><br>'; */


#------------------ CONVERTIMOS EL ARCHIVO - PASO 2 -------------------#

/**
 * El archivo resultante d ela conversion arrastra los errores de la
 * exportación a CSV del paso anterior.
 *
 * Falta mejorar los filtros regex para eliminar la suciedad
 * y poder automatizar correctamente esta parte del proceso
 */

// archivo de salida convertido
/* $file_redeable = 'entrada_indice_busqueda-legible.txt';

// corremos el yaz
$command = 'yaz-marcdump -i marc -o line ' . $file_name;
exec($command, $output);

$output_arr = [];

foreach ($output as $line) {
    $output_arr[] = $line . PHP_EOL;
}

file_put_contents($file_redeable, $output_arr);
echo 'Archivo ' . $file_redeable . ' creado exitosamente!<hr><br>'; */


#------------------ LIMPIAMOS EL ARCHIVO - PASO 3 -------------------#

/**
 * Tomamos el archivo en el formato correcto que nos devuelve el yaz
 * y le quitamos los campos y subcampos vacios y las linesa vacías de mas
 *
 * Falta mejorar este proceso a partir del archivo exportado en el paso 1
 * eliminando lineas de error como: //<!-- Skipping bad byte
 */

// archivo marc legible por humanos
$input = 'entrada_ejemploparadividir.txt';
// salida sin sub-campos vacíos
$output = 'salida_sin_subcampos.txt';
// salida sin campos ni subcampos vacíos
$file_clean = 'salida_final_limpia.txt';

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
 *
 */

$lines = file($file_clean); // Carga el contenido del archivo en un array
$count_EOL = 0; // Contador para saltos de línea
$count_910 = 0; // Contador para líneas que comienzan con "910"

foreach ($lines as $line) {
    if ($line === "\n") {
        $count_EOL++;
    }
    if (strpos($line, '910') === 0) {
        $count_910++;
    }
}

echo 'Hay ' . $count_EOL . ' registros en el archivo.';
echo 'Deberia haber ' . $count_910;


/* $lines = file($file_clean); // Carga el contenido del archivo en un array
$found = false; // Bandera para indicar si se ha encontrado una línea que comience con "910"
$result = []; // Array para almacenar las líneas
$current = []; // Array temporal para almacenar las líneas hasta encontrar una línea con "020" o "041"

foreach ($lines as $line) {
    if (strpos($line, '910') === 0) {
        $found = true;
    }
    if ($found) {
        $current[] = $line;
        if (strpos($line, '020') === 0 || strpos($line, '041') === 0 || $line === "\n") {
            $result[] = $current;
            $current = [];
            $found = false;
        }
    }
}

print_r($result); */



// Carga el contenido del archivo en un array
/* $lines = file($file_clean);
$results = [];

$found = false;

foreach ($lines as $line) {
    if (strpos($line, '020') === 0 || strpos($line, '041') === 0 || trim($line) === '') {
        if ($found) {
            break;
        }
        $found = true;
    }
    if ($found) {
        $results[] = $line;
    }
}
 */

/* $file = fopen($file_clean, "r"); // Abre el archivo en modo de lectura
$array = []; // Inicializa un array para guardar las líneas
$newArray = []; // Inicializa un nuevo array para guardar las líneas después de "020" o "041"

while (!feof($file)) {
    $line = fgets($file);
    if (trim($line) === '') {
        break;
    }
    if (strpos($line, '020') === 0 || strpos($line, '041') === 0) {
        $newArray[] = $line;
        continue;
    }
    $array[] = $line;
}
fclose($file); // Cierra el archivo

echo('<pre>');
print_r($array); // Imprime el array anterior a "020" o "041"
echo('</pre>');

echo('<pre>');
print_r($newArray); // Imprime el array después de "020" o "041"
echo('</pre>'); */


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
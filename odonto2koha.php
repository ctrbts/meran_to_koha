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
 * falta crear funcion recursiva para que trabaje sobre el archivo
 * que procesa el script de forma completa
 */

$file_filled = 'salida-paso-4.txt';

$lines = file('salida.txt');
//$lines = file($file_clean);

$temp_arr = [];

$header = []; // para guardar el encabezado
$levels = []; // para guarerar los niveles

// $lines es un arreglo que contiene al archivo para leerlo linea a linea
foreach ($lines as $line) {
    if ($line !== PHP_EOL) {
        // mientra no encontremos una linea en blanco (separacion de registros)
        // vamos almacentando las lineas en otro arreglo temporal
        $temp_arr[] = $line;
    } else {
        // trabajamos sobre el arreglo temporal
        $count_020 = 0; // contador para recurrencias de camos 020 o 041

        // separamos en encabezado y niveles
        foreach ($temp_arr as $temp_line) {
            // si encontramos un campo 020/041 incrementamos el contador
            if (strpos($temp_line, "020") === 0 || strpos($temp_line, "041") === 0) {
                $count_020++;
            }

            // si todavia no llegaos a la 020 armamos el encabezado
            if ($count_020 === 0) {
                $header[] = $temp_line;
            }

            // encontramos el primer 020 llenamos el primer el nivel
            if ($count_020 > 0) {
                $levels[] = $temp_line; // armamos el encabezado
            }
        }

        // trabajamos con los niveles
        /* $temp_level = [];
        foreach ($levels as $level_line) {
            $temp_level[] = $level_line;

            // si encontramos otro nivel armamos hasta aca y lo agregamos al encabezado
            // egregamos al archivo de salida el resultado
            if (strpos($level_line, "020") === 0 || strpos($level_line, "041") === 0) {
                $temp_level[] = PHP_EOL;

                $result = array_merge($header, $temp_level);


            }
        } */

        echo '<pre>';
        print_r($header);
        echo '</pre>';

        //file_put_contents($file_filled, $result, FILE_APPEND | LOCK_EX);
        //echo 'Archivo ' . $file_filled . ': registro agregado!<hr><br>';

        // solo estamos analizando un registro
        //break;
    }

}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/* $file = fopen($file_clean, "r");
$line = fgets($file);
$array = [];
$tempArray = [];

while (!feof($file)) {
    if (substr($line, 0, 3) == "020" || substr($line, 0, 3) == "041") {
        if (!empty($tempArray)) {
            array_push($array, $tempArray);
        }
        $tempArray = [];
    }
    if (!empty(trim($line))) {
        $tempArray[] = $line;
    }
    $line = fgets($file);
}
fclose($file);

print_r($array);
 */



//$count_EOL = 0; // Contador para saltos de línea
//$count_910 = 0; // Contador para líneas que comienzan con "910"







    /* if (strpos($line, "020") !== 0 || strpos($line, "041") !== 0) {
        } else {
            $levels[] = $line; // armamos el nivel
        } */

        /* if (strpos($temp_line, '910') === 0) {
            $count_910++;
        } */

        /* for ($idx = 0; $idx < count($levels); $idx++) {
            if ((substr($levels[$idx], 0, 3) != "020") || (substr($levels[$idx], 0, 3) != "020")) {
                echo $levels[$idx] . "\n";
            }
        } */

        //$count_041 = 0;

        //$result = array_merge($header, $levels);

        //echo 'Hay ' . $count_910 . ' registros en el archivo.<br><br>';
        //echo 'Hay ' . $count_EOL . ' registros en el archivo. Deberia haber ' . $count_910 . '<br><br>';

        // agrega al archivo de salida el contenido de lo quq vaos procesando
        //file_put_contents($file_filled, $temp_arr, FILE_APPEND | LOCK_EX);
        //file_put_contents($file_filled, $result);


    /*     if (strpos($line, '910') === 0) {
        $count_910++;
    }

    if (strpos($line, "020") !== 0 || strpos($line, "041") !== 0) {
        $header[] = $line; // armamos el encabezado
    } else {
        $levels[] = $line; // armamos el nivel
    }

    if (strpos($line, '910') === 0) {
        $count_910++;
    }

    if ($line === PHP_EOL) {
        $result = array_replace($temp_arr, $header);
        $count_EOL++;
    }

    if ($count_910 > 1) {
    }
 */





//////////////////////////

/* $file = fopen($file_clean, "r") or die("No se puede abrir el archivo");

//recorre el archivo hasta que encuentra una linea en blanco
while (!feof($file)) {
    $line = fgets($file);
    if (trim($line) == '') {
        break;
    }
    echo $line . "<br>";
}
fclose($file);
 */


//recorre archivo hasta campo "020"

/* $file = fopen($file_clean, "r") or die("No se puede abrir el archivo");
$lines = [];
$count_910 = 0;
while (!feof($file)) {
    $line = fgets($file);
    if (strpos($line, "910") === 0) {
        $count_910++;
    }
    if (strpos($line, "020") === 0 || strpos($line, "041") === 0) {
        break;
    }
    $lines[] = $line;
}
fclose($file);

print_r($lines);
 */
////////////////////////////////////

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
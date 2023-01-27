**Cat2koha**
------------

La función de este script es realizar la converción de registros de una Base de Datos Isis de Catalis con formato Marc21 y exportarlo en un archivo mrc como especifica la norma ISO 2709 (ANSI Z39.2) para poder importarlo en el sistema de gestión bibliotecario Koha.

Ss un script bash que funciona sobre plataforma ***Linux***.

### Requerimientos de **cat2koha**

- Utilitarios [**CISIS**](http://wiki.bireme.org/es/index.php/CISIS).
  Versión recomendada [Linux ver.
  5.7e](https://github.com/bireme/cisis/releases/download/64bits-5.7e-1030/cisis-64bits-5.7e-1030.tar.gz).

    > Se recomienda crear en /opt la carpeta cisis (/opt/cisis) y allí copiar los utilitarios descargados y otorgarles permisos de ejecución.
    Si ud. desea alojar estos utiliarios en otro directorio, debe modificar la variable PATH_CISIS='/opt/cisis' que se encuentra en cat2koha.sh. 
 
- Algunos conocimientos de [*Lenguaje de formateo de CISIS*](<http://modelo.bvsalud.org/download/cisis/CISIS-LinguagemFormato4-es.pdf>).
- Interprete requerido **Python 2.X**. Observación: en Python 3 el código retorna errores por contener sintaxis obsoletas para este intérprete. 
- Se recomienda la aplicación [**MarcEdit**](<https://marcedit.reeset.net/>) para validar el archivo resultante para verificar que la estructura Marc sea válida y no haya errores en la codificación, por ej. indicadores inválidos.

### **Funcionamiento**

> Parámetros necesarios para migrar registros de Catalis a un archivo .mrc para importar en Koha.
>
>  ***Sintaxis:***
>
>  *./cat2koha.sh [-b | --db] <DB_origen> [-f | --from] <Nro. MFN inicial> [-t | --to] <Nro. MFN final>*
>
>  Parámetros:
>
>  -b | --db   <Nombre DB de origen (respetar mayúsculas y minúsculas).
>              Si la misma se encuentra dentro de una carpeta ingresar './nombre_carpeta/nombre_bd'
>              o la ruta absoluta a la base de datos.>
>
>  -f | --from <Opcional. Número de MFN inicial entero mayor que 0.>
>
>  -t | --to   <Opcional. Número de MFN final mayor o igual que el valor utilizado en el parámetro --from.>

**cat2koha** necesita un parámetro de entrada obligatorio que es la Base de Datos a convertir y dos parámetros opcionales que son *"Nro. de MFN inicial"* o *"Nro. de MFN final"*.
> - Si ingresa ambos parámetros, *Nro. de MFN inicial* y *Nro. de MFN final*, el script procesará los registros de la BD desde el valor inicial ingresado hasta el valor final ingresado.
> - Si ingresa solo *Nro. de MFN inicial*, el script procesará los registros de la BD desde ese valor hasta el final de la misma.
> - Si ingresa solo *Nro. de MFN final*, el script procesará los registros de la BD desde mfn 1 hasta el valor indicado para finalizar.


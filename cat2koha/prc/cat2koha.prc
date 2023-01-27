/*
 * ---------------------------------------------------------------------------------------------
 *  Claudio M. Fuhr
 *  claudiofuhr@gmail.com
 *  claudiofuhr@cab.cnea.gov.ar
 *  Biblioteca Leo Falicov
 *  Centro Atómico Bariloche - Instituto Balseiro
 *
 *  Marzo 2016
 *
 *  GNU GENERAL PUBLIC LICENSE Version 3
 *
 *  Este script es utilizado por cat2koha.sh y su función es convertir, agregar y eliminar campos
 *  locales de Catalis no necesarios en KOHA.
 *  Se incorpora el campo 942 donde se especifica el tipo de item:
 *     BK (Libro) | CR (Periodical) | CF (CD-Rom) | VM (DVD, VHS) | MU (Sound) | MP (Map) | MX (Kit)
 *  Se migran los datos del campo 859 de existencias en Catalis al campo 952 de KOHA.
 *  Se utiliza el campo 999 temporalmente para construir los datos del LDR, que en Catalis están
 *  distribuidos en los campos 905, 906, 907, 908, 909, 917, 918 y 919.
 * ---------------------------------------------------------------------------------------------
 */


/*
 * Borrado de campos a corregir y campos innecesarios
 */
'd859d905d906d907d908d909d917d918d919d980d991d1106',

/* Control Number Identifier
 * Field 003 contains a MARC organization code that identifies the source of the MARC record.
 * 
 * Reemplazar LIBRARY_CODE por Código de Biblioteca otorgado por la LOC.
 */
'<003>LIBRARY_CODE</003>',

/* Se efectuó una validación del archivo .mrc y se encontraron algunos registros que
 * contienen el campo 082 tienen el primer indicador incorrecto.
 *
 * 082 - Dewey Decimal Classification Number (R)
 * MARC 21 Bibliographic - full
 *
 * First Indicator                              Second Indicator
 * -------------------------------------------------------------
 * 0 - Full edition                             # - No information provided
 * 1 - Abridged edition                         0 - Assigned by LC
 * 7 - Other edition specified in subfield $2   4 - Assigned by agency other than LC
 *
 * Corregimos indicador incorrecto del campo 82, ## por 0#
 *
 * Descomente el código que sigue si necesita corregir este error de indicadores.
 */

/* 
 * 'd82',
 * if p(v82) then
 * '<082>',
 * if v82:'##^' then
 *   replace(v82,'##^','0#^'),
 * else
 *   v82,
 * fi,
 * '</082>',
 * fi,
 */

/* Determinar el nivel bibliográfico
 *
 *   Catalis
 *           v906=a [Language material]                            | v907=a (Monographic component part)
 *           v906=e [Cartographic material]                        | v907=i (Integrating resource)
 *           v906=g [Projected medium]                             | v907=m (Monograph/item)
 *           v906=m [Computer file/electronic resource]            | v907=s (serial)
 *           v906=t [Manuscript language material (tesis,letters)] | 
 *
 *   Valores encontrados:
 *   v906_v907
 *      a_a
 *      a_i
 *      a_m
 *      a_s
 *      e_m
 *      g_m
 *      m_m
 *      t_m
 *
 *   BK (Libro) | CR (Periodical) | CF (CD-Rom) | VM (DVD, VHS) | MU (Sound) | MP (Map) | MX (Kit)
 *   Agregar en Koha itemtype: IR (Integrating Resource)
 *
 *  Se utilizará un campo temporal 890 (no existe en Marc21) para luego ser renombrado como 942.
 */
'<942>',           
  select s(v906)
    case 'a': ,if v907:'a' then
                   '##^cBK',
               ,else if v907:'i' then  
                   '##^cIR',
               ,else if v907:'m' then
                   '##^cBK',
               ,else if v907:'s' then
                   '##^cCR',
               fi,fi,fi,fi,
    case 'e': ,if v907:'m' then
                   '##^cBK',
               fi,
    case 'g': ,if v907:'m' then
                   '##^cVM',
               fi,
    case 'm': ,if v907:'m' then
                   '##^cBK',
               fi,
    case 't': ,if v907:'m' then
                   '##^cBK',
               fi,
  endsel,  
'</942>',

/*
 * Mapeo de Existencias
 *
 */

if p(v859) then

 (
   /* 
    * Verificamos que el item no esté dado de Baja,
    * los items que tengan asignado un inventario erroneo
    * se les agregó como prefijo el nro. de control y un guión bajo (CN_inv)
    * para que la bibliotecaria determine en cada caso si se corrige o se elimina
    * la existencia.
    * Se encontraron casos como: nuevo-0
    * muchos de estos casos no equivalen a existencias físicas sino que son registros analíticos
	* o recursos electrónicos. 
    */
   
   if a(v859^w) then
      /* Item no está dado de baja */
      '<952>##',

      /*
       *   Catalis $q = nota sobre el estado físico
       *   KOHA  $0 = Estado de retiro
       *         Pestaña:10, | Campo Koha: items.withdrawn, No repetible, No obligatorio, | Valor autorizado:WITHDRAWN,
       */ 

      /*
       *   KOHA  $1 = Estado de pérdida
       *         Pestaña:10, | Campo Koha: items.itemlost, No repetible, No obligatorio, | Valor autorizado:LOST,
       */


      /*
       *   KOHA  $2 = Fuente del sistema de clasificación o colocación
       *         Pestaña:10, | Campo Koha: items.cn_source, No repetible, No obligatorio, | Valor autorizado:cn_source,
	   *
	   *   Descomente el código de abajo si desea agregar el código de la fuente de clasificación.
	   *   Este código debe coincidir con alguno de los códigos que identifican a las distintas fuentes de clasificación
	   *   incorporadas en Koha.
       */
        
		/*
         * '^2udc',
         */
      /*
       *   KOHA  $3 = Especificación de materiales (volumen encuadernado u otra parte)
       *         Pestaña:10, | Campo Koha: items.materials, No repetible, No obligatorio, oculto,
       */

      /*
       *   KOHA  $4 = Estado dañado
       *         Pestaña:10, | Campo Koha: items.damaged, No repetible, No obligatorio, | Valor autorizado:DAMAGED,
       */ 

      /*
       *   KOHA  $5 = Restricciones de uso
       *         Pestaña:10, | Campo Koha: items.restricted, No repetible, No obligatorio, | Valor autorizado:RESTRICTED,
       */

      /*
       *   KOHA  $6 = Koha clasificación normalizada para ordenar
       *         subcampo ignorado
       */

      /*
       *   KOHA  $7 = No para préstamo
       *         Pestaña:10, | Campo Koha: items.notforloan, No repetible, No obligatorio, | Valor autorizado:NOT_LOAN,
       */

      /*
       *   Catalis $b = sección/colección específica donde el ítem se encuentra
       *   KOHA  $8 = Código de colección
       *         Pestaña:10, | Campo Koha: items.ccode, No repetible, No obligatorio, | Valor autorizado:CCODE,
       */

      /*
       *   KOHA  $9 = Koha itemnumber (autogenerado)
       *         subcampo ignorado
       */

      /* 
       *   Código del Branch agregado al crear la Biblioteca a la que pertenece la existencia
       *   KOHA  $a = Localización permanente
       *         Pestaña:10, | Campo Koha: items.homebranch, No repetible, No obligatorio, | Valor autorizado:branches,
	   *
	   *   Reemplace <BRANCH_CODE> por lo que corresponda.
       */
           '^a','<BRANCH_CODE>',

      /*
       *   Código del Branch agregado al crear la Biblioteca donde se encuentra actualmente la existencia
       *   KOHA  $b = Ubicación/localización actual
       *         Pestaña:10, | Campo Koha:items.holdingbranch, No repetible, No obligatorio, | Valor autorizado:branches,
	   *
	   *   Reemplace <BRANCH_CODE> por lo que corresponda.
       */
           '^b','<BRANCH_CODE>',

      /*
       *   Catalis $h = signatura topográfica (clase)
       *   KOHA  $c = Ubicación en estantería
       *         Pestaña:10, | Campo Koha: items.location, No repetible, No obligatorio, | Valor autorizado:LOC,
       */

      /*
       *   Catalis $y = fecha de adquisición  (20-05-2005)
       *   KOHA  $d = Fecha de adquisición
       *         Pestaña:10, | Campo Koha: items.dateaccessioned, No repetible, No obligatorio, | Plugin:dateaccessioned.pl,
       */
	          /* Esto es un ejemplo de la no normalización en la carga de datos. Recomiendo verificar en cada base de datos particular.
               * El campo v859^y no siempre cumple con un criterio estandarizado respecto al formato de fecha.
               * P.Ej. encontramos casos como:  10/02/2007
               *                                10-02-2007
               *                                10/02/07
               *                                10-02-07
               *                                10-2-2007
               *                                02/2007
               *                                2007 y algunos más...
               * Se determino que los casos que cumplan con DD/MM/AAAA o DD-MM-AAAA serán reformateados a AAAA-MM-DD como se guardan en Mysql o MariaDB.
               * [Los demás casos que no cumplan la condición, el dato será reemplazado por la fecha de creación de la existencia.]
			   * 
			   * Verificar, descomentar y corregir el código que sigue. 
               */
		/*	   
         *  if v859^y<>'' then
         *    '^d',v859^f,
         *  fi,
		 */

      /*
       *   Catalis $s | $d = proveedor (en caso de compra) | donante
       *   KOHA  $e = Fuente de adquisición
       *         Pestaña:10, | Campo Koha: items.booksellerid, No repetible,No obligatorio,
       */
           if v859^s<>'' then
              '^e',v859^s,
           fi,
           if v859^d<>'' then
       	      if v859^n<>'' then
 	          '^e',v859^d,
       	      fi,
           fi,

      /*
       *   KOHA  $f = Información codificada de la localización en otra ubicación
       *         Pestaña:10, | Campo Koha: items.coded_location_qualifier, No repetible, No obligatorio,
       */

      /*
       *   Catalis $c = precio de compra
       *   KOHA  $g = Coste, precio normal de compra
       *         Pestaña:10, | Campo Koha: items.price, No repetible, No obligatorio,
       */
           if v859^c<>'' then
              '^g',v859^c,
           fi,

      /*
       *   KOHA  $h = Enumeración/cronología de publicación seriada
       *         Pestaña:10, | Campo Koha: items.enumchron, No repetible, No obligatorio,
       *
       */

      /*
       *   Catalis $p = inventario (identificación de la pieza)
       *   KOHA  $i = Número de inventario
       *         Pestaña:10, | Campo Koha: items.stocknumber, No repetible, No obligatorio,
       */
           if v859<>'' then
              '^i',v859^p,
           fi,

      /*
       *   KOHA  $j = Número de control en estantería
       *         Pestaña:10, | Campo Koha: items.stack, No repetible, No obligatorio, oculto, | Valor autorizado:STACK,
       */

      /*
       *   KOHA  $l = Total de préstamos
       *         Pestaña:10, | Campo Koha: items.issues, No repetible, No obligatorio, oculto,
       */

      /*
       *   KOHA  $m = Renovaciones totales
       *         Pestaña:10, | Campo Koha: items.renewals, No repetible, No obligatorio, oculto,
       */

      /*
       *   KOHA  $n = Fondos totales
       *         Pestaña:10, | Campo Koha: items.reserves, No repetible, No obligatorio, oculto, 
       */

      /*
       *   Catalis $k = signatura topográfica (prefijo) +
       *   Catalis $h = signatura topográfica (clase) +
       *   Catalis $i = signatura topográfica (librística) +
       *   Catalis $v = signatura topográfica (volumen) +
       *   Catalis $3 = parte (ej. "Vol. 1" "Parte II" "Tomo 3") +
       *   Catalis $t = número de ejemplar o copia
       *
       *   KOHA  $o = Signatura topográfica completa
       *         Pestaña:10, | Campo Koha: items.itemcallnumber, No repetible, No obligatorio,
	   *
	   *   Importante: verificar previamente la correcta construcción de la signatura tipográfica
	   *               y corrija el código siguiente para su conveniencia.
       */
          if v859^k<>'' then
             '^o',v859^k,
             if v859^k<>'' then
                ' ', 
             fi,
          else
             '^o',
          fi,
          if v859^h<>'' then
             v859^h,
             if v859^i<>'' then
                ' ', 
             fi,
          fi,
          if v859^i<>'' then
             v859^i
             if v859^v<>'' then
                ' ',
             fi,
          fi,
          if v859^v<>'' then
             v859^v,
             if v859^3<>'' then
                ' ', 
             fi,
          fi,
          if v859^3<>'' then
             v859^3,
             if p(v859^t) then
                ' ',
             fi,
          fi,
          if v859^t<>'' then
              if a(v859^3) then 
                ' Ej.',v859^t,
              else
                'Ej.',v859^t,
              fi,
          fi,

      /*
       *   Catalis $p = inventario (identificación de la pieza)
       *   KOHA  $p = Código de barras
       *         Pestaña:10, | Campo Koha: items.barcode, No repetible, No obligatorio, | Plugin:barcode.pl,
       */
           if v859<>'' then
             '^p',v859^p,
           fi,

      /*
       *   KOHA  $q = En préstamo
       *         Pestaña:10, | Campo Koha: items.onloan, No repetible, No obligatorio, oculto,
       */
 
      /*
       *   KOHA  $r = Fecha visto por última vez
       *         Pestaña:10, | Campo Koha: items.datelastseen, No repetible, No obligatorio, oculto,
       */

      /*
       *   KOHA  $s = Fecha del último préstamo
       *         Pestaña:10, | Campo Koha: items.datelastborrowed, No repetible, No obligatorio, oculto,
       */
 
      /*
       *   Catalis $t = número de ejemplar o copia
       *   KOHA  $t = Número de copia
       *         Pestaña:10, | Campo Koha: items.copynumber, No repetible, No obligatorio,
	   *
	   *   Es un subcampo no obligatorio. No recomiendo convertir este subcampo para instancias multi-biblioteca.
       */

      /*
       *   KOHA  $u = Identificador Uniforme del Recurso
       *         Pestaña:10, | Campo Koha: items.uri, No repetible, No obligatorio, es una URL,
       */

      /*
       *   KOHA  $v = Coste, precio de reemplazo
       *         Pestaña:10, | Campo Koha: items.replacementprice, No repetible, No obligatorio,
       */

      /*
       *   KOHA  $w = Precio válido a partir de
       *         Pestaña:10, | Campo Koha: items.replacementpricedate, No repetible, No obligatorio,
       */

      /*
       *   Catalis $x = nota interna (i.e. no pública)
       *   KOHA  $x = Nota no pública
       *         Pestaña:10, No repetible, No obligatorio, oculto,
       */
           if v859^x<>'' then
              '^x',v859^x,
           fi,

      /*
       *   BK (Libro) | CR (Periodical) | CF (CD-Rom) | VM (DVD,VHS) | MU (Sound) | MP (Map) | MX (Kit)
       *   KOHA  $y = Tipo de ítem Koha
       *         Pestaña:10, | Campo Koha: items.itype, No repetible, No obligatorio, | Valor autorizado:itemtypes,
       */
           if v907:'m' or v907:'a' then
           else
              '^yBK',
           fi,
           if v907:'s' then
              '^yCR',
           fi,
           if v859^i:'CD-ROM' or v859^3:'CD-ROM' then
              '^yCF',
           fi,
           if v859^i:'DVD' or v859^3:'DVD' or p(v245^h) then
              '^yVM',
           fi,

      /*
       *   Catalis $n | $z = notas bibliográficas | nota pública
       *   KOHA  $z = Nota pública
       *         Pestaña:10, | Campo Koha: items.itemnotes, No repetible, No obligatorio,
       */
           if v859^n<>'' then
              '^z',v859^n,
           fi,
           if v859^z<>'' then
              if v859^n<>'' then
                 ' | ',v859^z,
              fi,
           fi,

       '</952>',
    fi,
 /),
fi,


/*
 * Construcción del LDR
 *
 * Documentación: https://www.loc.gov/marc/bibliographic/bdleader.html
 *
 * Creamos el campo temporal 999
 *  
 */

'<999>',
    '00000',  /* posición 0-4 */ 
    v905,     /* posición 5 */
    v906,     /* posición 6 */
    v907,     /* posición 7 */
    v908,     /* posición 8 */
    v909,     /* posición 9 */
    '22',     /* posición 10-11 */
    '00000',  /* posición 12-16 */
    v917,     /* posición 17 */
    v918,     /* posición 18 */
    v919,     /* posición 19 */
    '4500',   /* posición 20-23 */
'</999>'

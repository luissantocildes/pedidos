<?php
/***************************************************
* ajax.php
* 	Modulo que concentra algunas funciones que devuelven valores usando ajax.
****************************************************/

define('OK', 0);

global $modulos;

// Realiza la tarea indicada
switch ($task) {
    case 'listaImagenes': // Devuelve un XML con la lista de imagenes
        listaImagenesXML();
        break;
    case 'listaImagenes2': // Devuelve un XML con la lista de imagenes
        listaImagenesXML2();
        break;
    default: // Por defecto no se hace nada
        break;
}

/*************
 * Genera un fichero XML con el listado de imágenes
 *************/
function listaImagenesXML()
{
    global $modulos;

    // Cabeceras XML y control de cache
    header('Content-Type: text/xml');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

    echo '<?xml version="1.0" encoding="iso-8859-1" ?>';
    echo "\r\n";
?>
<imagenes>
    <?php
	// Obtiene la lista de imágenes (esto se podría mover al inicio de la función, para no intercalarlo
	// con código HTML)
	$select = procesaSql("select id, fichero, nombre, date_format(fecha, '%e/%c/%Y') as fecha from #_ficheros order by nombre");
	$imagenes = $modulos->db->query($select);
	if ($imagenes->numRows()) {
	    while ($imagen = $imagenes->fetchrow()) {
    ?>
    <imagen fichero="<?php echo $imagen['fichero']; ?>" nombre="<?php echo html_entity_decode($imagen['nombre']); ?>"
			fecha="<?php echo $imagen['fecha']; ?>" idFichero="<?php echo $imagen['id']; ?>" /> 
    <?php
        }
    }
?>
</imagenes>
<?php
}

/************+
 * Genera un fichero XML con el listado de imágenes.
 * Función similar a listaImagenesXML pero limitada a un número de elementos
 **/
function listaImagenesXML2()
{
    global $modulos, $elemPorPagina, $elemPorLinea;

    // Cabeceras XML y control de cache
    header('Content-Type: text/xml');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    echo '<?xml version="1.0" encoding="iso-8859-1" ?>';
    echo "\r\n";
	
    // calcula el total de páginas
    $select = procesaSql ("SELECT count(*) as total FROM #_ficheros");
    $aux = $modulos->db->query($select);
    if ($aux->numRows()) {
	$aux2 = $aux->fetchRow();
	$totalFotos = $aux2['total'];
	$totalPaginas = (int)ceil($totalFotos / $elemPorPagina);
    }
    
    // Calcula la pagina actual		
    $pagina = $modulos->getParam($_GET, 'pag', 0);
    if (!is_numeric($pagina) || $pagina >= $totalPaginas)
	$pagina = 0;
    $fotoIni = $pagina * $elemPorPagina;
    if ($fotoIni + $elemPorPagina > $totalFotos)
	$fotoFin = $totalFotos;
    else $fotoFin = $fotoIni + $elemPorPagina;

    // Obtiene la lista de fotos y genera el listado.
    $select = procesaSql("select id, fichero, nombre, date_format(fecha, '%e/%c/%Y') as fecha from #_ficheros order by nombre LIMIT $fotoIni, $elemPorPagina");
    $imagenes = $modulos->db->query($select);
    if ($imagenes->numRows()) {
    	?>
    	<imagenes>
	<?php
    	    while ($imagen = $imagenes->fetchrow()) {
   	?>
	    	<imagen fichero="<?php echo $imagen['fichero']; ?>" titulo="<?php echo html_entity_decode($imagen['nombre']); ?>" idFoto="<?php echo $imagen['id']; ?>" />
    	<?php
	    }
       	?>
        	
	    <totalPaginas value="<?php echo $totalPaginas; ?>" />
	    <fotosLinea value="<?php echo $elemPorLinea; ?>" />
	    <mensaje value="<?php echo "Mostrando fotos ".($fotoIni + 1)." - ".$fotoFin." de ".$totalFotos; ?>" />
	    <?php
		for ($c = 0; $c < $totalPaginas; $c++) {
		    echo "<pagina value='$c'";
		    if ($c == $pagina)
			echo " actual='1'";
		    echo " />\r\n";
		}
	    ?>
    	</imagenes>
    	<?php
    }
}

?>
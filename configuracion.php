<?php

define ('DEBUG', 1);

// Configuración de base de datos
$dbname = 'segurtil';
$dbuser = '12345678';
$dbpasswd = '12345678';
$dbhost = 'localhost';
$dbtype = 'mysqli';

$dsn = "$dbtype://$dbuser:$dbpasswd@$dbhost/$dbname";

$tablePrefix = 'segur';

$titulo = 'Segurestil';

/* Determina la carpeta origen en base a la URL con la que se accede al sitio, y configura
 * las demás rutas.
 * localhost -> desarrollo
 * Otra URL -> producción
 * Se podría hacer usando una variable tipo $en_desarrollo = TRUE pero no quería estar
 * modificando el archivo cada vez que lo subía a producción
 */
if ($_SERVER['HTTP_HOST'] == 'localhost') {
	$url = $_SERVER['HTTP_HOST']."/segurestil/pedidos";
	$dirApp = 'segurestil/';
	
} else {
	$url = $_SERVER['HTTP_HOST']."/pedidos";
	$dirApp = '';
}
$dirUpload = $_SERVER['DOCUMENT_ROOT']."/".$dirApp."documentos/";
$dirUploadImagenes = $_SERVER['DOCUMENT_ROOT']."/".$dirApp."imagenes/";

$dirModulos = 'modulos';

// Timeout por defecto, 1200 segundos (media hora)
$defaultTimeout = 1200;

// Cantidad de ofertas por línea en la página de ofertas
$ofertasPorLinea = 3;
$ofertasPorPagina = $ofertasPorLinea * 4;

// Elementos por página en los listados de la administración
$elemPorLinea = 4;
$lineasPorPagina = 3;
$elemPorPagina = $elemPorLinea * $lineasPorPagina;

$lineasListado = 20;

// Total de anuncioa a mostrar en el lateral
$anunciosLaterales = 3;

// Dirección de email del remitente
$emailRemitente = "luis.santocildes@gmail.com";

ini_set ('include_path', $_SERVER['DOCUMENT_ROOT']."/pedidos/pear/:".ini_get('include_path'));

?>

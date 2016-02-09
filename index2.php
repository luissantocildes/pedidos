<?php
/****************************************
 * Página base de la aplicación. Carga los archivos necesarios, el archivo base del "framework",
 * así como las funciones comunes y las constantes.
 * Ejecuta la función principal del módulo especificado, sin cargar ningún otro código HTML. Se
 * utiliza para recibir las llamadas via Ajax.
 ****************************************/
	include "configuracion.php";
	include "include/modulos.php";
	include "include/funciones.php";
	
	$modulos = new Modulo('./');

	$modulos->principal();
?>

<?php
/****************************************
 * Página base de la aplicación. Carga los archivos necesarios, el archivo base del "framework",
 * así como las funciones comunes y las constantes.
 * Ejecuta la función principal del módulo especificado, sin cargar ningún otro código HTML. Se
 * usa en caso de que sea necesario mostrar un pop-up.
 ****************************************/
	include "configuracion.php";
	include "include/modulos.php";
	include "include/funciones.php";
	
	$modulos = new Modulo('./');

	$modulos->principal();
?>

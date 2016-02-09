<?php
/****************************************
 * P�gina base de la aplicaci�n. Carga los archivos necesarios, el archivo base del "framework",
 * as� como las funciones comunes y las constantes.
 * Ejecuta la funci�n principal del m�dulo especificado, sin cargar ning�n otro c�digo HTML. Se
 * utiliza para recibir las llamadas via Ajax.
 ****************************************/
	include "configuracion.php";
	include "include/modulos.php";
	include "include/funciones.php";
	
	$modulos = new Modulo('./');

	$modulos->principal();
?>

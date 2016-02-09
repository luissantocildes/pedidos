<?php

/****************************************
 * Página base de la aplicación. Carga los archivos necesarios, el archivo base del "framework",
 * así como las funciones comunes y las constantes.
 * Muestra la página de inicio
 ****************************************/

	include "configuracion.php";
	include "include/modulos.php";
	include "include/funciones.php";
	include "modulos/const_usuarios.php";
	
	ini_set("display_errors", true);
	ini_set("error_reporting", E_ALL);
	
	$modulos = new Modulo('./');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Administraci&oacute;n <?php echo $titulo; ?></title>
		<link type="text/css" href="admin.css" rev="stylesheet" rel="stylesheet">
		<script language="javascript" src="js/ajax.js" type="text/javascript"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<div id="capaPausa" name="capaPausa">
			Espere un momento, por favor...
		</div>
		<div id="tituloExterior">
			<div id="titulo">Administraci&oacute;n <?php echo $titulo; ?></div>
		</div>
		<div id="contenedor">
			<div id="menu">
				<ul>
					<li><a href="index.php">Inicio</a></li>
					<li><a href="?opt=usuarios">Usuarios</a></li>
					<?php
						if (isset ($_SESSION['tipo']) && $_SESSION['tipo'] == C_ADMIN) { ?>
							<li><a href='?opt=campos'>Caracter&iacute;sticas</a></li>
						<?php }
					?>
					<li><a href='?opt=pedidos'>Pedidos</a></li>
					<li><a href='?opt=login&task=logout'>Salir</a></li>
				</ul>
			</div>
			<div id="contenido">
				<?php
					// Aquí se incluye el contenido
					$modulos->principal();
				?>
			</div>
		</div>
	</body>
</html>

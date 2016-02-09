<?php

/**********************************
 * Funciones varias.
 **********************************/

/*********
 * Genera una nueva imagen, copia de la imagen pasada, de un tamaño más pequeño, para ser
 * previsualizada, y la guarda en dirDestino
 * Parámetros:
 *	$nombreImagen: Nombre de la imagen a reducir
 *	$dirOrigen: Directorio donde se encuentra la imagen a reducir
 *	$dirDestino: Directorio donde guardar la imagen reducida
 *	$alto, $ancho: Dimensiones de la nueva imagen.
 ***/
function genera_thumb ($nombreImagen, $dirOrigen, $dirDestino, $ancho=100, $alto=0) {
	// Inicializa las variables a utilizar
	$imagenOrigen = $dirOrigen . $nombreImagen;
	$imagenDestino = $dirDestino . $nombreImagen;
	
	// Verifica que exista la imagen origen, la destino no importa, se sobreescribe
	if (file_exists ($imagenOrigen)) {
		// Ok, existe, se verifica que sea de un tipo que se pueda utilizar
		$datosImagen = getimagesize($imagenOrigen);
		if ($datosImagen[2] && (IMAGETYPE_JPEG | IMAGETYPE_PNG | IMAGETYPE_GIF )) {
			// OK, ahora se calcula el tamaño de la imagen resultante
			if ($ancho) {
				$reduccionX = $ancho / $datosImagen[0];
				$alto = $datosImagen[1] * $reduccionX;
			} else if ($alto) {
				$reduccionY = $alto / $datosImagen[1];
				$ancho = $datosImagen[0] * $reduccionY;
			} else {
				$ancho = $datosImagen[0];
				$alto = $datosImagen[1];
			}
			
			// Se reduce la imagen
			switch ($datosImagen[2]) {
				case IMAGETYPE_JPEG:
					$imagen = @imagecreatefromjpeg($imagenOrigen);
					break;
				case IMAGETYPE_PNG:
					$imagen = @imagecreatefrompng($imagenOrigen);
					break;
				case IMAGETYPE_GIF:
					$imagen = @imagecreatefromgif($imagenOrigen);
					break;
			}
			if ($imagen) {
				$thumb = imagecreatetruecolor($ancho, $alto);
				imagecopyresampled ($thumb, $imagen, 0, 0, 0, 0, $ancho, $alto, $datosImagen[0], $datosImagen[1]);
				
				// guarda la imagen
				switch ($datosImagen[2]) {
					case IMAGETYPE_JPEG:
						$aux=imagejpeg($thumb, $imagenDestino);
						break;
					case IMAGETYPE_PNG:
						$aux=imagepng($thumb, $imagenDestino);
						break;
					case IMAGETYPE_GIF:
						$aux=imagegif($thumb, $imagenDestino);
						break;
				}
				
				if ($aux) echo "1";
				else echo "2";
			}
		}
	} else
		return false;
}

/**********************
 * Genera el código HTML con los enlaces para saltar a una página determinada, en plan 1 2 3 4 5 ...
 * de un listado de resultados
 * Parámetros:
 * 	$paginaActual: Página que se está mostrando actualmente. Se muestra el número de página indicado pero no
 * 			se genera el enlace.
 * 	$totalPaginas: Total de página a generar.
 * 	$opt: Módulo/librería a mostrar al ir al enlace.
 * 	$extraLink: Array de parámetros extras para cada enlace.
 **********************/
function paginador ($paginaActual, $totalPaginas, $opt, $extraLink = Array()) {
	$cadena = '';
	for ($c = 0; $c < $totalPaginas; $c++) {
		if ($c == $paginaActual)
			$cadena .= "<b>" . ($c+1) . "</b>";
		else {
			$cadena .= "<a href='?opt=$opt&pag=".$c;
			if (count($extraLink))
				$cadena .= "&" . implode("&", $extraLink);
			$cadena .="'>" . ($c+1) . "</a>";
		}
		if ($c < $totalPaginas-1)
			$cadena .= " - ";
	}
	return $cadena;
}

/**********************
 * Función similar a paginador. Calcula el total de páginas a mostrar en base al total de elementos a mostrar.
 * Parámetros:
 * 	$paginaActual: Página que se está mostrando actualmente. Se muestra el número de página indicado pero no
 * 			se genera el enlace.
 * 	$totalElementos: Total de elementos del listado.
 * 	$opt: Módulo/librería a mostrar al ir al enlace.
 * 	$extraLink: Array de parámetros extras para cada enlace.
 **********************/
function paginador2 ($paginaActual, $totalElementos, $elemPorPagina, $opt, $extraLink = Array()) {
	return paginador ($paginaActual, ceil ($totalElementos / $elemPorPagina), $opt, $extraLink);
}

/***********************
 * Incrementa en 1 el valor del campo $nombreCampo en la tabla $nombreTabla
 * en todos los elementos que $nombreCampo >= $valorInicio.
 * Se utiliza para generar un hueco en un listado en el que unsertar un nuevo elemento.
 * Parámetros:
 *		$nombreTabla: Tabla a modificar
 *		$nombreCampo: Campo a modificar
 *		$valorInicio: Valor a partir del que realizar el incremento
 ****************/
function cambia_orden_elementos ($nombreTabla, $nombreCampo, $valorInicio) {
	global $modulos;
	
	$update = procesaSql("update $nombreTabla set $nombreCampo = $nombreCampo + 1 where $nombreCampo >= ?");
	$aux = $modulos->db->query($update, $valorInicio);
	
	if (PEAR::isError($aux))
		mostrar_error_sql ($aux);
}

/**********************************
 * Comprueba que exista una entrada determinada en una tabla
 * Parámetros:
 *		$nombreTabla:	Tabla a comprobar
 *		$nombreCampo:	Campo por el que buscar
 *		$valor:			Valor a buscar
 * Devuelve: true si existe la fila, false en caso contrario o si hay error
 *********/
function existe_fila ($nombreTabla, $nombreCampo, $valor) {
	global $modulos;
	
	$select = procesaSql("SELECT * FROM $nombreTabla WHERE $nombreCampo = ?");
	$aux = $modulos->db->query($select, $valor);
	
	if (PEAR::isError($aux))
		return false;
	else if ($aux->numRows())
		return true;
	else return false;
}

/**********************************
 * Devuelve una cabecera para ficheros XML.
 * Se usa para enviar datos al navegador.
 **********************************/
function cabeceraXML() {
    header('Content-Type: text/xml');
    header("Cache-Control: no-cache, must-revalidate");
    //A date in the past
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    echo '<?xml version="1.0" encoding="iso-8859-1" ?>';
    echo "\r\n";
}

?>

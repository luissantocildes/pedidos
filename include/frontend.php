<?php

include_once ("bbdd.php");

define ('FILE_UNDEF', 0);
define ('FILE_PRECIO', 1);
define ('FILE_CATALOGO', 2);
define ('FILE_FICHERO', 3);

function lista_paginas ($paginaActual, $totalImagenes) {
	global $ofertasPorPagina;
	
	$totalPaginas = ceil($totalImagenes / $ofertasPorPagina);
	if ($paginaActual)
		echo "<a href='?paginaActual=".($paginaActual-1)."'>&lt;&lt;</a> ";
	for ($c = 0; $c < $totalPaginas; $c++) {
		if ($c != $paginaActual)
			echo "<a href='?paginaActual=".$c."'>".($c+1)."</a>";
		else
			echo ($c+1);
		if ($c < $totalPaginas-1)
			echo " - ";
	}
	if ($paginaActual < $totalPaginas-1)
		echo " <a href='?paginaActual=".($paginaActual+1)."'>&gt;&gt;</a>";
}

?>
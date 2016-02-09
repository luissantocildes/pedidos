<?php

/****************** FUNCIONES AUXILIARES PARA LOS DISTRIBUIDORES **********************/

/***************
 * Busca un distribuidor por el campo indicado
 ****************/
function buscar_distribuidor ($distribuidor, $campoBusqueda) {
	global $modulos, $db;
	
	$select = procesaSql("SELECT * FROM #_distribuidores WHERE $campoBusqueda = ?");
	$resultados = $modulos->db->query($select, $distribuidor);
	if (PEAR::isError($resultados))
		return false;
	else {
		$filas = Array();
		while ($fila = $resultados->fetchRow())
			$filas[] = $fila;
			
		return $filas;
	}
}
?>
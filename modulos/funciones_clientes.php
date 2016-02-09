<?php

/****************** FUNCIONES AUXILIARES PARA LOS CLIENTES **********************/

/***************
 * Busca un cliente por el campo indicado
 ****************/
function buscar_cliente ($cliente, $campoBusqueda) {
	global $modulos, $db;
	
	$select = procesaSql("SELECT * FROM #_clientes WHERE $campoBusqueda = ?");
	$resultados = $modulos->db->query($select, $cliente);
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
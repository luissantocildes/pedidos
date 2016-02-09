<?php

/***********************************
 * Este archivo quizás no sea necesario. Pendiente de comprobar si hay alguna dependencia o si
 * está duplicado con otro nombre.
 ***********************************/

define ('ERROR_DESPLEGABLES', 0x01);
define ('ERROR_CLIENTE', 0x02);
define ('ERROR_DISTRIBUIDOR', 0x04);
define ('ERROR_FECHA_ENTRADA', 0x08);
define ('ERROR_FECHA_COMPROMISO', 0x10);
define ('ERROR_PEDIDO_PENDIENTE', 0x20);

/*********************** Funciones auxiliares para los pedidos *******************/
/*****************
 * Cuenta el total de los pedidos
 ***************/
function total_pedidos ($usuario = -1) {
	global $db, $modulos;
	
	$select = procesaSql ("SELECT count(*) as total FROM #_pedidos");
	$parametros = Array();
	if ($usuario > -1) {
		$select .= " WHERE idComercial = ?";
		$parametros = Array($usuario);
	}
	$pedidos = $modulos->db->query($select, $parametros);
	if (PEAR::isError($pedidos)) {
		return false;
	} else {
		$total = $pedidos->fetchRow();
		return $total['total'];
	}
}

/**********************
 * Devuelve una lista con los filtros para los pedidos
 ********************/
function lee_filtros_pedidos(){
	return Array("1"=>"N&uacute;m. pedido", "2"=>"Cliente", "3"=>"Comercial");
}

/*************************
 * Se coge un nuevo numero de pedido
 **************/
function nuevo_numero_pedido() {
	global $modulos;
	
	return $modulos->db->nextId(procesaSql ("#_idPedido"));
}

/*********************************
 * Devuelve la lista de tipos
 *****************/
function lee_tipos() {
	global $modulos, $db;
	
	$select = procesaSql("SELECT * FROM #_tipocaracteristica ORDER BY orden");
	$tipos = $modulos->db->query ($select);
	if (PEAR::isError($tipos)) {
		return Array();
	} else {
		$listaTipos = Array();
		while ($tipo = $tipos->fetchRow())
			$listaTipos[$tipo['id']] = $tipo;
		return $listaTipos;
	}
}

/*********************************
 * Devuelve la lista de caracteristicas
 *****************/
function lee_caracteristicas($tipo = -1) {
	global $modulos, $db;
	
	if ($tipo == -1) {
		$select = procesaSql("SELECT * FROM #_caracteristicas WHERE borrado = 0 ORDER BY id");
		$tipos = $modulos->db->query ($select);
	} else {
		$select = procesaSql("SELECT * FROM #_caracteristicas WHERE borrado = 0 AND tipo = ? ORDER BY id");
		$tipos = $modulos->db->query ($select, $tipo);
	}
	if (PEAR::isError($tipos)) {
		return Array();
	} else {
		$listaTipos = Array();
		while ($datosTipo = $tipos->fetchRow())
			$listaTipos[$datosTipo['id']] = $datosTipo;
		return $listaTipos;
	}
}

/************************************
 * Devuelve el código html de un desplegable con los tipos
 ****************************/
function crear_desplegable ($listaTipos, $campoTexto, $nombre, $defecto = -1, $onChange = false, $referencia=false, $elementoInicial=false) {
	$cadena = '';
	$cadena .= '<select id="'.$nombre.'" name="'.$nombre.'"';
	if ($onChange != false)
		$cadena .= ' onChange="'.$onChange.'"';
	$cadena .= ">\r\n";
	
	if (is_array($elementoInicial)) {
		$cadena .= '<option value="'.$elementoInicial[0].'">'.$elementoInicial[1].'</option>';
	}
	
	foreach ($listaTipos as $indice=>$valor) {
		$cadena .= '<option value="'.$indice.'"';
		if ($defecto == $indice)
			$cadena .= 'selected';
		if ($referencia)
			$cadena .= '>' . $valor[$referencia].' ('.$valor[$campoTexto] . ')</option>';
		else $cadena .= '>' . $valor[$campoTexto] . '</option>';
	}
	
	$cadena .= "</select>\r\n";
	
	return $cadena;
}
?>

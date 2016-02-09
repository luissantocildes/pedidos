<?php

/******************************
 * Clase de conexión a la base de datos.
 * Se utiliza la clase DB de Pear para implementar las funcionalidades.
 ******************************/

	include "DB.php";

	/***********************************
	 * conexion_db: Método que conecta con la base de datos.
	 ***********************************/
	function conexion_db() {
		global $dsn;
		
		$db =& DB::connect($dsn);

		if (PEAR::isError($db)) {
			if (defined('DEBUG')) {
				mostrar_error_sql ($db);
			}
		    return false;
		}
		$db->setFetchMode(DB_FETCHMODE_ASSOC);
		return $db;
	}

	/***********************************
	 * desconexion_db: Realiza el cierre de la conexión con la bbdd
	 ***********************************/
	function desconexion_db ($idConexion) {
		$idConexion->disconnect();
	}

	/************************************
	 * procesaSql: Reemplaza en una sentencia sql la cadena #_ por un prefijo
	 * especificado en la conficuración. Permite usar la misma instalación de la
	 * aplicación web en varios sitios web diferentes alojados en el mismo servidor
	 * Parámetros:
	 * 	$sql: Cadena sql a tratar.
	 * Devuelve una cadena con los caracteres #_ cambiados por $tablePrefix_
	 ************************************/
	function procesaSql ($sql) {
		global $tablePrefix;
		
		return str_replace ('#_', $tablePrefix."_", $sql);
	}
	
	/************************************
	 * procesa_sql: Renombrado de procesaSql para seguir el mismo criterio de nombres
	 ************************************/
	function procesa_sql ($sql) {
		return procesaSql($sql);
	}
	
	/************************************
	 * mostrar_error_sql: Procesa el objeto devuelto por la clase DB cuando ocurre un
	 * error al interactuar con la base de datos, mostrando el mensaje de error correspondiente.
	 * Parámetros:
	 * 	$objeto: Objeto con los datos del error.
	 ************************************/
	function mostrar_error_sql ($objeto) {
		echo 'Standard Message: ' . $objeto->getMessage() . "<br>"; 
		echo 'Standard Code: ' . $objeto->getCode() . "<br>"; 
		echo 'DBMS/User Message: ' . $objeto->getUserInfo() . "<br>"; 
		echo 'DBMS/Debug Message: ' . $objeto->getDebugInfo() . "<br>"; 
	}

	/**************************************
	 * Genera una sentencia SQL y la ejecuta en base a los parámetros pasados.
	 * Solo genera sentencias SQL sencillas, en plan SELEC tabla1.campo1, tabla2.campo2, tabla2.campo3 FROM tabla1, tabla2 WHERE tabla1.campo1 = tabla2.campo1 ...
	 * Parámetros:
	 * 	$campos: Array con la lista de campos a obtener. Cada elemento puede indicarse sin clave,
	 * 		usando la clave que genera automáticamente PHP, o se puede poner una clave. En este caso
	 * 		la clave se usará como un alias: SELECT campo1 as clave
	 * 		Ejemplo: ("contador"=>"campo1", "campo2")
	 * 	$tablas: Array con los nombres de las tablas.
	 * 	$condiciones: Array con las condiciones de los datos a obtener. Si el array está vacío o se
	 * 		omite el parámetro se devuelven todas las filas.
	 * 	$operador: Operador a utilizar para concatenar las condiciones. Se usa el mismo operador para todas las condiciones.
	 * 		Por defecto se concatenan con AND.
	 * 	$orden: Array con las condiciones para ordenar las tablas. Si se omite el parámetro las filas se devuelven
	 * 		tal cual las devuelve la base de datos.
	 * 	$limite: Total de filas a devolver y offset desde el que comenzar.
	 *
	 * NOTA: No se comprueba que ninguno de los valores pasados en los parámetros sean correctos, ni tampoco se
	 * 	comprueba que los campos o tablas existan el la base de datos por lo que es posible generar una
	 * 	sentencia SQL incorrecta. Tampoco se hace una limpieza de los parámetros, por lo que el código
	 * 	SQL generado puede ser suceptible de inyecciones SQL.
	 **************************************/
	function ejecuta_select ($campos, $tablas, $condiciones = Array(), $operador = "AND", $orden = Array(), $limite = Array()) {
		$select = 'SELECT ';

		// Prepara los campos a leer
		foreach ($campos as $clave=>$valor) {
			if (!is_numeric($clave))
				$campos[$clave] = "$valor as $clave";
		}
		$select .= implode (",", $campos) . " FROM ";
		
		// Prepara las tablas de las que leer
		foreach ($tablas as $clave=>$valor) {
			if (!is_numeric($clave))
				$tablas[$clave] = "$valor $clave";
		}
		$select .= implode (",", $tablas);
		
		// Prepara las condiciones de los where
		if (count($condiciones)) {
			foreach ($condiciones as $clave=>$valor) {
				if (!is_numeric($clave))
					$condiciones[$clave] = "$valor $clave";
			}
			$select .= " WHERE " . implode (" $operador ", $condiciones);
		}
		
		// Añade el ORDER BY
		if (count ($orden)) {
			$select .= " ORDER BY " . implode (",", $orden);
		}
		
		// Y el limit
		if (count ($limite)) {
			$select .= " LIMIT " . implode (",", $limite); 
		}
		return $select;
	}

?>
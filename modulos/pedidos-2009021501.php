<?php

/****************************
 * Módulo de pedidos
 *
 ******/

require ('const_usuarios.php');
require ('func_pedidos.php');
require ('funciones_clientes.php');
require ('funciones_distribuidores.php');

// Según el TASK pasado, se realiza la tarea determinada
switch ($task) {
	case 'borrarPedido':
		borrar_pedido();
		break;
	case 'leerPedido': // Lee los datos de un pedido
		leer_pedido();
		break;
	case 'guardarPedido': // Guarda el pedido, como definitivo o como borrador
		guardar_pedido();
		break;
	case 'listaClientes': // Muestra una página con la lista de clientes o distribuidores
	case 'listaDistribuidores':
		listado($task);
		break;
	case 'listadoPedidos':
		listado_pedidos();
		break;
	case 'nuevoPedido':
		nuevo_pedido();
		break;
	default:
		indice_pedidos($_SESSION['tipo']);
		break;
}

/************************
 * Muestra el índice de los pedidos. Determina si es el administrador o un usuario normal y dependiendo de esto muestra un indice u otro
 ****************/
function indice_pedidos($tipoUsuario = C_USUARIO) {
    switch ($tipoUsuario) {
        case C_ADMIN:
            indice_admin();
            break;
        case C_USUARIO:
        default:
            //indice_normal();
			indice_admin();
            break;
    }
}

/********************
 * Indice para el administrador
 *************/
function indice_admin() {
	global $modulos, $db;
	
	// Ok, ahora se muestra el índice
	$_SESSION['filtros'] = serialize(lee_filtros_pedidos());
	
	muestra_indice();
}

/*****************************
 * Genera el listado de los pedidos
 ***********************/
function listado_pedidos() {
	global $modulos, $db;
	
	$filtros = unserialize($_SESSION['filtros']);
	$totalPedidos = total_pedidos();
	$listaPedidos = Array();
	
	// Se muestra el listado de todos los pedidos.
	if ($_SESSION['tipo'] == C_ADMIN) {
		$select = procesaSql('SELECT p.id, p.numpedido, DATE_FORMAT(p.fechaentrada, \'%d/%m/%Y\') as fechaEntrada, p.referencia AS refcliente, p.importe, p.login, pp.idpedido '.
		'FROM (SELECT p1.id, p1.numpedido, p1.fechaentrada, c1.referencia, p1.importe, u1.login FROM #_pedidos p1, #_clientes c1, #_usuarios u1 WHERE p1.refcliente = c1.id AND p1.idcomercial = u1.id) as p '.
		'LEFT JOIN #_pedidos_pendientes pp ON p.id = pp.idpedido '.
		'ORDER BY p.fechaEntrada desc, numpedido');
		$pedidos = $modulos->db->query($select);
	} else {
		$select = procesaSql('SELECT p.id, p.numpedido, DATE_FORMAT(p.fechaentrada, \'%d/%m/%Y\') as fechaEntrada, p.referencia AS refcliente, p.importe, p.login, pp.idpedido '.
		'FROM (SELECT p1.id, p1.numpedido, p1.fechaentrada, c1.referencia, p1.importe, u1.login FROM #_pedidos p1, #_clientes c1, #_usuarios u1 WHERE p1.refcliente = c1.id AND p1.idcomercial = u1.id AND p1.idcomercial = ?) as p '.
		'LEFT JOIN #_pedidos_pendientes pp ON p.id = pp.idpedido '.
		'ORDER BY p.fechaEntrada desc, numpedido');
		$pedidos = $modulos->db->query($select, $_SESSION['idUsuario']);
	}

	cabeceraXML();
	?>
	<documento>
		<botones>
			<![CDATA[
			<!--button type="button">Limpiar Filtro</button-->
			<button type="button" onClick="nuevo_pedido('cuadroDatos', 'botones')">Nuevo pedido</button>
			]]>
		</botones>
		<html>
			<![CDATA[
			<!--Filtro: <input type="text" id="filtro" name="filtro" value="">
			<select>
				<?php
					foreach ($filtros as $id=>$nombre)
						echo "<option value='", $id, "'>", $nombre, "</option>";
				?>
			</select>&nbsp;
			<button type="button">Filtrar</button>
			<hr /-->
			<div id='capaPedido'>
			Mostrando <?php echo count($listaPedidos); ?> de <?php echo $totalPedidos; ?> pedidos.
			<table id='pedido'>
				<tr>
					<th>Pedido</th>
					<th>Fecha</th>
					<th>Cliente</th>
					<th>Comercial</th>
					<th>Total</th>
					<th>&nbsp;</th>
				</tr>
				<tr>
					<?php
						if ($totalPedidos) {
							while ($pedido = $pedidos->fetchRow()) {
							?>
								<tr>
									<td onclick="leer_pedido('<?php echo $pedido['numpedido']; ?>', 'cuadroDatos', 'botones');"><?php echo $pedido['numpedido']; if ($pedido['idpedido']) echo ' <span class="nota">(pendiente)</span>'; ?></td>
									<td onclick="leer_pedido('<?php echo $pedido['numpedido']; ?>', 'cuadroDatos', 'botones');"><?php echo $pedido['fechaEntrada']; ?></td>
									<td onclick="leer_pedido('<?php echo $pedido['numpedido']; ?>', 'cuadroDatos', 'botones');"><?php echo $pedido['refcliente']; ?></td>
									<td onclick="leer_pedido('<?php echo $pedido['numpedido']; ?>', 'cuadroDatos', 'botones');"><?php echo $pedido['login']; ?></td>
									<td onclick="leer_pedido('<?php echo $pedido['numpedido']; ?>', 'cuadroDatos', 'botones');"><?php echo sprintf('%.2F &euro;', $pedido['importe']); ?></td>
									<td>
										<?php 
											if ($pedido['idpedido']) {
											?><img src="imagenes/cancelar.gif" class="falsoBoton" onClick="borrar_pedido('<?php echo $pedido['id']; ?>');">
											<?php 
											} else
												echo "&nbsp;";
										?>
									</td>
								</tr>
							<?php
							}
						} else {
							echo "<td colspan='4'>No se han encontrado pedidos</td>";
						}
					?>
				</tr>
			</table>
			<div>
			]]>
		</html>
	</documento>
	<?php
}

/*****************
 * Muestra el índice
 *************/
function muestra_indice() {
	?>
	<script type="text/javascript" src="./js/tbl_change.js"></script>
    <form name='formulario' id='formulario' method='post' action=''>
		<div id="botones">
		</div>
		<div id="cuadroDatos">
		</div>
		<script language='javascript'>
			var paso = 0;
			listado_pedidos('cuadroDatos', 'botones');
		</script>
	</form>
	<?php
}

/*****************************************
 * Devuelve el formulario vacío para un nuevo pedido
 *****************************************/
function nuevo_pedido() {
	cabeceraXML();
	?>
	<documento>
		<html>
		<![CDATA[ 
			<?php 
			echo formulario_pedido();
			?>
		]]>
		</html>
		<botones>
			<![CDATA[
				<button type="button" onClick="enviar_formulario_pedido('formulario', 'cuadroDatos', 'botones', true);">Enviar pedido</button>
				<button type="button" onClick="enviar_formulario_pedido('formulario', 'cuadroDatos', 'botones', false);">Grabar borrador</button>
				<button type="button" onClick="if (confirm ('Va a cancelar el nuevo pedido\r\n¿Está seguro?')) listado_pedidos('cuadroDatos', 'botones');">Cancelar</button>
			]]>
		</botones>
	</documento>
	<?php
}

/*********************************
 * Devuelve el formulario con los datos del pedido solicitado
 *******************************/
function muestra_pedido($datosPedido=Array(), $error=0) {
	global $modulos, $db;
	
	$id = Modulo::getParam($_POST, 'id', '');
	if ($id == -1)
		$id = Modulo::getParam($_GET, 'id', '');
	
	cabeceraXML();
	?>
	<documento>
		<html>
		<![CDATA[ 
			<?php 
			echo formulario_pedido($datosPedido, $error);
			?>
		]]>
		</html>
		<botones>
			<![CDATA[
			<?php
				if (isset($datosPedido['pendiente'])) {
				?>
					<button type="button" onClick="enviar_formulario_pedido('formulario', 'cuadroDatos', 'botones', true);">Enviar pedido</button>
					<button type="button" onClick="enviar_formulario_pedido('formulario', 'cuadroDatos', 'botones', false);">Grabar borrador</button>
					<button type="button" onClick="if (confirm ('Va a perder los cambios\r\n¿Está seguro?')) listado_pedidos('cuadroDatos', 'botones');">Cancelar</button>
				<?php
				} else {
				?>
					<button type="button" onClick="listado_pedidos('cuadroDatos', 'botones');">Volver al listado</button>
				<?php
				}
			?>
			]]>
		</botones>
	</documento>
	<?php
}

/*********************************
 * Devuelve el texto del formulario con los datos pasados
 *********************************/
function formulario_pedido ($datosPedido = Array(), $error = 0) {
	if (is_array($datosPedido) && count($datosPedido) == 0 && !$error) {
		$datosPedido['fechaentrada'] = $datosPedido['fechacompromiso'] = 
		$datosPedido['codigonum'] = $datosPedido['puertanum'] = $datosPedido['observaciones'] =
		$datosPedido['refcliente'] = $datosPedido['distribuidor'] =
		$datosPedido['importe'] = $datosPedido['idcomercial'] = '';
		$datosPedido['id'] = -1;
		$datosPedido['lineasPedido'] = Array();
		$datosPedido['numpedido'] = nuevo_numero_pedido();
		$datosPedido['pendiente'] = '';
	};
	
	$tipos = &lee_tipos();

	// Hay errores, se marcan los campos erroneos y se pone un mensaje de error
	$claseFechaE = $claseFechaC = $claseRefC = $claseDist = $claseCod = '';
	if ($error) {
		if ($error & ERROR_FECHA_ENTRADA)
			$claseFechaE = 'error';
		if ($error & ERROR_FECHA_COMPROMISO)
			$claseFechaC = 'error';
		if ($error & ERROR_CLIENTE)
			$claseRefC = 'error';
		if ($error & ERROR_DISTRIBUIDOR)
			$claseDist = 'error';
		if ($error & ERROR_CODIGO)
			$claseCod = 'error';
	?>
		<div class="error">
			Hay un error en los datos enviados. Los datos erroneos apareceran marcados. Por favor completelos correctamente.
		</div>
	<?php
	}
	?>
		Nº pedido: <?php echo $datosPedido['numpedido']; ?><input type="hidden" id="id" name="id" value="<?php echo $datosPedido['id']; ?>"><input type="hidden" id="numPedido" name="numPedido" value="<?php echo $datosPedido['numpedido']; ?>"><br>
		<span class="<?php echo $claseFechaE; ?>">Fecha entrada: <input type="text" id="fechaEntrada" name="fechaEntrada" value="<?php echo $datosPedido['fechaentrada']; ?>" readonly>
		<?php
		if (isset($datosPedido['pendiente'])) {
			?>
			<img src="imagenes/calendario.png" class="falsoBoton" align="top" onClick="openCalendar('', 'formulario', 'fechaEntrada', 'date');">
			<?php
		}
		?>
		</span><br>
		<span class="<?php echo $claseFechaC; ?>">Fecha compromiso <input type="text" id="fechaCompromiso" name="fechaCompromiso" value="<?php echo $datosPedido['fechacompromiso']; ?>" readonly>
		<?php
		if (isset($datosPedido['pendiente'])) {
			?>
			<img src="imagenes/calendario.png" class="falsoBoton" align="top" onClick="openCalendar('', 'formulario', 'fechaCompromiso', 'date');">
			<?php
		}
		?>
		</span><br>
		<span class="<?php echo $claseRefC; ?>">Referencia cliente: <input type="text" id="refCliente" name="refCliente" value="<?php echo $datosPedido['refcliente']; ?>" readonly>
		<?php
		if (isset($datosPedido['pendiente'])) {
			?>
			<img src="imagenes/buscar.gif" class="falsoBoton" align="top" onClick="buscar_cliente('refCliente', 'capaAuxiliar');">
			<?php
		}
		?>
		</span><br>
		<span class="<?php echo $claseDist; ?>">Distribuidor: <input type="text" id="distribuidor" name="distribuidor" value="<?php echo $datosPedido['distribuidor']; ?>" readonly>
		<?php
		if (isset($datosPedido['pendiente'])) {
			?>
			<img src="imagenes/buscar.gif" class="falsoBoton" align="top" onClick="buscar_distribuidor('distribuidor', 'capaAuxiliar');">
			<?php
		}
		?>
		</span><br>
		<span class="<?php echo $claseCod; ?>">C&oacute;digo Nº: <input type="text" id="codigoNum" name="codigoNum" value="<?php echo $datosPedido['codigonum']; ?>"></span><br>
		Observaciones:<textarea id="observaciones" name="observaciones"><?php echo $datosPedido['observaciones']; ?></textarea><br>
		<table id='pedido'>
			<tr>
				<th>Paso</th>
				<th>Concepto</th>
				<th>Descripci&oacute;n</th>
				<th></th>
			</tr>
			<?php
				$totalLineas = count($datosPedido['lineasPedido']);
				$c = 0;
				if ($totalLineas) { // Muestra las lineas del pedido actual si este es difinitivo
					// Lee la lista de caracteristicas
					$listaCaracteristicas = Array();
					if (!isset($datosPedido['pendiente']))
						$listaCaracteristicas = lee_caracteristicas();

					for (; $c < $totalLineas; $c++) {
						echo "<tr id='fila_$c'><td>", $c+1, '</td><td>';
						if (!isset($datosPedido['pendiente']))
							echo $tipos[$datosPedido['lineasPedido'][$c]['tipo']]['descripcion'];
						else {
							if ($datosPedido['lineasPedido'][$c]['tipo'] == -1) {
								reset($tipos);
								$datosPedido['lineasPedido'][$c]['tipo'] = key($tipos);
							}
							echo crear_desplegable ($tipos, 'descripcion', "tipo_".$c, $datosPedido['lineasPedido'][$c]['tipo'], "leer_atributos(this, 'descripcion_$c');");
						}
						echo '</td><td>';
						if (!isset($datosPedido['pendiente']))
							echo $listaCaracteristicas[$datosPedido['lineasPedido'][$c]['valor']]['nombre'];
						else {
							$listaCaracteristicas = lee_caracteristicas($datosPedido['lineasPedido'][$c]['tipo']);
							echo crear_desplegable ($listaCaracteristicas, 'nombre', "descripcion_$c", $datosPedido['lineasPedido'][$c]['valor'], false, 'codigo');
						}
						echo '</td><td id="comandos_'.$c.'">';
						if (!isset($datosPedido['pendiente']))
							echo '&nbsp;';
						else
							if ($c == $totalLineas - 1) {
							?>
								<img src='imagenes/insertar.png' class='falsoBoton' alt='A&ntilde;adir l&iacute;nea' title='A&ntilde;adir l&iacute;nea' onClick='paso=aceptar_paso_insertar(<?php echo $c; ?>);'>
							<?php
							} else
								echo "<img src='imagenes/cancelar.gif' title='Borrar' alt='Borrar' class='falsoBoton' onClick='borrar_paso($c);'>";
						echo '</td></tr>';
					}
				}
				
				// Si el pedido es nuevo, se pone una linea para introducir datos
				if ($datosPedido['id'] == -1 && !$error) {
					$paso = $c;
					?>
					<tr id="fila_<?php echo $paso; ?>">
						<td id="paso_<?php echo $paso; ?>"><?php echo $c+1; ?></td>
						<td><select id="tipo_<?php echo $paso; ?>" name="tipo_<?php echo $paso; ?>" onChange="leer_atributos(this, 'descripcion_<?php echo $paso; ?>');">
								<option value="-1">Escoja un concepto</option>
								<?php
									foreach ($tipos as $datosTipo) {
										echo "<option value='",$datosTipo['id'],"'>", $datosTipo['descripcion'],"</option>";
									}
								?>
							</select>
						</td>
						<td><select id="descripcion_<?php echo $paso; ?>" name="descripcion_<?php echo $paso; ?>">
								<option value="-1">Escoja un concepto</option>
							</select>
						</td>
						<td id="comandos_<?php echo $paso; ?>"><img src='imagenes/insertar.png' class='falsoBoton' alt='A&ntilde;adir l&iacute;nea' title='A&ntilde;adir l&iacute;nea' onClick='paso=aceptar_paso_insertar(paso);'></td>
					</tr>
					<?php
				}
			?>
		</table>
	<?php
}

/*********************************
 * Muestra una página con la lista de clientes o de distribuidores
 *****************/
function listado($task) {
	$cuadroDestino = Modulo::getParam($_GET, 'cuadro', '');
	
	if ($task == 'listaClientes') {
		$texto = 'cliente';
		$js = "leer_lista_clientes ('lista');";
	} else {
		$texto = 'distribuidor';
		$js = "leer_lista_distribuidores ('lista');";
	}
	
	?>
	<html>
	<head>
		<script language="javascript" src="js/ajax.js" type="text/javascript"></script>
		<link type="text/css" href="admin.css" rev="stylesheet" rel="stylesheet">
	</head>
	<body style="margin: 5px 5px 5px 5px; text-align: center;">
		Seleccione un <?php echo $texto; ?>:<br>
		<select id="lista" size="10">
		</select><br>
		<button type="button" onClick="cerrar_ventana('lista', '<?php echo $cuadroDestino; ?>');">Escoger <?php echo $texto; ?></button>
		<button type="button" onClick="window.close();">Cancelar</button>
		<script language="javascript">
			<?php echo $js, "\r\n"; ?>
		</script>
	</body>
	</html>
	<?php
}

/**********************************
 * Guarda el pedido
 ************************/
function guardar_pedido() {
	global $modulos, $db;
	
	$error = 0;
	
	// Primero se leen los parametros y se verifica que sean correctos.
	$definitivo = (strtoupper(Modulo::getParam($_POST, 'definitivo', 'NO')) == 'SI') ? true : false;
	$id = Modulo::getParam($_POST, 'id', -1);
	$numPedido = Modulo::getParam($_POST, 'numPedido', '');
	$fechaEntrada = Modulo::getParam($_POST, 'fechaEntrada', '');
	$fechaCompromiso = Modulo::getParam($_POST, 'fechaCompromiso', '');
	$refCliente = Modulo::getParam($_POST, 'refCliente', '');
	$distribuidor = Modulo::getParam($_POST, 'distribuidor', '');
	$codigoNum = Modulo::getParam($_POST, 'codigoNum', 0);
	$observaciones = Modulo::getParam($_POST, 'observaciones', '');
	
	// Se leen los desplegables
	$totalDesplegables = Modulo::getParam($_POST, 'totalDesplegables', 0);
	if (!is_numeric($totalDesplegables))
		$totalDesplegables = 0;
	$tipos = Array();
	$valores = Array();
	foreach ($_POST as $nombre=>$valor) {
		$partesNombre = explode('_', $nombre);
		switch (strtolower($partesNombre[0])) {
			case 'tipo':
				$tipos[] = $valor;
				break;
			case 'descripcion':
				$descripcion[] = $valor;
				break;
		}
	}
	// Se verifica si hay tantos valores para los tipos como para las descripciones
	if (count($tipos) != count ($descripcion))
		$error = ERROR_DESPLEGABLES;
	
	// Verifica que los valores pasados sean correctos.
	// Primero se verifican los desplegables de tipos y de caracteristicas
	$select = procesaSql('select count(*) as total from #_tipocaracteristica WHERE id IN ('.implode(',', $tipos).')');
	$resultado = $modulos->db->query($select);
	$tiposExistentes = $resultado->fetchRow();
	
	$select = procesaSql('select count(*) as total from #_caracteristicas WHERE id IN ('.implode(',', $descripcion).') AND borrado = 0');
	$resultado = $modulos->db->query($select);
	$caracteristicasExistentes = $resultado->fetchRow();
	
	if (count($tipos) != $tiposExistentes['total'] || count($descripcion) != $caracteristicasExistentes['total']) {
		$error = ERROR_DESPLEGABLES;
	}

	// Verifica el cliente y el distribuidor
	$cliente = buscar_cliente($refCliente, 'referencia');
	if ($cliente === false || count($cliente) == 0)
		$error |= ERROR_CLIENTE;

	$distribuidor = buscar_distribuidor($distribuidor, 'referencia');
	if ($distribuidor === false || count($distribuidor) == 0)
		$error |= ERROR_DISTRIBUIDOR;

	// verifica las fechas
	if (!verifica_fecha($fechaEntrada))
		$error |= ERROR_FECHA_ENTRADA;
	if (!verifica_fecha($fechaCompromiso))
		$error |= ERROR_FECHA_COMPROMISO;

	// Si el ID > -1 entonces verifica que el pedido esté pendiente
	if ($id > -1) {
		$select = procesaSql('SELECT count(*) as total FROM #_pedidos_pendientes WHERE idpedido = ?');
		$resultado = $modulos->db->query($select, $id);
		$totalPedidos = $resultado->fetchRow();
		if ($totalPedidos['total'] != 1)
			$error |= ERROR_PEDIDO_PENDIENTE;
	}

	// Carga los parametros en un array
	$parametros = Array('id'=>$id,
						'numpedido'=>$numPedido,
						'fechaentrada'=>$fechaEntrada,
						'fechacompromiso'=>$fechaCompromiso,
						'refcliente'=>$cliente[0]['referencia'],
						'distribuidor'=>$distribuidor[0]['referencia'],
						'codigonum'=>$codigoNum,
						'observaciones'=>$observaciones);
						
	if (!$definitivo) { // Marca si el pedido está pendiente o no. También se mira el tipo de error. 
		$parametros['pendiente'] = true;
		if ($parametros['fechacompromiso'] == '' && ($error & ERROR_FECHA_COMPROMISO))
			$error = $error ^ ERROR_FECHA_COMPROMISO;
		if ($parametros['fechaentrada'] == '' && ($error & ERROR_FECHA_ENTRADA))
			$error = $error ^ ERROR_FECHA_ENTRADA;
		if ($parametros['distribuidor'] == '' && ($error & ERROR_DISTRIBUIDOR))
			$error = $error ^ ERROR_DISTRIBUIDOR;
	}
	// Si después de todo hay un error, se muestra de nuevo el formulario marcando el error
	if ($error) {
		// Si hay un error se convierten los tipos y descripciones en el formato de las lineas de pedidos
		$lineas = Array();
		for ($c = 0; $c < count($tipos); $c++) {
			$lineas[] = Array ('tipo' => $tipos[$c], 'valor' => $descripcion[$c],
								'importe' => '', 'pedido' => '', 'id' => '');
		}
		$parametros['lineasPedido'] = $lineas;
		print_r ($lineas);
		die();
		muestra_pedido($parametros, $error);
	} else { // sino se guarda el pedido y se muestra el índice de pedidos
		guarda_pedido($id, $numPedido, convierte_fecha($fechaEntrada), convierte_fecha($fechaCompromiso), $cliente[0]['id'], $distribuidor[0]['id'], $codigoNum, $observaciones, $tipos, $descripcion, $definitivo);
		listado_pedidos();
	}
}

/***********************
 * guarda el pedido
 ***************/
function guarda_pedido($id, $numPedido, $fechaEntrada, $fechaCompromiso, $refCliente, $distribuidor, $codigoNum, $observaciones, $tipos, $descripcion, $definitivo) {
	global $modulos, $db;
	
	// No se verifican los parametros de entrada ya que se considera que estos han sido comprobados antes de llamar a la función
	// Tampoco se escapan los mismos, ya que se considera que lo hace el php automáticamente
	
	// Primero se comprueba si el pedido está pendiente
	if ($id > -1) {
		$select = procesaSql('SELECT count(*) as total FROM #_pedidos_pendientes WHERE idpedido = ?');
		$resultado = $modulos->db->query($select, $id);
		$totalPendiente = $resultado->fetchRow();
		
		// Si el pedido está pendiente, se borran las lineas del mismo y se actualizan los datos, sino no se hace nada
		if ($totalPendiente['total'] != 0) {
			$delete = procesaSql('DELETE FROM #_lineapedido WHERE pedido = ?');
			$resultado = $modulos->db->query($delete, $id);
			
			// Inserta las lineas de los pedidos
			$importeTotal = 0.0;
			for ($c = 0; $c < count($tipos); $c++) {
				if ($descripcion[$c] != -1 && $tipos[$c] != -1) {
					// Primero obtiene el importe del tipo.
					$select = procesaSql('SELECT importe FROM #_caracteristicas WHERE id = ?');
					$resultados = $modulos->db->query($select, $descripcion[$c]);
					$importe = $resultados->fetchRow();
					$importeTotal += $importe['importe'];
			
					// E inserta la línea
					$insert = procesaSql('INSERT INTO #_lineapedido (tipo, valor, importe, pedido) VALUES (?, ?, ?, ?)');
					$resultados = $modulos->db->query($insert, Array($tipos[$c], $descripcion[$c], $importe['importe'], $id));
				}
			}
			
			// Ok, ahora se modifica el pedido
			$update = procesaSql('UPDATE #_pedidos SET numpedido = ?, fechaEntrada = ?, fechaCompromiso = ?, refCliente = ?, distribuidor = ?, codigonum = ?, importe = ?, observaciones = ? WHERE id = ?');
			$resultados = $modulos->db->query($update, Array($numPedido, $fechaEntrada, $fechaCompromiso, $refCliente, $distribuidor, $codigoNum, $importeTotal, $observaciones, $id));
			
			// Una vez grabado el pedido, si este es definitivo se elimina de la lista de pendientes
			if ($definitivo) {
				$delete = procesaSql('DELETE FROM #_pedidos_pendientes WHERE idpedido = ?');
				$resultado = $modulos->db->query($delete, $id);
			}
		}
	} else { // Es un pedido nuevo, se crea
		// Primero se insertan las lineas del pedido. Solo se insertan las líneas que tienen un tipo
		// o características distintos de -1
		$importeTotal = 0.0;
		for ($c = 0; $c < count($tipos); $c++) {
			if ($descripcion[$c] != -1 && $tipos[$c] != -1) {
				// Primero obtiene el importe del tipo.
				$select = procesaSql('SELECT importe FROM #_caracteristicas WHERE id = ?');
				$resultados = $modulos->db->query($select, $descripcion[$c]);
				$importe = $resultados->fetchRow();
				$importeTotal += $importe['importe'];
		
				// E inserta la línea
				$insert = procesaSql('INSERT INTO #_lineapedido (tipo, valor, importe, pedido) VALUES (?, ?, ?, ?)');
				$resultados = $modulos->db->query($insert, Array($tipos[$c], $descripcion[$c], $importe['importe'], $numPedido));
			}
		}

		$insert = procesaSql('INSERT INTO #_pedidos (id, fechaEntrada, fechaCompromiso, numPedido, refCliente, distribuidor, codigonum, observaciones, importe, idcomercial) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$resultados = $modulos->db->query($insert, Array($numPedido, $fechaEntrada, $fechaCompromiso, $numPedido, $refCliente, $distribuidor, $codigoNum, $observaciones, $importeTotal, $_SESSION['idUsuario']));
		
		// Si el pedido no es definitivo, se añade a la lista de pendientes
		if (!$definitivo) {
			$insert = procesaSql("INSERT INTO #_pedidos_pendientes (idpedido) VALUES (?)");
			$resultados = $modulos->db->query($insert, $numPedido);
		}
	}
}

/*************************************
 * Lee los datos de un pedido y muestra el formulario con los mismos
 *****************/
function leer_pedido() {
	global $modulos, $db;
	
	// Se lee el id y los datos del pedido
	$id = Modulo::getParam($_GET, 'id', '');
	if (is_numeric($id)) {
		$select = procesaSql('SELECT p.*, pp.idpedido
FROM (SELECT p1.id, p1.numpedido, DATE_FORMAT(p1.fechaentrada, \'%d/%m/%Y\') as fechaentrada, DATE_FORMAT(p1.fechacompromiso, \'%d/%m/%Y\') as fechacompromiso, c1.referencia as refcliente, 
d1.referencia as distribuidor, p1.codigonum, p1.observaciones, p1.importe, u1.login
FROM #_pedidos p1, #_clientes c1, #_distribuidores d1, #_usuarios u1
WHERE p1.refcliente = c1.id AND p1.idcomercial = u1.id AND p1.id = ? AND p1.distribuidor = d1.id) as p
LEFT JOIN #_pedidos_pendientes pp ON p.id = pp.idpedido');
		$resultados = $modulos->db->query($select, $id);
		if (PEAR::isError($resultados)) // Error con la base de datos o con la peticion
			devolver_error(2);
		else { // Ok, la peticion ha ido correctamente
			if ($resultados->numRows()) { // Hay resultados, se muestran
				$datosPedido = $resultados->fetchRow();
				
				// Ok, ahora se leen las lineas del pedido
				$select = procesaSql('SELECT * FROM #_lineapedido WHERE pedido = ?');
				$resultados = $modulos->db->query($select, $id);
				$datosPedido['lineasPedido'] = Array();
				while ($linea = $resultados->fetchRow())
					$datosPedido['lineasPedido'][] = $linea;

				// Verifica si el pedido está pendiente o no
				if (is_numeric($datosPedido['idpedido']))
					$datosPedido['pendiente'] = $datosPedido['idpedido'];
				
				muestra_pedido($datosPedido);
			} else { // No hay resultados
				devolver_error(3);
			}
		}
	} else { // Error, el id no es numerico
		devolver_error(1);
	}
}

function devolver_error($codigoError) {
global $modulos;
	cabeceraXML();
	?>
	<documento>
		<html>
		<![CDATA[ 
			<?php 
			echo $codigoError;
			print_r ($modulos->db);
			?>
		]]>
		</html>
		<botones>
			<![CDATA[
				<button type="button" onClick="listado_pedidos('cuadroDatos', 'botones');">Volver al listado</button>
			]]>
		</botones>
	</documento>
	<?php
}

function borrar_pedido() {
	global $modulos;
	
	$id = Modulo::getParam($_GET, 'id', '');
	if (is_numeric($id)) {
		$delete = procesaSql('delete from #_lineapedido where pedido = ?');
		$modulos->db->query($delete, $id);
		
		$delete = procesaSql('delete from #_pedidos where id = ?');
		$modulos->db->query($delete, $id);
	}
}
?>
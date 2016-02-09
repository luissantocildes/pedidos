<?php

/****************************
 * Módulo de pedidos
 *
 ******/

require ('const_usuarios.php');
require ('func_pedidos.php');
require ('funciones_clientes.php');
require ('funciones_distribuidores.php');
require ($this->dirPath.'/include/mail.php');

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
	$totalPedidos = ($_SESSION['tipo'] == C_ADMIN) ? total_pedidos() : total_pedidos($_SESSION['datosUsuario']['id']);
	$listaPedidos = Array();
	
	// Se muestra el listado de todos los pedidos.
	if ($_SESSION['tipo'] == C_ADMIN) {
		$select = procesaSql('SELECT p.id, p.numpedido, DATE_FORMAT(p.fechaentrada, \'%d/%m/%Y\') as fechaEntrada, p.refCliente, p.importe, p.importe_final, p.login, pp.idpedido '.
		'FROM (SELECT p1.id, p1.numpedido, p1.fechaentrada, p1.refCliente, p1.importe, p1.importe_final, u1.login FROM #_pedidos p1, #_usuarios u1 WHERE p1.idcomercial = u1.id) as p '.
		'LEFT JOIN #_pedidos_pendientes pp ON p.id = pp.idpedido '.
		'ORDER BY p.fechaEntrada desc, numpedido desc');
		$pedidos = $modulos->db->query($select);
	} else {
		$select = procesaSql('SELECT p.id, p.numpedido, DATE_FORMAT(p.fechaentrada, \'%d/%m/%Y\') as fechaEntrada, p.refCliente, p.importe, p.importe_final, p.login, pp.idpedido '.
		'FROM (SELECT p1.id, p1.numpedido, p1.fechaentrada, p1.refCliente, p1.importe, p1.importe_final, u1.login FROM #_pedidos p1, #_usuarios u1 WHERE p1.idcomercial = u1.id AND p1.idcomercial = ?) as p '.
		'LEFT JOIN #_pedidos_pendientes pp ON p.id = pp.idpedido '.
		'ORDER BY p.fechaEntrada desc, numpedido desc');
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
									<td onclick="leer_pedido('<?php echo $pedido['numpedido']; ?>', 'cuadroDatos', 'botones');"><?php echo $pedido['refCliente']; ?></td>
									<td onclick="leer_pedido('<?php echo $pedido['numpedido']; ?>', 'cuadroDatos', 'botones');"><?php echo $pedido['login']; ?></td>
									<td onclick="leer_pedido('<?php echo $pedido['numpedido']; ?>', 'cuadroDatos', 'botones');"><?php echo sprintf('%.2F &euro;', $pedido['importe_final']); ?></td>
									<td>
										<?php 
											if ($pedido['idpedido'] || $_SESSION['tipo']) {
											?><img src="imagenes/cancelar.gif" class="falsoBoton" onClick="borrar_pedido('<?php echo $pedido['id']; ?>', 'cuadroDatos', 'botones');">
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
	<script type="text/javascript">
		importeCaracteristicas = Array();
		importeTotal = 0;
		descuento = 0;
		importe = 0;
	</script>
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
	$tarifasCaracteristicas = &lee_caracteristicas();
	foreach ($tarifasCaracteristicas as $indice=>$tarifa) {
		$tarifasCaracteristicas[$indice] = $tarifa['importe'];
	}

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
		<javascript>
			importeCaracteristicas = Array(<?php // Genera un Array en javascript con las tarifas de las caracteristicas
				foreach ($tarifasCaracteristicas as $indice=>$tarifa)
					echo sprintf ("Array(%d, '%.2f'), ", $indice, $tarifa);
			?>
			Array());
			importe = <?php if ($datosPedido['importe']) echo $datosPedido['importe'];
							else echo 0.0; ?>;
			importeTotal = <?php if ($datosPedido['importe_final']) echo $datosPedido['importe_final'];
								else echo 0.0; ?>;
			descuento = <?php if ($datosPedido['descuento'] > 0) echo $datosPedido['descuento'];
								else echo $_SESSION['datosUsuario']['descuento']; ?>;
		</javascript>
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
	global $modulos;
	
	$id = Modulo::getParam($_POST, 'id', '');
	if ($id == -1)
		$id = Modulo::getParam($_GET, 'id', '');
	
	$tarifasCaracteristicas = &lee_caracteristicas();
	foreach ($tarifasCaracteristicas as $indice=>$tarifa) {
		$tarifasCaracteristicas[$indice] = $tarifa['importe'];
	}

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
		<javascript>
			importeCaracteristicas = Array(<?php // Genera un Array en javascript con las tarifas de las caracteristicas
				foreach ($tarifasCaracteristicas as $indice=>$tarifa)
					echo sprintf ("Array(%d, '%.2f'), ", $indice, $tarifa);
			?>
			Array());
			importe = <?php if ($datosPedido['importe']) echo $datosPedido['importe'];
							else echo 0.0; ?> ;
							
			importeTotal = <?php if ($datosPedido['importe_final']) echo $datosPedido['importe_final'];
								else echo 0.0; ?> ;
								
			descuento = <?php echo $datosPedido['descuento']; ?> ;
			
		</javascript>
		<botones>
			<![CDATA[
			<?php
				if (isset($datosPedido['pendiente'])) {
				?>
					<button type="button" onClick="enviar_formulario_pedido('formulario', 'cuadroDatos', 'botones', true);">Enviar pedido</button>
					<button type="button" onClick="enviar_formulario_pedido('formulario', 'cuadroDatos', 'botones', false);">Grabar borrador</button>
					<button type="button" onClick="if (confirm ('Va a perder los cambios realizados.\r\n¿Está seguro?')) listado_pedidos('cuadroDatos', 'botones');">Cancelar</button>
				<?php
				} else if ($_SESSION['tipo'] == C_ADMIN) {
				?>
					<button type="button" onClick="enviar_formulario_pedido('formulario', 'cuadroDatos', 'botones', true);">Enviar pedido</button>
					<button type="button" onClick="if (confirm ('Va a perder los cambios realizados.\r\n¿Está seguro?')) listado_pedidos('cuadroDatos', 'botones');">Cancelar</button>
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
	global $modulos;
	if (is_array($datosPedido) && count($datosPedido) == 0 && !$error) {
		date_default_timezone_set('Europe/Madrid');
		$datosPedido['fechaentrada'] = date('d/m/Y');
		$datosPedido['distribuidor'] = $_SESSION['datosUsuario']['nombrecomercial'];
		$datosPedido['codigonum'] = $_SESSION['datosUsuario']['referencia'];
		$datosPedido['puertanum'] = $datosPedido['observaciones'] =
		$datosPedido['refcliente'] = $datosPedido['idcomercial'] = '';
		$datosPedido['importe'] = $datosPedido['importe_final'] = 0.0;
		$datosPedido['id'] = -1;
		$datosPedido['lineasPedido'] = Array();
		$datosPedido['numpedido'] = nuevo_numero_pedido();
		$datosPedido['pendiente'] = '';
		$datosPedido['descuento'] = $_SESSION['datosUsuario']['descuento'];
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
			<?php echo $error; ?>
		</div>
	<?php
	}
	?>
		Nº pedido: <?php echo $datosPedido['numpedido']; ?><input type="hidden" id="id" name="id" value="<?php echo $datosPedido['id']; ?>"><input type="hidden" id="numPedido" name="numPedido" value="<?php echo $datosPedido['numpedido']; ?>"><br>
		<span class="<?php echo $claseFechaE; ?>">Fecha entrada: <input type="text" id="fechaEntrada" name="fechaEntrada" value="<?php echo $datosPedido['fechaentrada']; ?>" readonly>
		<?php
		if (isset($datosPedido['pendiente']) || $_SESSION['tipo'] == C_ADMIN) {
			?>
			<img src="imagenes/calendario.png" class="falsoBoton" align="top" onClick="openCalendar('', 'formulario', 'fechaEntrada', 'date');">
			<?php
		}
		?>
		</span><br>
		<span class="<?php echo $claseRefC; ?>">Referencia cliente: <input type="text" id="refCliente" name="refCliente" value="<?php echo $datosPedido['refcliente']; ?>">
		</span><br>
		<span class="<?php echo $claseDist; ?>">Distribuidor: <input type="text" id="distribuidor" name="distribuidor" value="<?php echo $datosPedido['distribuidor']; ?>" readonly>
		</span><br>
		<span class="<?php echo $claseCod; ?>">C&oacute;digo Nº: <input type="text" id="codigoNum" name="codigoNum" value="<?php echo $datosPedido['codigonum']; ?>" readonly></span><br>
		Observaciones:<textarea id="observaciones" name="observaciones"><?php echo $datosPedido['observaciones']; ?></textarea><br>
		<table id='pedido'>
			<tr>
				<th>Paso</th>
				<th>Concepto</th>
				<th>Descripci&oacute;n</th>
				<th>Importe</th>
			</tr>
			<?php
				$c = 0;
				foreach ($tipos as $datosTipo) {
					echo "<tr id='fila_$c'><td>", $c+1, '</td>';
					echo '<td>', $datosTipo['descripcion'], '</td>';
					echo '<td>';
					if (isset($datosPedido['pendiente']) || $_SESSION['tipo'] == C_ADMIN) {
						$aux = 'importe = cambia_importe(this, \''.($c+1).'\', \'td_importe\', \'td_descuento\', \'td_importeTotal\', importeCaracteristicas, importe)';
						if(isset($datosPedido['lineasPedido'][$c])) {
							$listaCaracteristicas = lee_caracteristicas($datosPedido['lineasPedido'][$c]['tipo']);
							echo crear_desplegable ($listaCaracteristicas, 'nombre', "descripcion_{$datosTipo['id']}", $datosPedido['lineasPedido'][$c]['valor'], $aux, 'codigo', ($error || $parametro['pendiente']) ? Array(-1, "Escoja una caracteristica") : false);
						} else {
							$listaCaracteristicas = lee_caracteristicas($datosTipo['id']);
							echo crear_desplegable ($listaCaracteristicas, 'nombre', "descripcion_{$datosTipo['id']}", -1, $aux, 'codigo', Array(-1, "Escoja una caracteristica"));
						}
						echo '<input type="hidden" id="importe_caracteristica_'.($c+1).'" name="importe_caracteristica_'.($c+1).'" value="';
						if (isset ($datosPedido['lineasPedido'][$c]))
							echo $datosPedido['lineasPedido'][$c]['importe'];
						else echo 0;
						echo '">';
					} else {
						$listaCaracteristicas = lee_caracteristicas($datosTipo['id']);
						echo $listaCaracteristicas[$datosPedido['lineasPedido'][$c]['valor']]['nombre'].' ('.$listaCaracteristicas[$datosPedido['lineasPedido'][$c]['valor']]['codigo'].')';
					}
					echo '</td><td id="celda_caracteristica_'.($c+1).'" style="text-align:right; padding-right:5px;">';
					if (isset ($datosPedido['lineasPedido'][$c])) {
						echo sprintf('%10.2f &euro;', $datosPedido['lineasPedido'][$c]['importe']);
					}
					echo '</td></tr>';
					$c++;
				}
			?>
			<tr>
				<td style="text-align:right;" colspan='3'>Total</td>
				<td id="td_importe" style="text-align:right; padding-right:5px;"><?php echo sprintf('%10.2f &euro;', $datosPedido['importe']); ?></td>
			</tr>
			<?php
				// Mira si hay un descuento a calcular
				if ($datosPedido['id'] == -1 && $_SESSION['datosUsuario']['descuento'] > 0) {
					$descuentoPorCiento = $_SESSION['datosUsuario']['descuento'];
				} else if ($datosPedido['descuento'] > 0) {
					$descuentoPorCiento = $datosPedido['descuento'];
				} else $descuentoPorCiento = 0.0;
				$descuentoTotal = $datosPedido['importe'] * $descuentoPorCiento / 100.0;
				$importeFinal = $datosPedido['importe'] - $descuentoTotal;

			?>
			<tr style="<?php if ($descuentoPorCiento == 0) echo "display: none" ?>">
				<td style="text-align:right;" colspan='3'>Descuento (<?php echo sprintf ('%.2f ', $datosPedido['descuento']); ?>%)</td>
				<td id="td_descuento" style="text-align:right; padding-right:5px;"><?php echo sprintf('-%10.2f &euro;', $descuentoTotal); ?></td>
			</tr>
			<tr>
				<td style="text-align:right;" colspan='3'>Importe Total</td>
				<td id="td_importeTotal" style="text-align:right; padding-right:5px;"><?php echo sprintf('%10.2f &euro;', $importeFinal); ?></td>
			</tr>
		</table>
		<input type='hidden' name='descuento' id='descuento' value='<?php echo $descuentoPorCiento; ?>'>
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
	$refCliente = Modulo::getParam($_POST, 'refCliente', '');
	$distribuidor = Modulo::getParam($_POST, 'distribuidor', '');
	$codigoNum = Modulo::getParam($_POST, 'codigoNum', 0);
	$observaciones = Modulo::getParam($_POST, 'observaciones', '');
	//$descuento = Modulo::getParam($_POST, 'descuento', 0.0);

	// Se leen los desplegables
	$totalDesplegables = Modulo::getParam($_POST, 'totalDesplegables', 0);
	if (!is_numeric($totalDesplegables))
		$totalDesplegables = 0;
	foreach ($_POST as $nombre=>$valor) {
		$partesNombre = explode('_', $nombre);
		switch (strtolower($partesNombre[0])) {
			case 'descripcion':
				$descripcion['id'][] = $valor;
				$descripcion['tipo'][] = $partesNombre[1];
				break;
		}
	}
	// Se verifica si se ha recibido algún desplegable
	if (count ($descripcion['id']) < 10)
		$error = ERROR_DESPLEGABLES;

	// Verifica que los valores pasados en los desplegables sean correctos
	// Primero se verifican los desplegables de caracteristicas, pero solo si no es un pedido definitivo
	$select = procesaSql('select count(*) as total from #_caracteristicas WHERE id IN ('.implode(',', $descripcion['id']).') AND borrado = 0');
	$resultado = $modulos->db->query($select);
	$total = $resultado->fetchRow();

	if (count($descripcion['id']) != $total['total'] && $definitivo) {
		$error = ERROR_DESPLEGABLES;
	}

	// verifica las fechas
	if (!verifica_fecha($fechaEntrada))
		$error |= ERROR_FECHA_ENTRADA;

	// Si el ID > -1 entonces verifica que el pedido esté pendiente, excepto si el usuario es administrador
	if ($id > -1) {
		$select = procesaSql('SELECT count(*) as total FROM #_pedidos_pendientes WHERE idpedido = ?');
		$resultado = $modulos->db->query($select, $id);
		$totalPedidos = $resultado->fetchRow();
		if ($totalPedidos['total'] != 1 && $_SESSION['tipo'] != C_ADMIN)
			$error |= ERROR_PEDIDO_PENDIENTE;
	}
	
	// se coge el descuento para el usuario. Si el ID del pedido es > -1, entonces
	// se coge el descuento del pedido actual, sino el del usuario
	if ($id > -1) {
		$select = procesaSql("SELECT descuento FROM #_pedidos WHERE id = ?");
		$resultados = $modulos->db->query($select, $id);
		if ($resultados->numRows()) {
			$aux = $resultados->fetchRow();
			$descuento = $aux['descuento'];
		} else $descuento = 0.0;
	} else 
		$descuento = $_SESSION['datosUsuario']['descuento'];

	// Carga los parametros en un array
	$parametros = Array('id'=>$id,
						'numpedido'=>$numPedido,
						'fechaentrada'=>$fechaEntrada,
						'refcliente'=>$refCliente,
						'distribuidor'=>$distribuidor,
						'codigonum'=>$codigoNum,
						'observaciones'=>$observaciones,
						'descuento'=>$descuento);
						
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
		for ($c = 0; $c < count($descripcion['tipo']); $c++) {
			$lineas[] = Array ('tipo' => $descripcion['tipo'][$c], 'valor' => $descripcion['id'][$c],
								'importe' => '', 'pedido' => '', 'id' => '');
		}
		$parametros['lineasPedido'] = $lineas;
		muestra_pedido($parametros, $error);
	} else { // sino se guarda el pedido y se muestra el índice de pedidos
		guarda_pedido($id, $numPedido, convierte_fecha($fechaEntrada), $refCliente, $distribuidor, $codigoNum, $observaciones, $descripcion, $descuento, $definitivo);
		if ($definitivo) {
			$destinatarios = Array ('admin');
			if ($_SESSION['tipo'] != C_ADMIN)
				$destinatarios[] = $_SESSION['usuario'];
			envia_email ($destinatarios, $numPedido);
		}
		listado_pedidos();
	}
}

/*****************************
 * Envia un email al usuario indicado con los datos del pedido
 *****************************/
function envia_email ($arrayLogin, $idPedido) {
	global $modulos, $db, $emailRemitente;
	
	// Lee los datos del pedido
	$select = procesaSql('SELECT * FROM #_pedidos WHERE id = ?');
	$resultados = $modulos->db->query($select, $idPedido);
	if (PEAR::isError($resultados)) {// Error con la base de datos o con la peticion
		devolver_error(2, $resultados);
		die ("123");
	} else
		$datosPedido = $resultados->fetchRow();

		// Busca las lineas del pedido
	$select = procesaSql('SELECT l.id, l.tipo, l.valor, l.importe, t.descripcion, c.nombre FROM #_lineapedido l, #_tipocaracteristica t, #_caracteristicas c WHERE c.id = l.valor AND l.tipo = t.id AND l.pedido = ? ORDER BY t.orden');
	$resultados = $modulos->db->query($select, $idPedido);
	//echo $select."\r\n";
	//print_r ($resultados);

	$datosPedido['lineasPedido'] = Array();
	while ($linea = $resultados->fetchRow())
		$datosPedido['lineasPedido'][] = $linea;

	if (count($datosPedido)) { // Ok, el pedido existe, entonces se envía el email
		
		$contenido = "Pedido nº {$datosPedido['numpedido']} realizado el {$datosPedido['fechaentrada']} por {$datosPedido['distribuidor']}\r\n\r\n";
		$contenido .= "Datos del pedido:\r\n";
		$contenido .= "Nº del pedido: {$datosPedido['numpedido']}\r\n";
		$contenido .= "Fecha de entrada: {$datosPedido['fechaentrada']}\r\n";
		$contenido .= "Referencia cliente: {$datosPedido['refcliente']}\r\n";
		$contenido .= "Distribuidor: {$datosPedido['distribuidor']}\r\n";
		$contenido .= "Código de puerta: {$datosPedido['codigonum']}\r\n";
		$contenido .= "Observaciones:\r\n{$datosPedido['observaciones']}\r\n-----\r\n";
		foreach ($datosPedido['lineasPedido'] as $linea) {
			$contenido .= sprintf("%21s: %15s %9.2f euro\r\n", $linea['descripcion'], $linea['nombre'], $linea['importe']);
		}
		$contenido .= sprintf("%37s: %9.2f euro\r\n", 'Total', $datosPedido['importe']);
		$descuento = $datosPedido['importe'] * $datosPedido['descuento'] / 100.0 * -1.0;
		$contenido .= sprintf("%37s: %9.2f euro\r\n", 'Descuento ('.($datosPedido['descuento']).'%)', $descuento);
		$contenido .= sprintf("%37s: %9.2f euro\r\n", 'Importe total', $datosPedido['importe_final']);
		//$modulos->mostrar($contenido); die();
		// Ahora envía el email a cada usuario indicado en el array $arrayLogin
		foreach ($arrayLogin as $usuario) {
			$select = procesaSql ('SELECT * FROM #_usuarios WHERE login = ?');
			$resultados = $modulos->db->query($select, $usuario);

			if (!PEAR::isError($resultados)) {
				if ($resultados->numRows() == 1) {
					$datosUsuario = $resultados->fetchRow();
					$mail = new Email();
					$mail->setTo($datosUsuario['email']);
					$mail->setFrom($emailRemitente);
					$mail->setSubject('Pedido realizado en Segurestil');
					$mail->setText($contenido);
					$mail->send();
					
				}
			}
		}
	};
}

/***********************
 * guarda el pedido
 ***************/
function guarda_pedido($id, $numPedido, $fechaEntrada, $refCliente, $distribuidor, $codigoNum, $observaciones, $descripcion, $descuento, $definitivo) {
	global $modulos, $db;
	
	// No se verifican los parametros de entrada ya que se considera que estos han sido comprobados antes de llamar a la función
	// Tampoco se escapan los mismos, ya que se considera que lo hace el php automáticamente
	
	// Primero se comprueba si el pedido está pendiente
	if ($id > -1) {
		$select = procesaSql('SELECT count(*) as total FROM #_pedidos_pendientes WHERE idpedido = ?');
		$resultado = $modulos->db->query($select, $id);
		$totalPendiente = $resultado->fetchRow();
		
		// Si el pedido está pendiente, se actualizan los datos, sino no se hace nada
		if ($totalPendiente['total'] != 0) {
			$select = procesaSql('SELECT l.valor, l.tipo, t.descripcion, l.importe FROM #_lineapedido l, #_tipocaracteristica t WHERE t.id = l.tipo AND l.pedido = ? ORDER BY t.orden');
			$resultado = $modulos->db->query($select, $id);
			$lineas = Array();
			while ($aux = $resultado->fetchRow())
				$lineas[] = $aux;

			// Comprueba las líneas que han cambiado y se actualizan
			for ($c = 0; $c < count($descripcion['id']); $c++) {
				if ($descripcion['id'][$c] != $lineas[$c]['valor']) {
					$update = procesaSql('UPDATE #_lineapedido l, #_caracteristicas c SET l.valor = ?, l.importe = c.importe WHERE c.id = ? AND l.valor = ?');
					//echo "$update\r\n";
					$resultado = $modulos->db->query($update, Array($descripcion['id'][$c], $descripcion['id'][$c], $lineas[$c]['valor']));
					if (PEAR::isError($resultado)) {
						devolver_error(2, $resultado);
						die("321");
					}
				}
			}
			
			// Calcula el total y los descuentos
			$total = 0;
			$select = procesaSql ('SELECT SUM(importe) as total FROM #_lineapedido WHERE pedido = ?');
			$resultado = $modulos->db->query($select, $id);
			if (PEAR::isError($resultado)) {
				devolver_error(2, $resultados);
				return;
				//die("123");
			} else {
				$aux = $resultado->fetchRow();
				$total = $aux['total'];
			}
			$importeFinal = $total - $total * $descuento / 100.0;
			
			// Una vez actualizadas las líneas se actualizan el importe total
			$update = procesaSql('UPDATE #_pedidos SET refcliente = ?, observaciones = ?, importe = ?, importe_final = ?, descuento = ? WHERE id = ?');
			$resultado = $modulos->db->query($update, Array($refCliente, $observaciones, $total, $importeFinal, $descuento, $id));
			if (PEAR::isError($resultado)) {
				devolver_error(2, $resultados);
				return;
				//die("123");
			}
			
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
		for ($c = 0; $c < count($descripcion['id']); $c++) {
			//if ($descripcion['id'][$c] != -1) {
				// Primero obtiene el importe del tipo.
				$select = procesaSql('SELECT importe FROM #_caracteristicas WHERE id = ?');
				$resultados = $modulos->db->query($select, $descripcion['id'][$c]);
				$importe = $resultados->fetchRow();
				$importeTotal += $importe['importe'];

				// E inserta la línea
				$insert = procesaSql('INSERT INTO #_lineapedido (tipo, valor, importe, pedido) VALUES (?, ?, (select importe from #_caracteristicas WHERE id = ?), ?)');
				#$resultados = $modulos->db->query($insert, Array($descripcion['tipo'][$c], $descripcion['id'][$c], $importe['importe'], $numPedido));
				$resultados = $modulos->db->query($insert, Array($descripcion['tipo'][$c], $descripcion['id'][$c], $descripcion['id'][$c], $numPedido));
			//}
		}

		// Calcula el descuento a aplicar
		//$descuento = $_SESSION['datosUsuario']['descuento'];
		if ($descuento > 0)
			$importeFinal = $importeTotal - $importeTotal * $descuento / 100;
		else $importeFinal = $importeTotal;
		
		$insert = procesaSql('INSERT INTO #_pedidos (id, fechaEntrada, numPedido, refCliente, distribuidor, codigonum, observaciones, importe, descuento, importe_final, idcomercial) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$resultados = $modulos->db->query($insert, Array($numPedido, $fechaEntrada, $numPedido, $refCliente, $distribuidor, $codigoNum, $observaciones, $importeTotal, $descuento, $importeFinal, $_SESSION['idUsuario']));
		
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
FROM (SELECT p1.id, p1.numpedido, DATE_FORMAT(p1.fechaentrada, \'%d/%m/%Y\') as fechaentrada, p1.refCliente as refcliente, 
p1.codigonum, p1.observaciones, p1.importe, u1.login, u1.nombrecomercial as distribuidor, p1.descuento, importe_final as importeFinal
FROM #_pedidos p1, #_usuarios u1
WHERE p1.idcomercial = u1.id AND p1.id = ?) as p
LEFT JOIN #_pedidos_pendientes pp ON p.id = pp.idpedido');
		$resultados = $modulos->db->query($select, $id);
		if (PEAR::isError($resultados)) // Error con la base de datos o con la peticion
			devolver_error(2, $resultados);
		else { // Ok, la peticion ha ido correctamente
			if ($resultados->numRows()) { // Hay resultados, se muestran
				$datosPedido = $resultados->fetchRow();
				
				// Ok, ahora se leen las lineas del pedido
				$select = procesaSql('SELECT l.id, l.tipo, l.valor, l.importe FROM #_lineapedido l, #_tipocaracteristica t WHERE l.tipo = t.id AND l.pedido = ? ORDER BY t.orden');
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

function devolver_error($codigoError, $aux=false) {
global $modulos;
	cabeceraXML();
	?>
	<documento>
		<html>
		<![CDATA[ 
			<?php 
			echo $codigoError;
			print_r ($modulos->db);
			echo "\r\n";
			if ($aux) echo $aux->getMessage();
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
	
	$id = Modulo::getParam($_POST, 'id', '');
	if (is_numeric($id)) {
		$delete = procesaSql('delete from #_lineapedido where pedido = ?');
		$modulos->db->query($delete, $id);
		
		
		$delete = procesaSql('delete from #_pedidos where id = ?');
		$modulos->db->query($delete, $id);
		
		$delete = procesaSql('delete from #_pedidos_pendiente where idpedido = ?');
		$modulos->db->query($delete, $id);
	}
	listado_pedidos();
}
?>
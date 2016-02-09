<?php

/****************************
 * Módulo para el tratamiento de los clientes.
 ****************************/

global $modulos, $db;
 
switch ($task) {
	case 'borrarAtributo': // Borra un atributo
		borrar_atributo();
		break;
	case 'grabarAtributo': // Graba los datos de un atributo
		grabar_atributo();
		break;
	case 'formularioAtributoVacio':
		formulario_vacio();
		break;
	case 'leerAtributo':
		leer_atributo();
		break;
	case 'listaAtributos':
		leer_lista_atributos();
		break;
	case 'listaTipos':
		leer_lista_tipos();
		break;
    default: // Por defecto muestra la lista de usuarios
        indice_campos();
}

/********************************
 * Borra el atributo indicado
 ********************************/
function borrar_atributo() {
	global $modulos, $db;
	
	// Lee el id del usuario a borrar y comprueba que sea correcto
	$id = Modulo::getParam ($_POST, 'id', -1);
	$tipo = Modulo::getParam($_POST, 'tipo', 0);
	
	// Primero se mira a ver si hay algún pedido en el que se utilice este atributo
	$cadena = procesaSql('SELECT * FROM #_lineapedido WHERE tipo = ?');
	$lineas = $modulos->db->query ($cadena, $id);
	
	if (PEAR::isError($lineas)) {
	} else { // Si el atributo se está usando, entonces solo se marca como borrado, sino se borra realmente
		if (is_numeric ($id) && $id > -1) {
			if ($lineas->numRows()) { 
				$cadena = procesaSql('UPDATE #_caracteristicas SET borrado = 1 WHERE id = ?');
			} else { 
				$cadena = procesaSql('DELETE FROM #_caracteristicas WHERE id = ?');
			}
			$resultado = $modulos->db->query ($cadena, $id);
		}
	}
	
	leer_lista_atributos($tipo);
}

/***********************************
 * Graba los datos de un atributo
 ***********************************/
function grabar_atributo() {
	global $modulos, $db;
	
	// Lee los datos del formulario
	$id = Modulo::getParam($_POST, 'id', -1);
	$nombre = Modulo::getParam($_POST, 'nombre', '');
	$codigo = Modulo::getParam($_POST, 'codigo', '');
	$importe = Modulo::getParam($_POST, 'importe', 0);
	$tipo = Modulo::getParam($_POST, 'tipo', 0);
	
	$error = '';
	$js = '';
	if (is_numeric($id)) { // Ok, el id es un número
		// Si el id pasado es -1, entonces se crea el atributo, sino se modifica un usuario existente
		if ($id == -1) { // Crear un usuario
			$insert = procesaSql ("INSERT INTO #_caracteristicas (nombre, codigo, importe, tipo) VALUES (?, ?, ?, ?)");
			$resultado = $modulos->db->query($insert, Array($nombre, $codigo, $importe, $tipo));
		} else { // Modificar uno existente
			$update = procesaSql ("UPDATE #_caracteristicas SET nombre = ?, codigo = ?, importe = ? WHERE id = ?");
			$parametros = Array($nombre, $codigo, $importe, $id);
			$resultado = $modulos->db->query($update, $parametros);
			if (PEAR::isError($resultado)) {
				mostrar_error_sql ($resultado);
			}
		}
	} else {
		$error = 'Parámetro incorrecto';
	}
	leer_lista_atributos($tipo);
}

/*****************************************
 * Devuelve el formulario vacío
 *****************************************/
function formulario_vacio() {
	cabeceraXML();
	?>
	<documento>
		<html>
		<![CDATA[ 
			<?php 
			echo formulario_atributo();
			?>
		]]>
		</html>
		<botones>
			<![CDATA[
				<button type="button" onClick="if (enviar_formulario_atributo('formulario', 'listaAtributos', 'listaTipos', 'etiquetaAtributos')) {vaciar_capas(Array('cuadroDatos', 'botones')); desbloquear_boton('botonCrear');};">Crear atributo</button>
				<button type="button" onClick="vaciar_capas(Array('cuadroDatos', 'botones')); desbloquear_boton('botonCrear');">Cancelar</button>
			]]>
		</botones>
	</documento>
	<?php
}

/*********************************
 * Devuelve el texto del formulario con los datos pasados
 *********************************/
function formulario_atributo ($datosAtributo = Array()) {
	if (count($datosAtributo) == 0) {
		$datosAtributo['codigo'] = $datosAtributo['nombre'] = '';
		$datosAtributo['importe'] = 0.0;
		$datosAtributo['id'] = -1;
	};
	?>
		Nombre:<input type="text" id="nombre" name="nombre" value="<?php echo $datosAtributo['nombre']; ?>" /><br />
		C&oacute;digo:<input type="text" id="codigo" name="codigo" value="<?php echo $datosAtributo['codigo']; ?>" /><br />
		Importe:<input type="text" id="importe" name="importe" value="<?php printf ("%.2F", $datosAtributo['importe']); ?>" /><br />
		<input type="hidden" name="id" id="id" value="<?php echo $datosAtributo['id']; ?>" />
	<?php
}

/******************************
 * Lee los datos del atributo seleccionado
 ******************************/
function leer_atributo() {
	global $modulos, $db;
	
	cabeceraXML();
	$id = Modulo::getParam($_GET, 'id', 0);
	if (is_numeric($id)) {
		$select = procesaSql ("SELECT * FROM #_caracteristicas WHERE id = ?");
		$atributo = $modulos->db->query ($select, $id);
		if (PEAR::isError($atributo)) {
			echo "<error texto='".$atributo->getUserInfo()."' />";
		} else {
			$datosAtributo = $atributo->fetchRow();
			?>
			<documento>
				<html>
				<![CDATA[
					<?php 
					echo formulario_atributo($datosAtributo);
					?>
				]]>
				</html>
				<botones>
					<![CDATA[
						<button type="button" onClick="enviar_formulario_atributo('formulario', 'listaAtributos', 'listaTipos', 'etiquetaAtributos');">Guardar cambios</button>
						<button type="reset">Restaurar formulario</button>
						<button type="button" onClick="borrar_atributo('formulario', 'listaAtributos', 'listaTipos', 'etiquetaAtributos');">Borrar atributo</button>
					]]>
				</botones>
			</documento>
			<?php
		}
	} else {
		echo "No es un numero";
	}
}

/******************************
 * Lee la lista de tipos
 ******************************/
function leer_lista_tipos() {
	global $modulos, $db;
	
	$select = procesaSql("SELECT * FROM #_tipocaracteristica ORDER BY descripcion");
	$tipos = $modulos->db->query($select);
	
	cabeceraXML();
	echo "<tipos>\r\n";
	$error = '';
	if (PEAR::isError($tipos)) {
	} else {
		if ($tipos->numRows()) {
			while ($tipo = $tipos->fetchRow()) {
				echo "<tipo id='".$tipo['id']."' descripcion='".htmlspecialchars($tipo['descripcion'], ENT_QUOTES)."' />\r\n";
			}	
		}
	}
	echo "</tipos>\r\n";
}

/******************************
 * Lee la lista de atributos
 ******************************/
function leer_lista_atributos($tipo=-1) {
	global $modulos, $db;
	
	// Tipo del atributo a leer. Verifica que exista
	if ($tipo > 0)
		$idTipo = $tipo;
	else {
		$idTipo = Modulo::getParam($_GET, 'tipo', 1);
		if (!is_numeric($idTipo))
			$idTipo = 1;
		else if ($idTipo < 1)
			$idTipo = 1;
	}
	
	// Lee el nombre del tipo de atributo
	$select = procesaSql("SELECT descripcion FROM #_tipocaracteristica WHERE id = ?");
	$tipo = $modulos->db->query($select, $idTipo);
	if (PEAR::isError($tipo)) {
	} else {
		if ($tipo->numRows())
			$nombreTipo = $tipo->fetchRow();
	}

	// Lee la lista de atributos	
	$select = procesaSql("SELECT * FROM #_caracteristicas WHERE tipo = ? AND borrado = 0 ORDER BY codigo, nombre");
	$atributos = $modulos->db->query($select, $idTipo);
	
	cabeceraXML();
	echo "<atributos>\r\n";
	if (PEAR::isError($atributos)) {
		echo "<atributo id='-1' codigo='".$atributos->getUserInfo()."' nombre='' importe='' />";
	} else {
		if ($atributos->numRows()) {
			while ($atributo = $atributos->fetchRow()) {
				echo "<atributo id='".$atributo['id']."' codigo='".$atributo['codigo']."' nombre='".htmlspecialchars($atributo['nombre'], ENT_QUOTES)."' importe='".$atributo['importe']."' />\r\n";
			}	
		} else {
			echo "<atributo id='-1' codigo='No hay atributos' nombre='' importe='' />";
		}
	}
	echo "<nombreAtributo texto='".$nombreTipo['descripcion']."' />\r\n";
	echo "</atributos>\r\n";
}

/******************************
 * Muestra el índice del módulo de usuarios
 ******************************/
function indice_campos() {
    ?>
    <form name='formulario' id='formulario' method='post' action=''>
		<div id='lista'>
			Tipo
			<select id='listaTipos' name='listaTipos' onChange='leer_lista_atributos(this, "listaAtributos", "etiquetaAtributos");'>
				<option>Cargando lista...</option>
			</select>
			<span id='etiquetaAtributos'>Atributo</span>
			<select id='listaAtributos' name='listaAtributos' onChange='leer_atributo(this, "cuadroDatos", "botones");' size=10>
				<option>Cargando lista...</option>
			</select>

			<button type="button" onClick="crear_nuevo_atributo('cuadroDatos', 'botones', this);" id="botonCrear">Crear nuevo atributo</button>
		</div>
		<div id='datos'>
			Datos
			<div id='cuadroDatos'>
			</div>
			<div id='botones'>
			</div>
		</div>
		<script language="javascript">
			leer_lista_tipos('listaTipos', 'listaAtributos', 'etiquetaAtributos');
		</script>
	</form>
    <?php
}

?>

<?php

/****************************
 * Módulo de distribuidores
 *
 ******/
 
global $modulos, $db;
 
switch ($task) {
	case 'grabarDistribuidor': // Graba los datos de un distribuidor
		grabar_distribuidor();
		break;
	case 'formularioDistribuidor': // Devuelve un formulario vacio para un nuevo distribuidor
		formulario_distribuidor_vacio();
		break;
	case 'datosDistribuidor': // Muestra los datos de un distribuidor
		datos_distribuidor();
		break;
	case 'listaDistribuidores': // Devuelve la lista de distribuidores
		lista_distribuidores();
		break;
	case 'borrarDistribuidor': // Borra el distribuidor indicado
		borrar_distribuidor();
		break;
    default: // Por defecto muestra la lista de distribuidores
        indice_distribuidores();
}

/********************************
 * Borra el distribuidor indicado
 ********************************/
function borrar_distribuidor() {
	global $modulos, $db;
	
	// Lee el id del distribuidor a borrar y comprueba que sea correcto
	$id = Modulo::getParam ($_POST, 'id', -1);
	if (is_numeric ($id) && $id > -1) {
		$delete = procesaSql('DELETE FROM #_distribuidores WHERE id = ?');
		$resultado = $modulos->db->query ($delete, $id);
	} else {
	}
	lista_distribuidores();
}

/********************************
 * Graba los datos del distribuidor nuevo o modificado
 ********************************/
function grabar_distribuidor() {
	global $modulos, $db;
	
	// Lee los datos del formulario
	$id = Modulo::getParam($_POST, 'id', -1);
	$referencia = Modulo::getParam($_POST, 'referencia', '');
	$nombreComercial = Modulo::getParam($_POST, 'nombrecomercial', '');
	$contacto = Modulo::getParam($_POST, 'contacto', '');
	$direccion = Modulo::getParam($_POST, 'direccion', '');
	$localidad = Modulo::getParam($_POST, 'localidad', '');
	$provincia = Modulo::getParam($_POST, 'provincia', '');
	$telefono = Modulo::getParam($_POST, 'telefono', '');
	$email = Modulo::getParam($_POST, 'email', '');
	$cif = Modulo::getParam($_POST, 'cif', '');
	
	$error = '';
	$js = '';
	if (is_numeric($id)) { // Ok, el id es un número
		// Si el id pasado es -1, entonces se crea el distribuidor, sino se modifica un distribuidor existente
		if ($id == -1) { // Crear un distribuidor
			$insert = procesaSql ("INSERT INTO #_distribuidores (referencia, nombrecomercial, contacto, direccion, localidad, provincia, telefono, email, cif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$resultado = $modulos->db->query($insert, Array($referencia, $nombreComercial, $contacto, $direccion, $localidad, $provincia, $telefono, $email, $cif));
		} else { // Modificar uno existente
			$update = procesaSql("UPDATE #_distribuidores SET referencia = ?, nombrecomercial = ?, contacto = ?, direccion = ?, localidad = ?, provincia = ?, telefono = ?, email = ?, cif = ? WHERE id = ?");
			$parametros = Array($referencia, $nombreComercial, $contacto, $direccion, $localidad, $provincia, $telefono, $email, $cif, $id);
			$resultado = $modulos->db->query($update, $parametros);
			if (PEAR::isError($resultado)) {
				mostrar_error_sql ($resultado);
			}
		}
	} else {
		$error = 'Parámetro incorrecto';
	}
	lista_distribuidores();
}

/*********************************
 * Devuelve el texto del formulario con los datos pasados
 *********************************/
function formulario_distribuidor ($datosDistribuidor = Array()) {
	if (count($datosDistribuidor) == 0) {
		$datosDistribuidor = Array('id'=>-1, 'referencia'=>'', 'nombrecomercial'=>'', 'contacto'=>'', 'direccion'=>'',
								'localidad'=>'', 'provincia'=>'', 'telefono'=>'', 'email'=>'', 'cif'=>'');
	};
	?>
		Referencia: <input type="text" id="referencia" name="referencia" value="<?php echo $datosDistribuidor['referencia']; ?>" /><br />
		Nombre comercial: <input type="text" id="nombrecomercial" name="nombrecomercial" value="<?php echo $datosDistribuidor['nombrecomercial']; ?>" /><br />
		Persona de contacto: <input type="text" id="contacto" name="contacto" value="<?php echo $datosDistribuidor['contacto']; ?>" /><br />
		Direcci&oacute;n: <input type="text" id="direccion" name="direccion" value="<?php echo $datosDistribuidor['direccion']; ?>" /><br />
		Localidad: <input type="text" id="localidad" name="localidad" value="<?php echo $datosDistribuidor['localidad']; ?>" /><br />
		Provincia: <input type="text" id="provincia" name="provincia" value="<?php echo $datosDistribuidor['provincia']; ?>" /><br />
		Tel&eacute;fono: <input type="text" id="telefono" name="telefono" value="<?php echo $datosDistribuidor['telefono']; ?>" /><br />
		E-mail: <input type="text" id="email" name="email" value="<?php echo $datosDistribuidor['email']; ?>" /><br />
		C.I.F.: <input type="text" id="cif" name="cif" value="<?php echo $datosDistribuidor['cif']; ?>" /><br />
		<input type="hidden" id="id" name="id" value="<?php echo $datosDistribuidor['id']; ?>" /><br />
	<?php
}

/********************************
 * Devuelve los datos de un distribuidor como un formulario
 ********************************/
function datos_distribuidor() {
	global $modulos, $db;
	
	cabeceraXML();
	$idDistribuidor = Modulo::getParam($_GET, 'id', '');
	if (is_numeric($idDistribuidor)) {
		$select = procesaSql ("SELECT * FROM #_distribuidores WHERE id = ?");
		$distribuidor = $modulos->db->query ($select, $idDistribuidor);
		if (PEAR::isError($distribuidor)) {
			echo "erroR";
		} else {
			$datosDistribuidor = $distribuidor->fetchRow();
			?>
			<documento>
				<html>
				<![CDATA[
					<?php 
					echo formulario_distribuidor($datosDistribuidor);
					?>
				]]>
				</html>
				<botones>
					<![CDATA[
						<button type="button" onClick="if (enviar_formulario_distribuidor('formulario', 'listaDistribuidores')) {vaciar_capas(Array('cuadroDatos', 'botones')); };">Guardar cambios</button>
						<button type="reset">Restaurar formulario</button>
						<button type="button" onClick="borrar_distribuidor('formulario', 'listaDistribuidores');">Borrar distribuidor</button>
					]]>
				</botones>
			</documento>
			<?php
		}
	} else {
		echo "No es un numero";
	}
}

/*****************************************
 * Devuelve el formulario vacío
 *****************************************/
function formulario_distribuidor_vacio() {
	cabeceraXML();
	?>
	<documento>
		<html>
		<![CDATA[ 
			<?php 
			echo formulario_distribuidor();
			?>
		]]>
		</html>
		<botones>
			<![CDATA[
				<button type="button" onClick="if (enviar_formulario_distribuidor('formulario', 'listaDistribuidores')) {vaciar_capas(Array('cuadroDatos', 'botones')); desbloquear_boton('botonCrear');};">Crear distribuidores</button>
				<button type="button" onClick="vaciar_capas(Array('cuadroDatos', 'botones')); desbloquear_boton('botonCrear');">Cancelar</button>
			]]>
		</botones>
	</documento>
	<?php
}

/*********************************
 * Devuelve una lista de distribuidores por XML
 *********************************/
function lista_distribuidores() {
    global $modulos, $db;
    
    // Lee la lista de distribuidores de la bbdd
    $select = procesaSql ("SELECT * FROM #_distribuidores ORDER BY nombrecomercial, referencia");
    $distribuidores = $modulos->db->query($select);

    cabeceraXML();
    echo "<distribuidores>";
    if (PEAR::isError ($distribuidores)) {
    	echo "<distribuidor id='-1' referencia='' nombrecomercial='".$distribuidores->getUserInfo()."' />";
    } else {
        if ($distribuidores->numRows()) { // Ok, hay distribuidores, se lee la lista
            while ($distribuidor = $distribuidores->fetchRow()) {
            	echo "<distribuidor ";
            	foreach ($distribuidor as $clave=>$valor) {
            		echo $clave, "='", $valor, "' ";
            	}
            	echo "/>\r\n";
            }
        } else {
        	echo "<distribuidor id='-1' referencia='' nombrecomercial='No hay distribuidores' />";
        }
    }
	echo "</distribuidores>";
}

/******************************
 * Muestra el índice del módulo de distribuidores
 ******************************/
function indice_distribuidores() {
    ?>
    <form name='formulario' id='formulario' method='post' action=''>
		<div id='lista'>
			Distribuidores
			<select id='listaDistribuidores' name='listaDistribuidores' onChange='leer_distribuidor(this, "cuadroDatos", "botones");' size=10>
				<option>Cargando lista...</option>
			</select>

			<button type="button" onClick="crear_nuevo_distribuidor('cuadroDatos', 'botones', this);" id="botonCrear">Crear nuevo distribuidor</button>
		</div>
		<div id='datos'>
			Datos
			<div id='cuadroDatos'>
			</div>
			<div id='botones'>
			</div>
		</div>
		<script language="javascript">
			leer_lista_distribuidores('listaDistribuidores');
		</script>
	</form>
    <?php
}

?>

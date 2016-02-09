<?php

/****************************
 * Módulos de clientes
 *
 ******/
 
require ('const_usuarios.php');
  
global $modulos, $db;
 
switch ($task) {
	case 'grabarCliente': // Graba los datos de un usuario
		grabar_cliente();
		break;
	case 'formularioCliente': // Devuelve un formulario vacio para un nuevo cliente
		formulario_cliente_vacio();
		break;
	case 'datosCliente': // Muestra los datos de un usuario
		datos_cliente();
		break;
	case 'listaClientes': // Devuelve la lista de usuarios
		lista_clientes();
		break;
	case 'borrarCliente': // Borra el usuario indicado
		borrar_cliente();
		break;
    default: // Por defecto muestra la lista de usuarios
        indice_clientes();
}

/********************************
 * Borra el usuario indicado
 ********************************/
function borrar_cliente() {
	global $modulos, $db;
	
	// Lee el id del usuario a borrar y comprueba que sea correcto
	$id = Modulo::getParam ($_POST, 'id', -1);
	if (is_numeric ($id) && $id > -1) {
		$delete = procesaSql('DELETE FROM #_clientes WHERE id = ?');
		$resultado = $modulos->db->query ($delete, $id);
	} else {
	}
	lista_clientes();
}

/********************************
 * Graba los datos del cliente nuevo o modificado
 ********************************/
function grabar_cliente() {
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
		// Si el id pasado es -1, entonces se crea el cliente, sino se modifica un cliente existente
		if ($id == -1) { // Crear un cliente
			$insert = procesaSql ("INSERT INTO #_clientes (referencia, nombrecomercial, contacto, direccion, localidad, provincia, telefono, email, cif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$resultado = $modulos->db->query($insert, Array($referencia, $nombreComercial, $contacto, $direccion, $localidad, $provincia, $telefono, $email, $cif));
		} else { // Modificar uno existente
			$update = procesaSql("UPDATE #_clientes SET referencia = ?, nombrecomercial = ?, contacto = ?, direccion = ?, localidad = ?, provincia = ?, telefono = ?, email = ?, cif = ? WHERE id = ?");
			$parametros = Array($referencia, $nombreComercial, $contacto, $direccion, $localidad, $provincia, $telefono, $email, $cif, $id);
			$resultado = $modulos->db->query($update, $parametros);
			if (PEAR::isError($resultado)) {
				mostrar_error_sql ($resultado);
			}
		}
	} else {
		$error = 'Parámetro incorrecto';
	}
	lista_clientes();
}

/*********************************
 * Devuelve el texto del formulario con los datos pasados
 *********************************/
function formulario_cliente ($datosCliente = Array()) {
	if (count($datosCliente) == 0) {
		$datosCliente = Array('id'=>-1, 'referencia'=>'', 'nombrecomercial'=>'', 'contacto'=>'', 'direccion'=>'',
								'localidad'=>'', 'provincia'=>'', 'telefono'=>'', 'email'=>'', 'cif'=>'');
	};
	?>
		Referencia: <input type="text" id="referencia" name="referencia" value="<?php echo $datosCliente['referencia']; ?>" /><br />
		Nombre comercial: <input type="text" id="nombrecomercial" name="nombrecomercial" value="<?php echo $datosCliente['nombrecomercial']; ?>" /><br />
		Persona de contacto: <input type="text" id="contacto" name="contacto" value="<?php echo $datosCliente['contacto']; ?>" /><br />
		Direcci&oacute;n: <input type="text" id="direccion" name="direccion" value="<?php echo $datosCliente['direccion']; ?>" /><br />
		Localidad: <input type="text" id="localidad" name="localidad" value="<?php echo $datosCliente['localidad']; ?>" /><br />
		Provincia: <input type="text" id="provincia" name="provincia" value="<?php echo $datosCliente['provincia']; ?>" /><br />
		Tel&eacute;fono: <input type="text" id="telefono" name="telefono" value="<?php echo $datosCliente['telefono']; ?>" /><br />
		E-mail: <input type="text" id="email" name="email" value="<?php echo $datosCliente['email']; ?>" /><br />
		C.I.F.: <input type="text" id="cif" name="cif" value="<?php echo $datosCliente['cif']; ?>" /><br />
		<input type="hidden" id="id" name="id" value="<?php echo $datosCliente['id']; ?>" /><br />
	<?php
}

/********************************
 * Devuelve los datos de un usuario como un formulario
 ********************************/
function datos_cliente() {
	global $modulos, $db;
	
	cabeceraXML();
	$idCliente = Modulo::getParam($_GET, 'id', '');
	if (is_numeric($idCliente)) {
		$select = procesaSql ("SELECT * FROM #_clientes WHERE id = ?");
		$cliente = $modulos->db->query ($select, $idCliente);
		if (PEAR::isError($cliente)) {
			echo "erroR";
		} else {
			$datosCliente = $cliente->fetchRow();
			?>
			<documento>
				<html>
				<![CDATA[
					<?php 
					echo formulario_cliente($datosCliente);
					?>
				]]>
				</html>
				<botones>
					<![CDATA[
						<button type="button" onClick="if (enviar_formulario_cliente('formulario', 'listaClientes')) {vaciar_capas(Array('cuadroDatos', 'botones')); };">Guardar cambios</button>
						<button type="reset">Restaurar formulario</button>
						<button type="button" onClick="borrar_cliente('formulario', 'listaClientes');">Borrar cliente</button>
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
function formulario_cliente_vacio() {
	cabeceraXML();
	?>
	<documento>
		<html>
		<![CDATA[ 
			<?php 
			echo formulario_cliente();
			?>
		]]>
		</html>
		<botones>
			<![CDATA[
				<button type="button" onClick="if (enviar_formulario_cliente('formulario', 'listaClientes')) {vaciar_capas(Array('cuadroDatos', 'botones')); desbloquear_boton('botonCrear');};">Crear clientes</button>
				<button type="button" onClick="vaciar_capas(Array('cuadroDatos', 'botones')); desbloquear_boton('botonCrear');">Cancelar</button>
			]]>
		</botones>
	</documento>
	<?php
}

/*********************************
 * Devuelve una lista de usuarios por XML
 *********************************/
function lista_clientes() {
    global $modulos, $db;
    
    // Lee la lista de usuarios de la bbdd
    $select = procesaSql ("SELECT * FROM #_clientes ORDER BY nombrecomercial, referencia");
    $clientes = $modulos->db->query($select);

    cabeceraXML();
    echo "<clientes>";
    if (PEAR::isError ($clientes)) {
    	echo "<cliente id='-1' referencia='' nombrecomercial='".$clientes->getUserInfo()."' />";
    } else {
        if ($clientes->numRows()) { // Ok, hay clientes, se lee la lista
            while ($cliente = $clientes->fetchRow()) {
            	echo "<cliente ";
            	foreach ($cliente as $clave=>$valor) {
            		echo $clave, "='", $valor, "' ";
            	}
            	echo "/>\r\n";
            }
        } else {
        	echo "<cliente id='-1' referencia='' nombrecomercial='No hay clientes' />";
        }
    }
	echo "</clientes>";
}

/******************************
 * Muestra el índice del módulo de usuarios
 ******************************/
function indice_clientes() {
    ?>
    <form name='formulario' id='formulario' method='post' action=''>
		<div id='lista'>
			Clientes
			<select id='listaClientes' name='listaClientes' onChange='leer_cliente(this, "cuadroDatos", "botones");' size=10>
				<option>Cargando lista...</option>
			</select>

			<button type="button" onClick="crear_nuevo_cliente('cuadroDatos', 'botones', this);" id="botonCrear">Crear nuevo cliente</button>
		</div>
		<div id='datos'>
			Datos
			<div id='cuadroDatos'>
			</div>
			<div id='botones'>
			</div>
		</div>
		<script language="javascript">
			leer_lista_clientes('listaClientes');
		</script>
	</form>
    <?php
}

?>

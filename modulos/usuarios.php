<?php

/****************************
 * Módulo para administrar usuarios
 *
 ******/
 
require ('const_usuarios.php');
  
global $modulos, $db;
 
switch ($task) {
	case 'grabarUsuario': // Graba los datos de un usuario
		grabar_usuario();
		break;
	case 'formularioUsuario': // Devuelve un formulario vacio para un nuevo usuario
		formulario_usuario_vacio();
		break;
	case 'datosUsuario': // Muestra los datos de un usuario
		datos_usuario();
		break;
	case 'listaUsuarios': // Devuelve la lista de usuarios
		lista_usuarios();
		break;
	case 'borrarUsuario': // Borra el usuario indicado
		borrar_usuario();
		break;
    default: // Por defecto muestra la lista de usuarios
        indice_usuarios($_SESSION['tipo']);
}

/******************************
 * Muestra el índice del módulo de usuarios
 ******************************/
function indice_usuarios($tipoUsuario = C_USUARIO) {
    switch ($tipoUsuario) {
        case C_ADMIN:
            indice_usuario_admin();
            break;
        case C_USUARIO:
        default:
            indice_usuario_normal();
            break;
    }
}

/********************************
 * Borra el usuario indicado
 ********************************/
function borrar_usuario() {
	global $modulos, $db;
	
	// Lee el id del usuario a borrar y comprueba que sea correcto
	$id = Modulo::getParam ($_POST, 'id', -1);
	if (is_numeric ($id) && $id > -1) {
		$delete = procesaSql('DELETE FROM #_usuarios WHERE id = ?');
		$resultado = $modulos->db->query ($delete, $id);
		lista_usuarios();
	} else {
	}
}

/********************************
 * Verifica si un usuario existe
 ********************************/
function existe_usuario ($login) {
	global $modulos, $db;

	if (is_string($login)) {
		$select = procesaSql ("SELECT * FROM #_usuarios WHERE login = ?");
		$resultado = $modulos->db->query ($select, $login);
		if (PEAR::isError($resultado)) {
			return false;
		} else {
			return $resultado->numRows();
		}
	} else return false;
}

/********************************
 * Graba los datos del usuario nuevo o modificado
 ********************************/
function grabar_usuario() {
	global $modulos, $db;
	
	// Lee los datos del formulario
	$id = Modulo::getParam($_POST, 'id', -1);
	if ($id == '')
		$id = -1;

	$datosUsuario = Array (
		'login' => strtolower(Modulo::getParam($_POST, 'login', '')),
		'passwd1' => Modulo::getParam($_POST, 'passwd1', ''),
		'passwd2' => Modulo::getParam($_POST, 'passwd2', ''),
		'tipo' => Modulo::getParam($_POST, 'tipo', C_NORMAL),
		'referencia' => Modulo::getParam($_POST, 'referencia', ''),
		'nombrecomercial' => Modulo::getParam($_POST, 'nombrecomercial', ''),
		'contacto' => Modulo::getParam($_POST, 'contacto', ''),
		'direccion' => Modulo::getParam($_POST, 'direccion', ''),
		'localidad' => Modulo::getParam($_POST, 'localidad', ''),
		'provincia' => Modulo::getParam($_POST, 'provincia', ''),
		'telefono' => Modulo::getParam($_POST, 'telefono', ''),
		'email' => Modulo::getParam($_POST, 'email', ''),
		'cif' => Modulo::getParam($_POST, 'cif', ''),
		'descuento' => Modulo::getParam($_POST, 'descuento', 0.0)
	);

	$claves = array_keys($datosUsuario);
	array_splice($claves, 1, 2);
	$claves[] = 'password';

	$error = '';
	$js = '';
	if (is_numeric($id)) { // Ok, el id es un número
		// Primero verifica los parámetros
		if ($datosUsuario['passwd1'] != $datosUsuario['passwd2']) {
			$error = 'Las contraseñas son diferentes';
		} else if ($datosUsuario['login'] == '') {
			$error = 'Escriba el nombre del usuario';
		} else if ($datosUsuario['passwd1'] == '' && $datosUsuario['id'] == -1) {
			$error = 'Escriba una contrase&ntilde;a';
		}
		
		if (!$error) {
			// Modifica el password del usuario
			$datosUsuario['password'] = $datosUsuario['passwd1'];
			unset ($datosUsuario['passwd1'], $datosUsuario['passwd2']);
			
			// Si el id pasado es -1, entonces se crea el usuario, sino se modifica un usuario existente
			if ($id == -1) { // Crear un usuario
				if (!existe_usuario($datosUsuario['login'])) { // Si el usuario no existe, entonces se crea
					$aux = substr('INSERT INTO #_usuarios (' . implode(',', $claves) . ') VALUES (' . str_repeat('?, ', count($claves)), 0, -2) . ')';
					$insert = procesaSql ($aux);
					$resultado = $modulos->db->query($insert, $datosUsuario);
				} else {
					lista_usuarios($datosUsuario);
					return;
				}
			} else { // Modificar uno existente
				if ($datosUsuario['password'] == '') {
					array_pop ($datosUsuario);
					array_pop ($claves);
				}
			
				$update = 'UPDATE #_usuarios SET ' . implode (' = ?,', $claves) . ' = ? ';
				$update .= 'WHERE id = ?';
				array_push ($datosUsuario, $id);
				
				$update = procesaSql ($update);
				$resultado = $modulos->db->query($update, $datosUsuario);
			}
			if (PEAR::isError($resultado)) {
				mostrar_error_sql ($resultado);
			}
		}
	} else {
		$error = 'Parámetro incorrecto';
	}
	if ($_SESSION['tipo'] == C_ADMIN)
		lista_usuarios();
	else datos_usuario();
}

/*********************************
 * Devuelve el texto del formulario con los datos pasados
 *********************************/
function formulario_usuario ($datosUsuario = Array()) {
	if (count($datosUsuario) == 0) {
		$datosUsuario = Array(	'login' => '',
								'nombre' => '',
								'apellidos' => '',
								'direccion' => '',
								'localidad' => '',
								'provincia' => '',
								'tel1' => '',
								'tel2' => '',
								'email' => '',
								'descuento' => 0.0);
		$datosUsuario['tipo'] = C_NORMAL;
		$datosUsuario['id'] = -1;
	};
	?>
		<input type="hidden" name="id" id="id" value="<?php echo $datosUsuario['id']; ?>" />
		Usuario:<input type="text" id="login" name="login" value="<?php echo $datosUsuario['login']; ?>" /><br />
		Password:<input type="password" id="passwd1" name="passwd1" value="" /><br />
		Repita el password:<input type="password" id="passwd2" name="passwd2" value="" /><br />
	<?php // Si el usuario logueado es un administrador, muestra el desplegable, sino no muestra nada
	if ($_SESSION['tipo'] == C_ADMIN) {
		?>
		Tipo de usuario: <select id="tipo" name="tipo">
			<option value=0 <?php echo ($datosUsuario['tipo'] == C_NORMAL) ? 'selected' : ''; ?>>Distribuidor</option>
			<option value=1 <?php echo ($datosUsuario['tipo'] == C_ADMIN) ? 'selected' : ''; ?>>Administrador</option>
		</select><br />
		<?php
	} else {
		echo "";
	}
	?>
		<hr>
		C&oacute;digo: <input type="text" id="referencia" name="referencia" value="<?php echo $datosUsuario['referencia']; ?>" /><br />
		Nombre comercial: <input type="text" id="nombrecomercial" name="nombrecomercial" value="<?php echo $datosUsuario['nombrecomercial']; ?>" /><br />
		Persona de contacto: <input type="text" id="contacto" name="contacto" value="<?php echo $datosUsuario['contacto']; ?>" /><br />
		Direcci&oacute;n: <input type="text" id="direccion" name="direccion" value="<?php echo $datosUsuario['direccion']; ?>" /><br />
		Localidad: <input type="text" id="localidad" name="localidad" value="<?php echo $datosUsuario['localidad']; ?>" /><br />
		Provincia: <input type="text" id="provincia" name="provincia" value="<?php echo $datosUsuario['provincia']; ?>" /><br />
		Tel&eacute;fono: <input type="text" id="telefono" name="telefono" value="<?php echo $datosUsuario['telefono']; ?>" /><br />
		E-mail: <input type="text" id="email" name="email" value="<?php echo $datosUsuario['email']; ?>" /><br />
		C.I.F.: <input type="text" id="cif" name="cif" value="<?php echo $datosUsuario['cif']; ?>" /><br />
		<hr>
		Descuento: 
		<?php
			if ($_SESSION['tipo'] == C_ADMIN) { ?>
			<input type="text" id="descuento" name="descuento" value="<?php echo sprintf ('%.2f', $datosUsuario['descuento']); ?>" />%<br />
			<?php } else {
				echo sprintf ('%.2f %%', $datosUsuario['descuento']);
			}
		?>
	<?php
}

/********************************
 * Devuelve los datos de un usuario como un formulario
 ********************************/
function datos_usuario() {
	global $modulos, $db;
	
	$idUsuario = Modulo::getParam($_GET, 'usuario', '');
	
	// Si no se ha pasado un id de usuario, o si el usuario actual no es un administrador, entonces
	// se leen los datos del usuario actual
	if ($idUsuario == '' || $_SESSION['tipo'] != C_ADMIN) {
		$idUsuario = $_SESSION['idUsuario'];
	}
	if (is_numeric($idUsuario)) { // Se muestran los datos del usuario que se ha solicitado en la URL
		$select = procesaSql ("SELECT * FROM #_usuarios WHERE id = ?");
		$usuario = $modulos->db->query ($select, $idUsuario);
		if (PEAR::isError($usuario)) {
			echo "erroR";
		} else {
			$datosUsuario = $usuario->fetchRow();
		}
	}
	cabeceraXML();
	?>
	<documento>
		<html>
		<![CDATA[
			<?php 
			echo formulario_usuario($datosUsuario);
			?>
		]]>
		</html>
		<botones>
			<![CDATA[
			<?php
			if ($_SESSION['tipo'] == C_ADMIN) {
				?>
				<button type="button" onClick="if (enviar_formulario_usuario('formulario', 'listaUsuarios', 'cuadroDatos', 'botones')) {vaciar_capas(Array('cuadroDatos', 'botones')); };">Guardar cambios</button>
				<button type="reset">Restaurar formulario</button>
				<button type="button" onClick="borrar_usuario('formulario', 'listaUsuarios');">Borrar usuario</button>
				<?php
			} else {
				?>
				<button type="button" onClick="if (enviar_formulario_usuario('formulario', 'listaUsuarios', 'cuadroDatos', 'botones')) {vaciar_capas(Array('cuadroDatos', 'botones')); };">Guardar cambios</button>
				<button type="reset">Restaurar formulario</button>
				<?php
			}
			?>
			]]>
		</botones>
	</documento>
	<?php
}

/*****************************************
 * Devuelve el formulario vacío
 *****************************************/
function formulario_usuario_vacio($datos = false) {
	if (is_array($datos)) { // Se muestran los datos que se han pasado
		$datosUsuario = &$datos;
	} else {
		cabeceraXML();
		$datosUsuario = false;
	}
	?>
	<documento>
		<html>
		<![CDATA[ 
			<?php 
			if (is_array($datos)) {
				echo "<div class='error'>El usuario ya existe. Seleccione otro nombre para el usuario.</div>";
			}
			echo formulario_usuario($datosUsuario);
			?>
		]]>
		</html>
		<botones>
			<![CDATA[
				<button type="button" onClick="if (enviar_formulario_usuario('formulario', 'listaUsuarios', 'cuadroDatos', 'botones')) {vaciar_capas(Array('cuadroDatos', 'botones')); desbloquear_boton('botonCrear');};">Crear usuario</button>
				<button type="button" onClick="vaciar_capas(Array('cuadroDatos', 'botones')); desbloquear_boton('botonCrear');">Cancelar</button>
			]]>
		</botones>
	</documento>
	<?php
}

/*********************************
 * Devuelve una lista de usuarios por XML
 *********************************/
function lista_usuarios($datosUsuario = false) {
    global $modulos, $db;
    
    // Lee la lista de usuarios de la bbdd
    $select = procesaSql ("SELECT * FROM #_usuarios ORDER BY login");
    $usuarios = $modulos->db->query($select);
    if (PEAR::isError ($usuarios)) {
    } else {
        $listaUsuarios = Array();
        if ($usuarios->numRows()) { // Ok, hay usuarios, se lee la lista
            while ($usuario = $usuarios->fetchRow()) {
                $listaUsuarios[] = $usuario;
            }
        }
    }
    cabeceraXML();
?>
<usuarios>
	<?php
    foreach ($listaUsuarios as $usuario) {        
		?>
			<usuario id="<?php echo $usuario['id']; ?>" login="<?php echo $usuario['login']; ?>" nombre="<?php echo $usuario['nombre']; ?>" apellido="<?php echo $usuario['apellidos']; ?>" />
		<?php
    }
	if (is_array($datosUsuario)) {
		formulario_usuario_vacio($datosUsuario);
	}
?>
</usuarios>

  <?php
}

/*********************************
 * Muestra la página de administrador de usuarios
 *********************************/
function indice_usuario_admin() {
    ?>
    <form name='formulario' id='formulario' method='post' action=''>
		<div id='lista'>
			Usuarios
			<select id='listaUsuarios' name='listaUsuarios' onChange='leer_usuario(this, "cuadroDatos", "botones");' size=10>
				<option>Cargando lista...</option>
			</select>

			<button type="button" onClick="crear_nuevo_usuario('cuadroDatos', 'botones', this);" id="botonCrear">Crear nuevo usuario</button>
		</div>
		<div id='datos'>
			Datos
			<div id='cuadroDatos'>
			</div>
			<div id='botones'>
			</div>
		</div>
		<script language="javascript">
			leer_lista_usuarios('listaUsuarios');
		</script>
	</form>
    <?php
}

/***********************************
 * Muestra los datos del usuario logueado
 ***********************************/
function indice_usuario_normal() {
    ?>
    <form name='formulario' id='formulario' method='post' action=''>
		<div id='datosUsuario'>
			Datos
			<div id='cuadroDatos'>
			</div>
			<div id='botones'>
			</div>
		</div>
		<script language="javascript">
			leer_usuario_actual('cuadroDatos', 'botones');
		</script>
	</form>
    <?php
}

?>

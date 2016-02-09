<?php

/********************************************
 * login.php - Módulo de login
 *		Login al sistema
 ********************************************/
	
	// Realiza la tarea indicada
	switch ($task) {
		case 'logout': // Termina la sesion
			logout();
			break;
			
		case 'login': // Valida el usuario y contraseña pasados
			login_usuario();
			break;

		default: // Por defecto se muestra el formulario de pedido
			muestra_formulario_login();
			break;
	}

/*********************************
 * Valida si los datos pasados por el formulario de login son válidos.
 * Si lo son, entonces se crea la sesion.
 *********************************/
function login_usuario () {
	global $modulos, $defaultTimeout;

	$usuario = Modulo::getParam($_POST, 'usuario', '');
	$password = strtoupper(Modulo::getParam ($_POST, 'password', ''));
 
	if ($usuario != '' && $password != '') {
		$select = procesaSql("SELECT * FROM #_usuarios WHERE lower(login) = lower(?)");
		$usuarios = $modulos->db->query ($select, $usuario);
		if (!PEAR::isError($usuarios)) {
			if ($usuarios->numRows()) {
				$datosUsuario = $usuarios->fetchRow();
				if (strtoupper($datosUsuario['password']) != $password) {
					muestra_formulario_login ('', "Escriba un usuario y contrase&ntilde;a correctos.");
				} else {
					$_SESSION['idUsuario'] = $datosUsuario['id'];
					$_SESSION['usuario'] = $usuario;
					$_SESSION['datosUsuario'] = $datosUsuario;
					if (strlen($datosUsuario['timeout']) == 0)
						$_SESSION['timeout'] = $defaultTimeout;
					else $_SESSION['timeout'] = $datosUsuario['timeout'];
					$_SESSION['ultima'] = time();
					$_SESSION['tipo'] = $datosUsuario['tipo'];

					// Se fuerza a que se ejecute el módulo que se quería usar
					if (isset($_POST['parametros_get']))
						$pGet = &$_POST['parametros_get'];
					else $pGet = Array();
					if (isset($_POST['parametros_post']))
						$pPost = &$_POST['parametros_post'];
					else $pPost = Array();
					$modulos->repite_accion($pGet, $pPost);
				}
			} else
				muestra_formulario_login ($usuario, "Escriba un usuario y contrase&ntilde;a correctos.");
		} else {
			echo "Error $select";
		}
	} else {
		muestra_formulario_login ($usuario, "Escriba el usuario y la contrase&ntilde;a, por favor.");
	}
} // login_usuario

/**********************************
 * Muestra el formulario de login
 * @param: $usuario: nombre del usuario a mostrar
 **********************************/
function muestra_formulario_login ($usuario = '', $error = '') {
	global $domain;
	?>
		<div id="cuadro_login">
			<div>Panel de control de <B><?php echo $domain; ?></B></div>
			<div>
				<form action="" method="POST" id="form_login" name="form_login">
					<table>
						<?php
							if ($error) {
							?>
								<tr>
									<TD class="error" colspan="2"><?php echo $error; ?></TD>
								</tr>
							<?php
							}
						?>
						<TR>
							<TD class="defCampo">Usuario:</TD>
							<td style="width:300px;"><input type="text" name="usuario" id="usuario" value="<?php echo $usuario; ?>"></td>
						</TR>
						<tr>
							<TD class="defCampo">Contrase&ntilde;a:</TD>
							<td><input type="password" name="password" id="password"></td>
						</tr>
						<tr>
							<TD colspan="2"><input type="submit" id="login" class="boton" value="Entrar"></TD>
						</tr>
					</table>
					<input type="hidden" name="task" value="login">
					<!--input type="hidden" name="parametros_get" value="<?php echo htmlentities(serialize($_GET)); ?>">
					<input type="hidden" name="parametros_post" value="<?php echo htmlentities(serialize($_POST)); ?>"-->
				</form>
			</div>
		</div>
		<script type="text/javascript" language="JavaScript">document.form_login.usuario.focus();</script>
	<?php
} // muestra_formulario_login

/***********************************
 * logout()
 * Cierra la sesión
 ***********************************/
function logout() {
	global $url;
//Modulo::mostrar( $_SERVER);
	session_destroy();
	?>
		<script language="JavaScript" type="text/javascript">
			document.location="http://<?php echo $url ?>";
		</script>
	<?php
} // logout

?>

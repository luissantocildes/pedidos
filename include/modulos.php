<?php

/***********************************************
 * Clase base del "Framework" desarrollado para simplificar un poco el desarrollo de los sitios web.
 *
 * La función principal es la carga de unos módulos u otros en función de los parámetros pasados
 * al cargar la página. Por defecto los valores se cogen de los parámetros pasados por $_POST, sino de $_GET.
 * Si no se pasa ningún valor se carga un módulo por defecto indicado en $inicio.
 *
 * Parámetros de la clase:
 * 	opt: nombre del módulo a cargar. Los posibles módulos a cargar están prefijados en la
 * 		propiedad $opciones.
 * 	task: tarea o función que hará el módulo cargado.
 *
 * Para utilizar el "framework" se debe de crear un objeto de la clase Modulo, normalmente en el archivo
 * principal del sitio. Posteriormemte, desde el sitio donde se quiere mostrar el contenido se llamará el
 * método PRINCIPAL del objeto.
 *
 * La estructura básica de los módulos está formada por un código principal que llama a diferentes funciones en
 * base al valor de la variable $task. El módulo debe cargar las variables globales $modulo para acceder a los
 * métodos y/o atributos que necesite para su funcionamiento (p. ej. acceso a la base de datos).
 * Una mejora evidente es que los módulos sean objetos que hereden de un tipo base, para que la clase Modulo
 * simplemente llame al método de ejecución del objeto pasándole los parámetros.
 ***********************************************/

include ('bbdd.php');

define( "_MOS_NOTRIM", 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
define( "_MOS_ALLOWRAW", 0x0004 );

class Modulo {
	var $opt;
	// Lista de módulos del sitio web.
	var $opciones = Array ('login' => 'login.php',
				'indice'=>'indice.php',
				'usuarios'=>'usuarios.php',
				'pedidos'=>'pedidos.php');

	// Módulo por defecto si no el módulo indicado no está en la lista de módulos.
	var $inicio = 'indice';
	
	var $db;
	var $recargar = '';
	
	var $dirPath = '';

	var $parametros;	// Vble. auxiliar para almacenar los parámetros pasados

	/******************
	 * Constructor
	 * Coge el parámetro opt, verifica la sesion, e inicializa la conexión con la base de datos
	 ******************/
	function Modulo ($dirPath = '') {
		if ($this->db = conexion_db()) {
			// Se carga la sesión. Si no hay sesión se carga el módulo de login, sino se carga
			// el módulo especificado en el parámetro opt.
			if ($this->inicializa_sesion()) {
				if (isset ($_GET['opt']))
					$this->opt = $this->getParam($_GET, 'opt', 'noticias');
				else $this->opt = $this->getParam($_POST, 'opt', 'noticias');
			} else {
				//$this->parametros = Array('GET'=>$_GET, 'POST'=>$_POST);
				$this->opt = 'login';
			}
			$this->opt = $this->opt; // ????????? 
			$this->dirPath = $dirPath;
		} else {
			die ('Error al conectar a la base de datos<br>');
		}
	} // Modulo

	/********************
	 * Método auxiliar para depuración.
	 * Imprime la cadena $cosa entre etiquetas PRE para que visualizarla correctamente.
	 ********************/
	function mostrar ($cosa) {
		echo "<pre>";
		print_r ($cosa);
		echo "</pre>";
	}

	/**************************
	 * Función principal del objeto.
	 * Su función es comprobar que el módulo especificado existe, y en ese caso cargarlo.
	 **************************/
	function principal () {
		global $dirModulos;

		if ($this->db) {

			require_once ($this->dirPath . '/' . $dirModulos . '/const_usuarios.php');
			
			/* Si el usuario es administrador se añade el módulo campos a la lista de
			 * módulos permitidos.
			 * NOTA: Esto sería más genérico si se indicaran en la lista de módulos
			 * los permisos necesarios para poder acceder a los módulos.
			 */
			if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == C_ADMIN)
				$this->opciones['campos'] = 'campos.php';

			// Obtiene la tarea a realizar por el módulo y carga
			$task = Modulo::getParam($_POST, 'task', '');
			if ($task == '')
				$task = Modulo::getParam($_GET, 'task', $this->inicio);
			if (isset($this->opciones[$this->opt])) {
				include ($this->dirPath . "/" . $dirModulos . "/" . $this->opciones[$this->opt]);
			} else {
				include ($this->dirPath . "/" . $dirModulos . "/" . $this->opciones[$this->inicio]);
			}
		}
	} // principal
	
	/************************
	 * terminado()
	 *	Ejecuta los procesos necesarios para cerrar los modulos. Funciona a modo de destructor.
	 *	No es necesario llamarlo en la implementación actual ya que lo único que hace es cerrar
	 *	la conexión con la base de datos.
	 ************************/
	function terminado () {
		if ($this->db)
			desconexion_db ($this->db);
	} // terminado

	/************************
	 * getParam (Array, String, , )
	 * Coge un valor de un array y lo devuelve, si no está definido devuelve el valor $def
	 * Parámetros:
	 * 	$arr: Array en el que buscar el parámetro
	 * 	$name: Nombre del parámetro a buscar
	 * 	$def: Valor por defecto a devolver si no existe el valor
	 * 	$mask: Máscara que determina si hay que preprocesar el resultado. De momento solo acepta el valor
	 * 		_MOS_NOTRIM que ejecuta la función trim sobre el resultado.
	 ************************/
	function getParam( &$arr, $name, $def=null, $mask=0 ) {
	
		$return = null;
		if (isset( $arr[$name] )) {
			$return = $arr[$name];
			
			if (is_string( $return )) {
				// trim data
				if (!($mask&_MOS_NOTRIM)) {
					$return = trim( $return );
				}
			}
	
			return $return;
		} else {
			return $def;
		}
	} // getParam
	
	/**************************
	 * inicializa_sesion()
	 * Carga los datos de la sesión del usuario.
	 * @return: Devuelve true si la sesion es correcta. Si el usuario no está logueado o la sesión ha
	 * caducado, entonces devuelve false
	 **************************/
	function inicializa_sesion() {
		session_start();
		// Primero se verifica que el usuario esté logueado y que este exista en la base de datos
		if (isset($_SESSION['usuario'])) {
			$select = procesaSql("SELECT * FROM #_usuarios WHERE login = '{$_SESSION['usuario']}'");
			$usuario = $this->db->query ($select);
			if (!PEAR::isError($usuario)) { // Ok, consulta correcta, verifica que exista el usuario
				if ($usuario->fetchRow()) { // el usuario existe, a ver que no se haya pasado de su timeout
					if (time() <= $_SESSION['ultima'] + $_SESSION['timeout']) { // Correcto, actualiza la hora de la ultima sesión
						$_SESSION['ultima'] = time() + $_SESSION['timeout'];
						return true;
						exit(0);
					}
				}
			}
		}

		// En el caso de que no se cumpla ninguna de las condiciones del if se elimina la sesión
		session_destroy();
		session_start();
		return false;
	} // inicializa_sesion

	/******************************
	 * repite_accion ()
	 * vuelve a ejecutar la acción pasada en los parámetros $get y $post.
	 * Esta es una solución poco elegante al problema de volver a cargar una página determinada si ha
	 * caducado la sesión del usuario
	 * Parámetros:
	 * 	$get: Parámetros GET
	 * 	$post: Parámetros POST
	 ******************************/
	function repite_accion($get, $post) {
		if (count ($get))
			$_GET = unserialize(stripslashes($get));
		if (count ($post))
			$_POST = unserialize(stripslashes($post));

		$this->opt = $this->getParam($_GET, 'opt', 'indice');
		$this->principal();
	} // repite_accion

}

?>

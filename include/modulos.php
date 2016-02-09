<?php

/***********************************************
 * Clase base del "Framework" desarrollado para simplificar un poco el desarrollo de los sitios web.
 *
 * La funci�n principal es la carga de unos m�dulos u otros en funci�n de los par�metros pasados
 * al cargar la p�gina. Por defecto los valores se cogen de los par�metros pasados por $_POST, sino de $_GET.
 * Si no se pasa ning�n valor se carga un m�dulo por defecto indicado en $inicio.
 *
 * Par�metros de la clase:
 * 	opt: nombre del m�dulo a cargar. Los posibles m�dulos a cargar est�n prefijados en la
 * 		propiedad $opciones.
 * 	task: tarea o funci�n que har� el m�dulo cargado.
 *
 * Para utilizar el "framework" se debe de crear un objeto de la clase Modulo, normalmente en el archivo
 * principal del sitio. Posteriormemte, desde el sitio donde se quiere mostrar el contenido se llamar� el
 * m�todo PRINCIPAL del objeto.
 *
 * La estructura b�sica de los m�dulos est� formada por un c�digo principal que llama a diferentes funciones en
 * base al valor de la variable $task. El m�dulo debe cargar las variables globales $modulo para acceder a los
 * m�todos y/o atributos que necesite para su funcionamiento (p. ej. acceso a la base de datos).
 * Una mejora evidente es que los m�dulos sean objetos que hereden de un tipo base, para que la clase Modulo
 * simplemente llame al m�todo de ejecuci�n del objeto pas�ndole los par�metros.
 ***********************************************/

include ('bbdd.php');

define( "_MOS_NOTRIM", 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
define( "_MOS_ALLOWRAW", 0x0004 );

class Modulo {
	var $opt;
	// Lista de m�dulos del sitio web.
	var $opciones = Array ('login' => 'login.php',
				'indice'=>'indice.php',
				'usuarios'=>'usuarios.php',
				'pedidos'=>'pedidos.php');

	// M�dulo por defecto si no el m�dulo indicado no est� en la lista de m�dulos.
	var $inicio = 'indice';
	
	var $db;
	var $recargar = '';
	
	var $dirPath = '';

	var $parametros;	// Vble. auxiliar para almacenar los par�metros pasados

	/******************
	 * Constructor
	 * Coge el par�metro opt, verifica la sesion, e inicializa la conexi�n con la base de datos
	 ******************/
	function Modulo ($dirPath = '') {
		if ($this->db = conexion_db()) {
			// Se carga la sesi�n. Si no hay sesi�n se carga el m�dulo de login, sino se carga
			// el m�dulo especificado en el par�metro opt.
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
	 * M�todo auxiliar para depuraci�n.
	 * Imprime la cadena $cosa entre etiquetas PRE para que visualizarla correctamente.
	 ********************/
	function mostrar ($cosa) {
		echo "<pre>";
		print_r ($cosa);
		echo "</pre>";
	}

	/**************************
	 * Funci�n principal del objeto.
	 * Su funci�n es comprobar que el m�dulo especificado existe, y en ese caso cargarlo.
	 **************************/
	function principal () {
		global $dirModulos;

		if ($this->db) {

			require_once ($this->dirPath . '/' . $dirModulos . '/const_usuarios.php');
			
			/* Si el usuario es administrador se a�ade el m�dulo campos a la lista de
			 * m�dulos permitidos.
			 * NOTA: Esto ser�a m�s gen�rico si se indicaran en la lista de m�dulos
			 * los permisos necesarios para poder acceder a los m�dulos.
			 */
			if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == C_ADMIN)
				$this->opciones['campos'] = 'campos.php';

			// Obtiene la tarea a realizar por el m�dulo y carga
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
	 *	No es necesario llamarlo en la implementaci�n actual ya que lo �nico que hace es cerrar
	 *	la conexi�n con la base de datos.
	 ************************/
	function terminado () {
		if ($this->db)
			desconexion_db ($this->db);
	} // terminado

	/************************
	 * getParam (Array, String, , )
	 * Coge un valor de un array y lo devuelve, si no est� definido devuelve el valor $def
	 * Par�metros:
	 * 	$arr: Array en el que buscar el par�metro
	 * 	$name: Nombre del par�metro a buscar
	 * 	$def: Valor por defecto a devolver si no existe el valor
	 * 	$mask: M�scara que determina si hay que preprocesar el resultado. De momento solo acepta el valor
	 * 		_MOS_NOTRIM que ejecuta la funci�n trim sobre el resultado.
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
	 * Carga los datos de la sesi�n del usuario.
	 * @return: Devuelve true si la sesion es correcta. Si el usuario no est� logueado o la sesi�n ha
	 * caducado, entonces devuelve false
	 **************************/
	function inicializa_sesion() {
		session_start();
		// Primero se verifica que el usuario est� logueado y que este exista en la base de datos
		if (isset($_SESSION['usuario'])) {
			$select = procesaSql("SELECT * FROM #_usuarios WHERE login = '{$_SESSION['usuario']}'");
			$usuario = $this->db->query ($select);
			if (!PEAR::isError($usuario)) { // Ok, consulta correcta, verifica que exista el usuario
				if ($usuario->fetchRow()) { // el usuario existe, a ver que no se haya pasado de su timeout
					if (time() <= $_SESSION['ultima'] + $_SESSION['timeout']) { // Correcto, actualiza la hora de la ultima sesi�n
						$_SESSION['ultima'] = time() + $_SESSION['timeout'];
						return true;
						exit(0);
					}
				}
			}
		}

		// En el caso de que no se cumpla ninguna de las condiciones del if se elimina la sesi�n
		session_destroy();
		session_start();
		return false;
	} // inicializa_sesion

	/******************************
	 * repite_accion ()
	 * vuelve a ejecutar la acci�n pasada en los par�metros $get y $post.
	 * Esta es una soluci�n poco elegante al problema de volver a cargar una p�gina determinada si ha
	 * caducado la sesi�n del usuario
	 * Par�metros:
	 * 	$get: Par�metros GET
	 * 	$post: Par�metros POST
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

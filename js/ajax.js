// 
// Ajax.js
// Colección de funciones Javascript para la conexión via Ajax y para interactuar con el frontend.
//
// Se deberían separar las funciones en diferentes archivos según la funcionalidad
//

// variable que contrendrá los resultados de la consulta Ajax
var documentoXML = '';

// variable que contendrá al objeto XMLHttpRequest
var ObjetoXMLHttpRequest = false;

// Almacena las im�genes que ser�n le�das desde el XML
var imagenes;

// m�ximo de im�genes peque�as a mostrar
var maxImagenesMostrar = 5;

// N�mero de p�ginas a mostrar, est�n divididas en este caso de 10 en 10
var pagina = 0;

// Variable de control, indica el m�ximo de paginas
var totalPaginas = 0;

// Imagen desde la que se comenzara a cargar las im�genes cuando se cargue la p�gina por primera vez
var comenzar = 0;

// Modo de depuraci�n. depuracion != 0
var depuracion;
depuracion = 0;

// URL del servidor desde el que se cargaran las im�genes
var URLservidor;
if (document.URL.split('/')[2] == 'localhost') 
	URLservidor = 'http://'+document.URL.split('/')[2]+'/segurestil/pedidos/';
else
	URLservidor = 'http://'+document.URL.split('/')[2]+'/pedidos/';

// archivo XML, contiene las propiedades de las im�genes
var archivoXML = 'index2.php?opt=ajax&task=listaImagenes';
var archivoXML2 = 'index2.php?opt=ajax&task=listaImagenes2';

var parametrosPeticion = '';

// Consultas AJAX más utilizadas
var listaArchivoXML = new Array();
listaArchivoXML['listaUsuarios'] = Array('GET', 'index2.php?opt=usuarios&task=listaUsuarios');
listaArchivoXML['leerDatosUsuario'] = Array('GET', 'index2.php?opt=usuarios&task=datosUsuario');
listaArchivoXML['formularioUsuarioVacio'] = Array('GET', 'index2.php?opt=usuarios&task=formularioUsuario');
listaArchivoXML['grabarUsuario'] = Array('POST', 'index2.php?opt=usuarios&task=grabarUsuario');
listaArchivoXML['borrarUsuario'] = Array('POST', 'index2.php?opt=usuarios&task=borrarUsuario');
listaArchivoXML['listaTipos'] = Array('GET', 'index2.php?opt=campos&task=listaTipos');
listaArchivoXML['listaAtributos'] = Array('GET', 'index2.php?opt=campos&task=listaAtributos');
listaArchivoXML['leerAtributo'] = Array('GET', 'index2.php?opt=campos&task=leerAtributo');
listaArchivoXML['formularioAtributoVacio'] = Array('GET', 'index2.php?opt=campos&task=formularioAtributoVacio');
listaArchivoXML['grabarAtributo'] = Array('POST', 'index2.php?opt=campos&task=grabarAtributo');
listaArchivoXML['borrarAtributo'] = Array('POST', 'index2.php?opt=campos&task=borrarAtributo');
listaArchivoXML['listaClientes'] = Array('GET', 'index2.php?opt=clientes&task=listaClientes');
listaArchivoXML['leerCliente'] = Array('GET', 'index2.php?opt=clientes&task=datosCliente');
listaArchivoXML['formularioCliente'] = Array('GET', 'index2.php?opt=clientes&task=formularioCliente');
listaArchivoXML['grabarCliente'] = Array('POST', 'index2.php?opt=clientes&task=grabarCliente');
listaArchivoXML['borrarCliente'] = Array('POST', 'index2.php?opt=clientes&task=borrarCliente');
listaArchivoXML['listaDistribuidores'] = Array('GET', 'index2.php?opt=distribuidores&task=listaDistribuidores');
listaArchivoXML['leerDistribuidor'] = Array('GET', 'index2.php?opt=distribuidores&task=datosDistribuidor');
listaArchivoXML['formularioDistribuidor'] = Array('GET', 'index2.php?opt=distribuidores&task=formularioDistribuidor');
listaArchivoXML['grabarDistribuidor'] = Array('POST', 'index2.php?opt=distribuidores&task=grabarDistribuidor');
listaArchivoXML['borrarDistribuidor'] = Array('POST', 'index2.php?opt=distribuidores&task=borrarDistribuidor');
listaArchivoXML['listadoPedidos'] = Array('GET', 'index2.php?opt=pedidos&task=listadoPedidos');
listaArchivoXML['nuevoPedido'] = Array('GET', 'index2.php?opt=pedidos&task=nuevoPedido');
listaArchivoXML['guardarPedido'] = Array('POST', 'index2.php?opt=pedidos&task=guardarPedido');
listaArchivoXML['leerPedido'] = Array('GET', 'index2.php?opt=pedidos&task=leerPedido');
listaArchivoXML['borrarPedido'] = Array('POST', 'index2.php?opt=pedidos&task=borrarPedido');

// imagen por defecto a mostrarse cuando no se carga ninguna imagen del servidor
var imagenVacio = 'vacio.gif';

// Variables globales
var funcion = '';	// Función callback para AJAX
var idCapa = '';	// sin uso
var IE = false;		// Flag para determinar si el navegador es IE

// Creaci�n del ObjetoXMLHttpRequest. De paso determina el navegador
try{
   ObjetoXMLHttpRequest = new ActiveXObject("MSXML2.XMLHTTP");
   IE = true;
}catch(exception1){
   try{
      ObjetoXMLHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
	   IE = true;
   }catch(exception2){
      ObjetoXMLHttpRequest = false;
   }
}
if(!ObjetoXMLHttpRequest && window.XMLHttpRequest){
   ObjetoXMLHttpRequest = new XMLHttpRequest();
}

/**********************
 * Determina si una variable está definida.
 **********************/
function isDefined(variable) {
    return (typeof(window[variable]) == "undefined")?  false: true;
	//return (typeof(variable) == "undefined")?  false: true;
}


// funci�n para calcular el n�mero de p�ginas
function calcularpaginas(totalImagenes){
   totalPaginas = Math.ceil(totalImagenes/maxImagenesMostrar);
}

// Selecciona el elemento "valor" en el desplegable "idDesplegable"
function seleccionaElemento (idDesplegable, valor) {
	var desplegable = document.getElementById (idDesplegable);
	if (valor != '') {
		for (c = 0; c < desplegable.length; c++)
			if (desplegable.options[c].value == valor) {
				desplegable.selectedIndex = c;
				break;
			}
	}
}

/*******************************************************************************************
 * FUNCIONES AUXILIARES
 *******************************************************************************************/

/**************************************
 * Limpia el html de las capas indicadas.
 * listaCapas: Lista de las capas a vaciar. Puede ser una cadena con el nombre
 *  	de la capa, o un array de cadenas.
 **************************************/
function vaciar_capas (listaCapas) {
	if (is_string(listaCapas)) {
		var capa = document.getElementById(listaCapas);
		capa.innerHTML = '';
	} else if (listaCapas instanceof Array) {
		for (c = 0; c < listaCapas.length; c++) {
			var capa = document.getElementById(listaCapas[c]);
			capa.innerHTML = '';
		}
	}
}

/**************************************
 * Desabilita el boton indicado.
 * Se le puede pasar el identificador del boton o el propio objeto
 **************************************/
function bloquear_boton(boton) {
	if (is_string(boton)) {
		var aux = document.getElementById(boton);
		aux.disabled = true;
	} else
		boton.disabled = true;
}

/**************************************
 * Habilita el boton indicado
 * Se le puede pasar el identificador del boton o el propio objeto
 **************************************/
function desbloquear_boton(boton) {
	if (is_string(boton)) {
		var aux = document.getElementById(boton);
		aux.disabled = false;
	} else
		boton.disabled = false;
}

/***************************************
 * Comprueba si el parámetro es una cadena
 ***************************************/
function is_string(parametro) {
	return (typeof(parametro) == 'string');
}

/****************************************
 * Busca una etiqueta dentro del documento XML
 ****************************************/
function busca_etiquetas (nombreEtiqueta) {
	return documentoXML.getElementsByTagName(nombreEtiqueta);
}

/****************************************
 * Vacia una lista select.
 * Se le puede pasar el identificador de la lista o el propio objeto
 ****************************************/
function vaciar_lista (lista) {
	var listaAVaciar;
	if (is_string (lista)) {
		listaAVaciar = document.getElementById(lista);
	} else
		listaAVaciar = lista;
		
	if (listaAVaciar)
		while (listaAVaciar.firstChild) {
			listaAVaciar.removeChild(listaAVaciar.firstChild);
		}
}

/***************************************
 * Oculta un elemento cambiando la propiedad visibility
 *
 * Esta función y la siguiente se podrían haber agrupado en una sola
 ***************************************/
function oculta_elemento (idElemento) {
	//document.getElementById(idElemento).style.visibility="hidden";
	var elemento = document.getElementById(idElemento);
	
	//if (isDefined(elemento)) {
		elemento.style.visibility = 'hidden';
	//}
}

/***************************************
 * Muestra un elemento cambiando la propiedad visibility
 ***************************************/
function muestra_elemento (idElemento) {
	var elemento = document.getElementById(idElemento);
	//document.getElementById(idElemento).style.visibility="visible";
	//if (isDefined(elemento)) {
		elemento.style.visibility = 'visible';
	//}
}

/************************************
 * recalcula el total del importe para las características de las puertas seleccionadas.
 ************************************/
function cambia_importe (desplegable, idCeldaCaracteristica, nombreCeldaImporte, nombreCeldaDescuento, nombreCeldaImporteTotal, listaImportes, total) {
	var celdaCaracteristica = document.getElementById('celda_caracteristica_'+idCeldaCaracteristica);
	var importeCaracteristica = document.getElementById('importe_caracteristica_'+idCeldaCaracteristica);
	var celdaImporte = document.getElementById(nombreCeldaImporte);
	var celdaDescuento = document.getElementById(nombreCeldaDescuento);
	var celdaImporteFinal = document.getElementById(nombreCeldaImporteTotal);
	var descuentoTotal = 0;
	var importeFinal = 0;
	
	// Busca el importe y lo cambia
	var caracteristica = desplegable.options[desplegable.selectedIndex].value;
	if (caracteristica != -1) {
		var c = 0;
		while (c < listaImportes.length && listaImportes[c][0] != caracteristica)
			c++;
		if (c < listaImportes.length) {
			celdaCaracteristica.innerHTML = listaImportes[c][1] + " &euro;";
			total = total - parseFloat(importeCaracteristica.value) + parseFloat(listaImportes[c][1]);
			importeCaracteristica.value = listaImportes[c][1];
			celdaImporte.innerHTML = total.toFixed(2) + " &euro;";
			descuentoTotal = total * (descuento/100);
			celdaDescuento.innerHTML = "-" + descuentoTotal.toFixed(2) + " &euro;";
			importeFinal = total - descuentoTotal;
			celdaImporteFinal.innerHTML = importeFinal.toFixed(2) + " &euro;";
		}
	}
	return total;
}

/****************************************************************************/

/*****************************************************************************
 * FUNCIONES AJAX                                                            *
 * La implementación utilizada es muy sencilla, y no permite realizar varias *
 * llamadas en paralelo. Solo se puede hacer una llamada a la vez. Usando    *
 * funciones anónimas se podría haber permitido paralelismo de una forma     *
 * sencilla, aunque no hacía falta para los requisitos del sitio. Se optó por*
 * bloquear la interación del usuario con una capa que se muestra cuando se  *
 * realiza la petición y se oculta cuando se reciben los datos del servidor. *
 *****************************************************************************/
var elementoHTML;
var campoBotones;

/**************************************
 * Hace una solicitud al servidor.
 * Parámetros:
 *		operacion: cadena con el nombre de la operacion a realizar. Debe de ser uno de los índices de listaArchivoXML
 *		elemento: Elemento sobre el que se actuará al obtener el resultado de la consulta.
 *		botones: Botones sobre los que se actuará al obtener el resultado de la consulta.
 *		funcionALlamar: Función de callback.
 **************************************/
function cargarXML(operacion, elemento, botones, funcionALlamar) {
	if(depuracion){
		alert('cargarXML()');
	}
	
	muestra_elemento('capaPausa');
	
	elementoHTML = elemento;
	campoBotones = botones;
	
	if(ObjetoXMLHttpRequest){
		// Genera la cadena de petición, tomando en cuenta si es de tipo GET o POST.
		peticion = URLservidor + listaArchivoXML[operacion][1];
		if (parametrosPeticion != '' && listaArchivoXML[operacion][0] == 'GET') {
			peticion = peticion + '&' + parametrosPeticion;
		}
		ObjetoXMLHttpRequest.open(listaArchivoXML[operacion][0], peticion, true);
		if (parametrosPeticion != '' && listaArchivoXML[operacion][0] == 'POST') {
			ObjetoXMLHttpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			ObjetoXMLHttpRequest.send(parametrosPeticion);
		}
	}
	
	funcion = funcionALlamar;
	
	ObjetoXMLHttpRequest.onreadystatechange = procesa_resultados;
	if (listaArchivoXML[operacion][0] == 'GET') {
		ObjetoXMLHttpRequest.send(null);
	}
	return false;
}

/************************************
 * Recibe los resultados de la petición, los guarda y llama a la función indicada
 ************************************/
function procesa_resultados() {
	if (ObjetoXMLHttpRequest.readyState == 4 && ObjetoXMLHttpRequest.status == 200){
		documentoXML = ObjetoXMLHttpRequest.responseXML;
		funcion();
		oculta_elemento('capaPausa');
	}
}

/********************************** USUARIOS ************************************/
/*************************************
 * Muestra la lista de los usuarios.
 *************************************/
function escribir_lista_usuarios () {
	var usuarios = busca_etiquetas('usuario');
	var datos = busca_etiquetas('html');

	// Borra la lista actual de usuarios
	vaciar_lista (elementoHTML);
	
	// Ok, la rellena con los datos obtenidos
	var c = 0;
	while (c < usuarios.length) {
		var elemento = document.createElement('option');
		elemento.value = usuarios[c].attributes.getNamedItem('id').nodeValue;
		elemento.text = usuarios[c].attributes.getNamedItem('login').nodeValue;

		try {
			elementoHTML.options.add(elemento);
		} catch(e) {
			alert ('error');
		}
		c++;
	}
	
	// Ahora muestra los datos del usuario seleccionado, si se ha obtenido alguno.
	if (datos.length) {
		elementoHTML = document.getElementById('cuadroDatos');
		campoBotones = document.getElementById('botones');
		ver_usuario();
	}
}

/***************************************
 * Lee la lista de los usuarios y los muestra.
 ***************************************/
function leer_lista_usuarios (nombreElemento) {
	var desplegable = document.getElementById(nombreElemento);
	parametrosPeticion='';
	cargarXML ('listaUsuarios', desplegable, null, escribir_lista_usuarios);
}

/**************************************
 * Solicita los datos de un usuario y los muestra
 **************************************/
function leer_usuario (select, nombreCapa, nombreCapaBotones) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	parametrosPeticion = 'usuario='+select.options[select.selectedIndex].value;
	cargarXML ('leerDatosUsuario', capa, botones, ver_usuario);	
}

/**************************************
 * Solicita los datos de un usuario y los muestra
 **************************************/
function leer_usuario_actual (nombreCapa, nombreCapaBotones) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	parametrosPeticion = '';
	cargarXML ('leerDatosUsuario', capa, botones, ver_usuario);	
}

/**************************************
 * Coge los datos del usuario y los muestra
 **************************************/
function ver_usuario() {
	var resultado = busca_etiquetas('html');
	resultado = IE ? resultado[0].text : resultado[0].textContent;
	var resultadoBotones = busca_etiquetas('botones');
	resultadoBotones = IE ? resultadoBotones[0].text : resultadoBotones[0].textContent;
	
	elementoHTML.innerHTML = resultado;
	campoBotones.innerHTML = resultadoBotones;
}

/**************************************
 * Muestra un formulario vacío, para crear un nuevo usuario
 **************************************/
function crear_nuevo_usuario(nombreCapa, nombreCapaBotones, boton) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	
	bloquear_boton(boton);
	parametrosPeticion = '';
	cargarXML ('formularioUsuarioVacio', capa, botones, ver_usuario);	
}

/*****************************************
 * Envía los datos del formulario de usuario
 *****************************************/
function enviar_formulario_usuario(nombreFormulario, nombreDesplegable, nombreCapa, nombreBotones) {
	var formulario = document.getElementById(nombreFormulario);
	var desplegable = document.getElementById(nombreDesplegable);
	var campos = document.getElementsByTagName('input');
	var tipo = document.getElementsByTagName('select');
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreBotones);
	
	if (campos[1].value == '') {
		alert ('Escriba un nombre de usuario.');
		campos[1].focus();
		return false;
	} else if (campos[2].value != campos[3].value && campos[0].value == "-1") {
		alert ('Las contraseñas deben de ser iguales.');
		campos[2].focus();
		return false;
	} else if (campos[2].value == '' && campos[0].value == "-1") {
		alert ('Escriba una contraseña.');
		campos[2].focus();
		return false;
	} else if (campos[9].value == '') {
		alert ('Escriba una direccion de email');
		campos[9].focus();
		return false;
	}
	
	var aux = new Array();
	for (c = 0; c < campos.length; c++)
		aux[c] = campos[c].id+'='+campos[c].value;
	parametrosPeticion = aux.join("&");
	if (tipo.length > 0) {
		parametrosPeticion += '&tipo='+tipo[1].options[tipo[1].selectedIndex].value;
		cargarXML ('grabarUsuario', desplegable, null, resultado_grabacion);
	} else {
		cargarXML ('grabarUsuario', capa, botones, resultado_grabacion);
	}
	return true;
}

/*****************************************
 * Muestra los resultados de la grabación de los datos
 *****************************************/
function resultado_grabacion() {
	var usuarios = busca_etiquetas('usuario');
	if (usuarios.length > 0)
		escribir_lista_usuarios();
	else ver_usuario();
}

/*****************************************
 * Borra el usuario indicado
 *****************************************/
function borrar_usuario(nombreFormulario, nombreDesplegable, nombreCapaMensaje) {
	var formulario = document.getElementById(nombreFormulario);
	var desplegable = document.getElementById(nombreDesplegable);
	var campos = document.getElementsByTagName('input');
	var tipo = document.getElementsByTagName('select');
	var capa = document.getElementById(nombreCapaMensaje);
	
	var texto = "Está por borrar al usuario " + campos[1].value + ".\r\n¿Está seguro?";
	
	if (confirm(texto)) {
		parametrosPeticion = 'id='+campos[0].value;
		cargarXML ('borrarUsuario', desplegable, null, resultado_grabacion);
		vaciar_capas(Array('cuadroDatos', 'botones'));
	}
}

/********************************** ATRIBUTOS ************************************/
/******************************************
 * Lee la lista de tipos
 ******************************************/
function leer_lista_tipos (nombreListaTipos, nombreListaAtributos, nombreEtiqueta) {
	var desplegableTipos = document.getElementById(nombreListaTipos);
	parametrosPeticion='';
	cargarXML ('listaTipos', desplegableTipos, Array(nombreListaAtributos, nombreEtiqueta), escribir_lista_tipos);
}

/******************************************
 * Escribe la lista de tipos
 ******************************************/
function escribir_lista_tipos () {
	var tipos = busca_etiquetas('tipo');	

	vaciar_lista (elementoHTML);

	// Rellena la lista con los tipos	

	// Caso especial, se tiene que poner escoja un concepto al principio de la lista
	if (campoBotones[0] == null && campoBotones[1] == null) {
		var elemento = document.createElement('option');
		elemento.value = "-1";
		elemento.text = "Escoja un concepto";

		elementoHTML.options.add(elemento);
	}

	var c = 0;
	while (c < tipos.length) {
		var elemento = document.createElement('option');
		elemento.value = tipos[c].attributes.getNamedItem('id').nodeValue;
		elemento.text = tipos[c].attributes.getNamedItem('descripcion').nodeValue;

		try {
			elementoHTML.options.add(elemento);
		} catch(e) {
			alert ('error');
		}
		c++;
	}
	
	if (campoBotones[0] != null && campoBotones[1] != null) {
		// Ok, se realiza la petición de la lista de atributos
		leer_lista_atributos (elementoHTML, campoBotones[0], campoBotones[1]);
	}
}

/******************************************
 * Lee la lista de atributos
 ******************************************/
function leer_lista_atributos (listaTipos, nombreLista, nombreEtiqueta) {
	var listaAtributos = document.getElementById(nombreLista);
	var etiquetaAtributos;
	if (nombreEtiqueta != null)
		etiquetaAtributos = document.getElementById(nombreEtiqueta);
	else etiquetaAtributos = null
	
	parametrosPeticion = '&tipo='+listaTipos.options[listaTipos.selectedIndex].value;
	cargarXML ('listaAtributos', listaAtributos, etiquetaAtributos, escribir_lista_atributos);
}

/******************************************
 * Escribe la lista de tipos
 ******************************************/
function escribir_lista_atributos () {
	var atributos = busca_etiquetas('atributo');
	var etiqueta = busca_etiquetas('nombreAtributo');

	// Pone el nombre del tipo de atributo
	if (campoBotones != null)
		campoBotones.innerHTML = etiqueta[0].attributes.getNamedItem('texto').nodeValue;

	// Rellena la lista con los atributos
	vaciar_lista (elementoHTML);
	var c = 0;
	while (c < atributos.length) {
		var elemento = document.createElement('option');
		elemento.value = atributos[c].attributes.getNamedItem('id').nodeValue;
		elemento.text = atributos[c].attributes.getNamedItem('codigo').nodeValue + ' (' + atributos[c].attributes.getNamedItem('nombre').nodeValue + ')';

		try {
			elementoHTML.options.add(elemento);
		} catch(e) {
			alert ('error');
		}
		c++;
	}
}

/***************************************
 * Lee los datos del atributo seleccionado
 ***************************************/
function leer_atributo (listaAtributos, nombreCapa, nombreCapaBotones) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	
	if (listaAtributos.options[listaAtributos.selectedIndex].value > -1) {
		parametrosPeticion = 'id='+listaAtributos.options[listaAtributos.selectedIndex].value;
		// Se hace la petición
		// Se usa ver_usuario en lugar de una función propia ya que se realiza la misma función, mostrar código html y una serie de botones
		cargarXML ('leerAtributo', capa, botones, ver_usuario);
	}
}

/**************************************
 * Muestra un formulario vacío, para crear un nuevo atributo
 **************************************/
function crear_nuevo_atributo(nombreCapa, nombreCapaBotones, boton) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	
	bloquear_boton(boton);
	parametrosPeticion = '';
	cargarXML ('formularioAtributoVacio', capa, botones, ver_usuario);	
}

/*****************************************
 * Envía los datos del formulario de atributo
 *****************************************/
function enviar_formulario_atributo(nombreFormulario, nombreDesplegable, nombreDesplegableTipos, nombreEtiquetaAtributos) {
	var formulario = document.getElementById(nombreFormulario);
	var desplegable = document.getElementById(nombreDesplegable);
	var desplegableTipos = document.getElementById(nombreDesplegableTipos);
	var campos = document.getElementsByTagName('input');
	var etiquetaAtributos = document.getElementById(nombreEtiquetaAtributos);
	
	if (campos[0].value == '') {
		alert ('Escriba un nombre para el atributo.');
		campos[0].focus();
		return false;
	} else if (campos[1].value == '') {
		alert ('Escriba un código.');
		campos[1].focus();
		return false;
	} else if (campos[2].value == '') {
		alert ('Escriba un importe.');
		campos[2].focus();
		return false;
	}
	
	parametrosPeticion = 'nombre='+campos[0].value+'&codigo='+campos[1].value+'&importe='+campos[2].value+'&id='+campos[3].value+'&tipo='+desplegableTipos.options[desplegableTipos.selectedIndex].value;
	cargarXML ('grabarAtributo', desplegable, etiquetaAtributos, resultado_grabacion_atributo);
	return true;
}

/*****************************************
 * Muestra los resultados de la grabación de los atributos
 *****************************************/
function resultado_grabacion_atributo() {
	escribir_lista_atributos();
}

/*****************************************
 * Borra el atributo indicado
 *****************************************/
function borrar_atributo(nombreFormulario, nombreDesplegable, nombreDesplegableTipos, nombreEtiqueta) {
	var formulario = document.getElementById(nombreFormulario);
	var desplegable = document.getElementById(nombreDesplegable);
	var desplegableTipos = document.getElementById(nombreDesplegableTipos);
	var etiqueta = document.getElementById(nombreEtiqueta);
	var campos = document.getElementsByTagName('input');
	
	var texto = "Está por borrar el atributo " + campos[0].value + ".\r\n¿Está seguro?";
	
	if (confirm(texto)) {
		parametrosPeticion = 'id='+campos[3].value+'&tipo='+desplegableTipos.options[desplegableTipos.selectedIndex].value;
		cargarXML ('borrarAtributo', desplegable, etiqueta, resultado_grabacion_atributo);
		vaciar_capas(Array('cuadroDatos', 'botones'));
	}
}

/********************************** CLIENTES ************************************/
/*************************************
 * Muestra la lista de clientes
 *************************************/
function escribir_lista_clientes () {
	var clientes = busca_etiquetas('cliente');

	// Borra la lista actual de usuarios
	vaciar_lista (elementoHTML);
	
	// Ok, ahora rellena con los usuarios
	var c = 0;
	while (c < clientes.length) {
		var elemento = document.createElement('option');
		elemento.value = clientes[c].attributes.getNamedItem('id').nodeValue;
		elemento.text = clientes[c].attributes.getNamedItem('nombrecomercial').nodeValue + "(" + clientes[c].attributes.getNamedItem('referencia').nodeValue + ")";

		try {
			elementoHTML.options.add(elemento);
		} catch(e) {
			alert ('error');
		}
		c++;
	}
}

/***************************************
 * Lee la lista de los clientes y los muestra
 ***************************************/
function leer_lista_clientes (nombreElemento) {
	var desplegable = document.getElementById(nombreElemento);
	parametrosPeticion='';
	cargarXML ('listaClientes', desplegable, null, escribir_lista_clientes);
}

/***************************************
 * Lee los datos del cliente seleccionado
 ***************************************/
function leer_cliente (listaClientes, nombreCapa, nombreCapaBotones) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	
	if (listaClientes.options[listaClientes.selectedIndex].value > -1) {
		parametrosPeticion = 'id='+listaClientes.options[listaClientes.selectedIndex].value;
		// Se hace la petición
		// Se usa ver_usuario en lugar de una función propia ya que se realiza la misma función, mostrar código html y una serie de botones
		cargarXML ('leerCliente', capa, botones, ver_usuario);
	}
}

/**************************************
 * Muestra un formulario vacío, para crear un nuevo cliente
 **************************************/
function crear_nuevo_cliente(nombreCapa, nombreCapaBotones, boton) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	
	bloquear_boton(boton);
	parametrosPeticion = '';
	cargarXML ('formularioCliente', capa, botones, ver_usuario);	
}

/*****************************************
 * Envía los datos del formulario de cliente
 *****************************************/
function enviar_formulario_cliente(nombreFormulario, nombreDesplegable) {
	var formulario = document.getElementById(nombreFormulario);
	var desplegable = document.getElementById(nombreDesplegable);
	var campos = document.getElementsByTagName('input');
	
	if (campos[0].value == '') {
		alert ('Escriba una referencia.');
		campos[0].focus();
		return false;
	} else if (campos[1].value == '') {
		alert ('Escriba un nombre comercial.');
		campos[1].focus();
		return false;
	}
	
	// Crea la cadena de parametros y envía la petición
	parametrosPeticion = '';
	for (c = 0; c < campos.length; c++) {
		parametrosPeticion += campos[c].name + '=' + campos[c].value;
		if (c < campos.length-1)
			parametrosPeticion += '&';
	}
	
	cargarXML ('grabarCliente', desplegable, null, resultado_grabacion_cliente);
	return true;
}

/*****************************************
 * Muestra los resultados de la grabación de los clientes
 *****************************************/
function resultado_grabacion_cliente() {
	escribir_lista_clientes();
}

/*****************************************
 * Borra el cliente indicado
 *****************************************/
function borrar_cliente(nombreFormulario, nombreDesplegable) {
	var formulario = document.getElementById(nombreFormulario);
	var desplegable = document.getElementById(nombreDesplegable);
	var campos = document.getElementsByTagName('input');
	
	var texto = "Está por borrar al cliente " + campos[1].value + ".\r\n¿Está seguro?";
	
	if (confirm(texto)) {
		parametrosPeticion = 'id='+campos[campos.length-1].value;
		cargarXML ('borrarCliente', desplegable, null, resultado_grabacion_cliente);
		vaciar_capas(Array('cuadroDatos', 'botones'));
	}
}

/********************************** DISTRIBUIDORES ************************************/
/*************************************
 * Muestra la lista de distribuidores
 *************************************/
function escribir_lista_distribuidores () {
	var distribuidores = busca_etiquetas('distribuidor');

	// Borra la lista actual de usuarios
	vaciar_lista (elementoHTML);
	
	// Ok, ahora rellena con los usuarios
	var c = 0;
	while (c < distribuidores.length) {
		var elemento = document.createElement('option');
		elemento.value = distribuidores[c].attributes.getNamedItem('id').nodeValue;
		elemento.text = distribuidores[c].attributes.getNamedItem('nombrecomercial').nodeValue + "(" + distribuidores[c].attributes.getNamedItem('referencia').nodeValue + ")";

		try {
			elementoHTML.options.add(elemento);
		} catch(e) {
			alert ('error');
		}
		c++;
	}
}

/***************************************
 * Lee la lista de los distribuidores y los muestra
 ***************************************/
function leer_lista_distribuidores (nombreElemento) {
	var desplegable = document.getElementById(nombreElemento);
	parametrosPeticion='';
	cargarXML ('listaDistribuidores', desplegable, null, escribir_lista_distribuidores);
}

/***************************************
 * Lee los datos del distribuidor seleccionado
 ***************************************/
function leer_distribuidor (listaDistribuidores, nombreCapa, nombreCapaBotones) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	
	if (listaDistribuidores.options[listaDistribuidores.selectedIndex].value > -1) {
		parametrosPeticion = 'id='+listaDistribuidores.options[listaDistribuidores.selectedIndex].value;
		// Se hace la petición
		// Se usa ver_usuario en lugar de una función propia ya que se realiza la misma función, mostrar código html y una serie de botones
		cargarXML ('leerDistribuidor', capa, botones, ver_usuario);
	}
}

/**************************************
 * Muestra un formulario vacío, para crear un nuevo distribuidor
 **************************************/
function crear_nuevo_distribuidor(nombreCapa, nombreCapaBotones, boton) {
	var capa = document.getElementById(nombreCapa);
	var botones = document.getElementById(nombreCapaBotones);
	
	bloquear_boton(boton);
	parametrosPeticion = '';
	cargarXML ('formularioDistribuidor', capa, botones, ver_usuario);	
}

/*****************************************
 * Envía los datos del formulario de distribuidor
 *****************************************/
function enviar_formulario_distribuidor(nombreFormulario, nombreDesplegable) {
	var formulario = document.getElementById(nombreFormulario);
	var desplegable = document.getElementById(nombreDesplegable);
	var campos = document.getElementsByTagName('input');
	
	if (campos[0].value == '') {
		alert ('Escriba una referencia.');
		campos[0].focus();
		return false;
	} else if (campos[1].value == '') {
		alert ('Escriba un nombre comercial.');
		campos[1].focus();
		return false;
	}
	
	// Crea la cadena de parametros y envía la petición
	parametrosPeticion = '';
	for (c = 0; c < campos.length; c++) {
		parametrosPeticion += campos[c].name + '=' + campos[c].value;
		if (c < campos.length-1)
			parametrosPeticion += '&';
	}
	
	cargarXML ('grabarDistribuidor', desplegable, null, resultado_grabacion_distribuidor);
	return true;
}

/*****************************************
 * Muestra los resultados de la grabación de los distribuidores
 *****************************************/
function resultado_grabacion_distribuidor() {
	escribir_lista_distribuidores();
}

/*****************************************
 * Borra el distribuidor indicado
 *****************************************/
function borrar_distribuidor(nombreFormulario, nombreDesplegable) {
	var formulario = document.getElementById(nombreFormulario);
	var desplegable = document.getElementById(nombreDesplegable);
	var campos = document.getElementsByTagName('input');
	
	var texto = "Está por borrar al distribuidor " + campos[1].value + ".\r\n¿Está seguro?";
	
	if (confirm(texto)) {
		parametrosPeticion = 'id='+campos[campos.length-1].value;
		cargarXML ('borrarDistribuidor', desplegable, null, resultado_grabacion_distribuidor);
		vaciar_capas(Array('cuadroDatos', 'botones'));
	}
}

/********************************** PEDIDOS ******************************/
/*********************
 * Solicita el listado de pedidos
 **************/
function listado_pedidos(nombreCapaDatos, nombreCapaBotones) {
	var capa = document.getElementById(nombreCapaDatos);
	var botones = document.getElementById(nombreCapaBotones);
	
	parametrosPeticion = ''
	cargarXML ('listadoPedidos', capa, botones, mostrar_listado_pedidos);
}

/**********************
 * Muestra el listado de los pedidos
 **************/
function mostrar_listado_pedidos() {
	var resultado = busca_etiquetas('html');
	resultado = IE ? resultado[0].text : resultado[0].textContent;
	var resultadoBotones = busca_etiquetas('botones');
	resultadoBotones = IE ? resultadoBotones[0].text : resultadoBotones[0].textContent;
	
	elementoHTML.innerHTML = resultado;
	campoBotones.innerHTML = resultadoBotones;
}

/*********************
 * Carga el formulario para un nuevo pedido
 *************/
function nuevo_pedido(nombreCapaDatos, nombreCapaBotones) {
	var capaDatos = document.getElementById (nombreCapaDatos);
	var capaBotones = document.getElementById (nombreCapaBotones);
	
	parametrosPeticion = '';
	cargarXML ('nuevoPedido', capaDatos, capaBotones, mostrar_pedido);
}

/**********************
 * Muestra el pedido solicitado. También muestra el formulario para un pedido nuevo.
 ****************/
function mostrar_pedido() {
	var script = busca_etiquetas('javascript');
	script = IE ? script[0].text : script[0].textContent;
	
	eval (script);
	
	ver_usuario();
}

/********************
 * Lee la lista de atributos
 **************/
function leer_atributos(desplegableTipos, nombreDesplegableAtributos) {
	// Verifica si se ha seleccionado la primera opción, en ese caso solo vacía el otro desplegable y le pone la opción de "escoja un concepto"
	if (desplegableTipos.options[desplegableTipos.selectedIndex].value == -1) {
		var listaAtributos = document.getElementById(nombreDesplegableAtributos);
		vaciar_lista (listaAtributos);
		var elemento = document.createElement('option');
		elemento.value = -1;
		elemento.text = "Escoja un concepto";
		
		try {
			listaAtributos.options.add(elemento);
		} catch (e) {
			alert(elemento.value);
		}
	} else
		leer_lista_atributos(desplegableTipos, nombreDesplegableAtributos, null);
}

/*************************
 * Muestra la lista de clientes y copia el seleccionado
 *********************/
function buscar_cliente(nombreCuadroCliente, nombreCapaDatos) {
	window.open('index2.php?opt=pedidos&task=listaClientes&cuadro='+nombreCuadroCliente, 'clientes', 'top=100,left=100,width=350,height=230');
}

/*************************
 * Muestra la lista de distribuidores y copia el seleccionado
 *********************/
function buscar_distribuidor(nombreCuadroDistribuidor, nombreCapaDatos) {
	window.open('index2.php?opt=pedidos&task=listaDistribuidores&cuadro='+nombreCuadroDistribuidor, 'clientes', 'top=100,left=100,width=350,height=230');
}

/************************
 * cierra la ventana actual y pasa el resultado al cuadro indicado en la ventana padre.
 **********/
function cerrar_ventana(nombreLista, nombreCuadro) {
	var lista = document.getElementById(nombreLista);
	var cuadro = window.opener.parent.document.getElementById(nombreCuadro);
	
	if (lista.selectedIndex == -1)
		alert ('Seleccione un elemento, por favor');
	else {
		var aux = lista.options[lista.selectedIndex].text.split('(');
		var aux = aux[1].split(')');
		cuadro.value=aux[0];
		window.close();
	}
}

/************************
 * Acepta el paso actual y añade uno nuevo
 ******************/
function aceptar_paso_insertar(paso) {
	var celdaInicio = document.getElementById('comandos_'+paso);
	var concepto = document.getElementById('tipo_'+paso);
	var descripcion = document.getElementById('descripcion_'+paso);
	var tabla = document.getElementById('pedido');
	var fila = document.createElement('tr');
	var celda = document.createElement('td');
	
	// Primero se verifica si se ha seleccionado un concepto y una descripcion. Si no se ha seleccionado se devuelve un error
	if (concepto.selectedIndex == 0 || descripcion.options[descripcion.selectedIndex].value == -1) {
		alert ('Escoja un concepto y una descripcion');
		concepto.focus();
	} else { // Si se ha seleccionado
		// Se modifica el botón por el de borrar
		celdaInicio.innerHTML="<img src='imagenes/cancelar.gif' title='Borrar' alt='Borrar' class='falsoBoton' onClick='borrar_paso("+paso+");'>";
	
		// Se añade una nueva fila a la tabla
		paso = paso + 1;
		celda.innerHTML = paso+1;
		fila.appendChild(celda);
		celda = document.createElement('td');
		celda.innerHTML = '<select id="tipo_'+paso+'" name="tipo_'+paso+'" onChange="leer_atributos(this, \'descripcion_'+paso+'\');">'+
							'</select>';
		fila.appendChild(celda);
		celda = document.createElement('td');
		celda.innerHTML = '<select id="descripcion_'+paso+'" name="descripcion_'+paso+'">'+
							'<option value="-1">Escoja un concepto</option>'+
							'</select>';
		fila.appendChild(celda);
		celda = document.createElement('td');
		celda.innerHTML = "<img src='imagenes/insertar.png' class='falsoBoton' alt='Aceptar' title='aceptar' onClick='paso=aceptar_paso_insertar(paso);'>";
		celda.id = 'comandos_'+paso;
		fila.appendChild(celda);
		
		fila.id = "fila_"+paso;
		
		tabla.lastChild.appendChild(fila);
		
		// Se cargan los conceptos
		leer_lista_tipos('tipo_'+paso, null, null);
	}
	return paso;
}

/**********************
 * Borra el paso indicado
 *************/
function borrar_paso(paso) {
	// busca la fila a borrar y la elimina
	var lista1 = document.getElementById('tipo_'+paso);
	var lista2 = document.getElementById('descripcion_'+paso);
	var celda = document.getElementById('comandos_'+paso);

	vaciar_lista(lista1);
	vaciar_lista(lista2);
	celda.innerHTML='';
}

/******************************
 * Verifica el pedido y lo envía
 *	definitivo: Indica si el pedido es definitivo o solo se grabará como borrador. Si es un borrador, entonces se pueden dejar campos en blanco.
 *****************************/
function enviar_formulario_pedido(nombreFormulario, nombreCapa, nombreCapaBotones, definitivo) {
	var campos = document.getElementsByTagName('input');
	var desplegables = document.getElementsByTagName('select');
	var observaciones = document.getElementById('observaciones');
	var formulario = document.getElementById(nombreFormulario);
	
	// verifica si hay campos pendientes de completar y genera la cadena de parametros
	var pendiente = false;
	parametrosPeticion = '';
	for (c = 0; c < campos.length; c++) {
		pendiente = pendiente | (campos[c].value == '');
		parametrosPeticion += '&' + campos[c].id + '=' + encodeURIComponent(campos[c].value);
		if (campos[c].value == '')
			parametrosPeticion += '%20';
	}
	var totalDesplegables = 0;
	for (c = 0; c < desplegables.length; c++) {
		var index = desplegables[c].selectedIndex;
		pendiente = pendiente | index == -1 | (index == 0 && desplegables[c].options[index].value == -1);
		if (desplegables[c].selectedIndex > -1) {
			parametrosPeticion += '&' + desplegables[c].id + '=' + desplegables[c].options[index].value;
			totalDesplegables += 1;
		}
	}
	parametrosPeticion += '&desplegables=' + totalDesplegables;
	if (observaciones.value)
		parametrosPeticion += '&observaciones=' + encodeURIComponent(observaciones.value);
		
	parametrosPeticion = 'definitivo=' + (definitivo ? 'SI' : 'NO') + parametrosPeticion;
	
	// Si hay campos pendientes y se quiere enviar el pedido se manda un aviso
	if (definitivo && pendiente)
		alert ('No se puede enviar un pedido mientras queden campos por completar. Solo puede guardarlo como borrador.');
	else { // en cualquier otro caso se envía el pedido
		var capaDatos = document.getElementById(nombreCapa);
		var capaBotones = document.getElementById(nombreCapaBotones);
		cargarXML ('guardarPedido', capaDatos, capaBotones, resultado_grabacion_pedido);
	}
}

/********************************
 * Muestra el resultado de la grabación del pedido. Si hubo algún error se muestra el formulario, sino se muestra la lista de pedidos
 *************************/
function resultado_grabacion_pedido() {
	ver_usuario();
}

/*********************************
 * Muestra el pedido solicitado
 ***************************/
function leer_pedido(idPedido, nombreCapaDatos, nombreCapaBotones) {
	var capaDatos = document.getElementById(nombreCapaDatos);
	var capaBotones = document.getElementById(nombreCapaBotones);
	
	parametrosPeticion = 'id='+idPedido;
	cargarXML('leerPedido', capaDatos, capaBotones, mostrar_pedido);
}

/********************************
 * Borra el pedido solicitado
 ******************************/
function borrar_pedido(idPedido, nombreCapaDatos, nombreCapaBotones) {
	var capaDatos = document.getElementById(nombreCapaDatos);
	var capaBotones = document.getElementById(nombreCapaBotones);

	if (confirm('Está por borrar el pedido nº ' + idPedido + '\r\n¿Está seguro?')) {
		parametrosPeticion = 'id='+idPedido;
		cargarXML('borrarPedido', capaDatos, capaBotones, mostrar_listado_pedidos);
	}
}
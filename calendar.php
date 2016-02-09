<!--
    Carga en castellano de una librería para mostrar calendarios interactivos.
-->
<html>
<head>
	<link rel="stylesheet" rev="stylesheet" href="admin.css" type="text/css">
</head>
<body>
<script type="text/javascript" src="./js/tbl_change.js"></script>
<script type="text/javascript">
var month_names = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
var day_names = new Array('Dom','Lun','Mar','Mi&eacute;','Jue','Vie','S&aacute;b','Dom');
var submit_text = "";
</script>
</head>
<body onload="initCalendar();">
<div id="calendar_data"></div>
<div id="clock_data"></div>
</body>
</html>

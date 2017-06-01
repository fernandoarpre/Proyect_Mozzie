<?php
//definicion de rutas y constantes
define('ROOT_PATH',	".");
define('MODULE_PATH',	ROOT_PATH.'/modulos/');
define('STATIC_PATH',	ROOT_PATH.'/static/');
define('TEMPLATE_PATH',	STATIC_PATH.'/template/');
define('CONF_PATH',  ROOT_PATH.'/config.php');
define("CONFIG",ROOT_PATH."/config/");
define ('BASE_URL_PATH', 'http://'.dirname($_SERVER['HTTP_HOST'].''.$_SERVER['SCRIPT_NAME']).'/');
define( 'DB_HOST',         '');
define( 'DB_DATABASE',     '');
define( 'DB_USER',         '');
define( 'DB_PASSWORD',     '');
//require CONF_PATH;

define( 'URL_INGRESO', 'http://localhost/cfc_servicios/modulos/SW_login/SW_login.php?wsdl');
define( 'URL_CONSULTAS', 'http://localhost/cfc_servicios/modulos/SW_Consultas/SW_Consultas.php?wsdl');
define( 'URL_CONSULTA_AFILIADOS', 'http://localhost/cfc_servicios/modulos/SW_Consulta_Afiliados/SW_Consulta_Afiliados.php?wsdl');
?>
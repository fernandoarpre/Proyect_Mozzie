<?php
//require 'config.php';
//require 'mainTemplate.php';
include 'mainModel.php';
include 'Model.php';
include 'Crud.php';
require_once('lib/nusoap.php');
//ini_set('display_errors', false);
register_shutdown_function(function(){
	$error = error_get_last();
		if(null !== $error) {
		// $error ( [type] , [message] , [file] , [line] )
			echo "<div style='font-size:11px'><b>Mensaje: </b>".$error['message']."<br>";
			echo "<b>Archivo: </b>".$error['file']."<br>";
			echo "<b>Linea: </b>".$error['line']."<br></div>";
		}
	});

// Funciones para el paso de datos y manejo de variables.!
try 
{
	$metodo = $_SERVER['REQUEST_METHOD'];
	$tipo_res = "";
	$response = null; 
    $variables = $_POST;
    $archivos = $_FILES;
   
	if(!isset($_POST['controlador'])){return;} // Evita que ocurra un error si no manda controlador.
	if(!isset($_POST['accion'])){return;} // Evita que ocurra un error si no manda accion.
	if(!isset($_POST['tipo_res'])){$tipo_res = 'JSON';} // Define tipo de respuesta como JSON.!
	$accion = $variables['accion'];
	$controller = $variables['controlador'];
	include '../modulos/'.$controller."/controlador.php";
	define('BASE_PATH', realpath(dirname(__FILE__)));
	function autoloader($class)
	{
	    
	    $path = "../modulos/";
	    $dir = opendir($path);
	    $files = array();
	    while ($current = readdir($dir)){
	        if( $current != "." && $current != "..") {
	        	$dir_modelo = '../modulos/'.$current.'/modelo.php';
	        	if(file_exists($dir_modelo)){
	                include $dir_modelo;
	        	}
	        }
	    }
	    include_once 'mainModel.php';
	}
	spl_autoload_register('autoloader');
	$clase = new $controller();
	$clase->params = $variables;
	$clase->files = $archivos;
	// Dependiendo de la accion se ejecutaran las tareas y se definira el tipo de respuesta.
	$response = $clase->$accion(); 
	if($tipo_res == "JSON")
	{
	  echo json_encode($response,true); // $response será un array con los datos de nuestra respuesta.
	}
	elseif ($tipo_res == "HTML") {
	  echo $response; // $response será un html con el string de nuestra respuesta.
	}
} // Fin Try
catch (Exception $e) {

}
class mainController 
{
	
	function __construct(){

	}
    function Crear_Archivos_Modulo($modulo,$datos_ejemplo,$descripcion)
	{		
			$rutaTemplate = "../../static/templates/";
			//Creacion de la carpeta
			$Template = new Template();
			$carpetaModulo="../".$modulo;
			$Template->makeDir($carpetaModulo);

			if($datos_ejemplo == "N")
			{
				//Creacion de la vista.!
				$vista = $Template->getFile($rutaTemplate."vista_se.html");
				$vista = str_replace("[[modulo]]",strtolower($modulo), $vista);
				$vista = str_replace("[[descripcion_modulo]]",$descripcion, $vista);
				$archivoVista = $carpetaModulo."/vista.html";
				$Template->putFile($archivoVista,$vista);

				//Creacion del Controlador.!
				$controlador = $Template->getFile($rutaTemplate."controlador_se.php");
				$controlador = str_replace("[[modulo]]",strtolower($modulo), $controlador);
				$archivoControlador = $carpetaModulo."/controlador.php";
				$Template->putFile($archivoControlador,$controlador);

				//Creacion del Modelo.!
				$modelo = $Template->getFile($rutaTemplate."modelo_se.php");
				$modelo = str_replace("[[modulo]]",strtolower($modulo), $modelo);
				$archivoModelo = $carpetaModulo."/modelo.php";
				$Template->putFile($archivoModelo,$modelo);

				//Creacion del Js!
				$JS = $Template->getFile($rutaTemplate."controlador_js_se.js");
				$JS = str_replace("[[modulo]]",strtolower($modulo), $JS);
				$archivoJS= $carpetaModulo."/controlador.js";
				$Template->putFile($archivoJS,$JS);

			}
			else
			{

			}
			
	}

	/* primero creamos la función que hace la magia
	 * esta funcion recorre carpetas y subcarpetas
	 * añadiendo todo archivo que encuentre a su paso
	 * recibe el directorio y el zip a utilizar 
	 */
	function agregar_zip($dir, $zip) {
	  //verificamos si $dir es un directorio
	  if (is_dir($dir)) {
	    //abrimos el directorio y lo asignamos a $da
	    if ($da = opendir($dir)) {
	      //leemos del directorio hasta que termine
	      while (($archivo = readdir($da)) !== false) {
	        /*Si es un directorio imprimimos la ruta
	         * y llamamos recursivamente esta función
	         * para que verifique dentro del nuevo directorio
	         * por mas directorios o archivos
	         */
	        if (is_dir($dir . $archivo) && $archivo != "." && $archivo != "..") {
	          //echo "<strong>Creando directorio: $dir$archivo</strong><br/>";
	          agregar_zip($dir . $archivo . "/", $zip);

	          /*si encuentra un archivo imprimimos la ruta donde se encuentra
	           * y agregamos el archivo al zip junto con su ruta 
	           */
	        } elseif (is_file($dir . $archivo) && $archivo != "." && $archivo != "..") {
	          //echo "Agregando archivo: $dir$archivo <br/>";
	          $zip->addFile($dir . $archivo, $dir . $archivo);
	          //unlink($dir . $archivo);//Destruye archivo
	        }
	      }
	      //cerramos el directorio abierto en el momento
	      closedir($da);
	    }
	  }
	}


}
?>
<?php
set_time_limit(20000);
class mainModel{
	private $DB;
	private $URL;
	public $mensaje;
	public $tmensaje;
	var $host='localhost';
	var	$db='gvc_camacol';
	var	$usuario='root';
	var	$password='';


	function __construct(){
	}
	function Conectarse(){
		if (!isset($_SESSION)) session_start();
		
		try{
			$dbHandle = new PDO("mysql:host=$this->host; dbname=$this->db; charset=utf8", $this->usuario, $this->password);
			$dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
	 		$dbHandle->setAttribute(PDO::ATTR_EMULATE_PREPARES,FALSE);
	 		$dbHandle->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");
			$this->DB= $dbHandle;
		}catch( PDOException $exception ){
			die($exception->getMessage());
		}
	}
	function Conec2(){
		if (!isset($_SESSION)) session_start();
		
		try{
			$dbHandle = new PDO("mysql:host=10.1.20.3; dbname=empresa20161231; charset=utf8", "root", "allpcccfmed");
			$dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
	 		$dbHandle->setAttribute(PDO::ATTR_EMULATE_PREPARES,FALSE);
	 		$dbHandle->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");
			$this->DB= $dbHandle;
		}catch( PDOException $exception ){
			die($exception->getMessage());
		}
	}

	function ConecParques()
	{
		if (!isset($_SESSION)) session_start();
		try{
			$dbHandle = new PDO("sqlsrv:server=10.1.20.5\SWPARQUES; Database=DBCamacol;", "sa", "CCFcamacol58");
			$dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
	 		$dbHandle->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);
			$this->DB= $dbHandle;

		}catch( PDOException $exception ){
			die($exception->getMessage());
		}
	}

	function ConecOfima()
	{
		if (!isset($_SESSION)) session_start();
		try{
			$dbHandle = new PDO("sqlsrv:server=10.1.20.5\SQL2014; Database=CCFCAMACOL;", "sa", "CCFcamacol58");
			$dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
	 		$dbHandle->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);
			$this->DB= $dbHandle;

		}catch( PDOException $exception ){
			die($exception->getMessage());
		}
	}

	public function nochange()
	{
		print("<b>No se Realizaron Cambios</b>");
		die();
	}
	function error_PDO($exception,$query){
    $trace=$exception->getTrace();
    $respuesta = "";
    $caperror = "";
    for ($a=0;$a < count($trace);$a++){
       if($trace[$a]['function']=='consultar_datos' || $trace[$a]['function']=='total_registros' || $trace[$a]['function']=='trae_uno' || $trace[$a]['function']=='ejecuta_query'){
        $error=explode("]",$exception->getMessage());
        $caperror = $exception->getMessage();
       }
    }
    print_r($query);
    die($caperror);
    escribir_log($query,$caperror);
    header(':', true, '400'); //forza a que el error no salga como respuesta exitosa web  
}
	
	function db_camacol()
	{
		try{  
        	$this->DB->exec("use empresa;");
	    }catch( PDOException $exception ){
	        $this->error_PDO($exception,"use empresa;");

	    }
		
	}
	function db_proyecto()
	{
		try{  
        	$this->DB->exec("use $this->db;");
	    }catch( PDOException $exception ){
	        $this->error_PDO($exception,"use $this->db;");

	    }
		
	}

	function consultar($query,$params){
		try{
			$statement = $this->DB->prepare($query);
			$statement->execute($params);
			$rows = $statement->fetchAll(PDO::FETCH_CLASS);
			return $rows;
		}catch( PDOException $exception ){
	        $this->error_PDO($exception,$query);
	    }	
	}

	function consultar_uno($query,$params){
		$mat=$this->consultar($query,$params);
		if(count($mat) > 0)
		{
			return $mat[0];	
		}
		else{
			return array();
		}
		
	}
	
	function consultar_datos($queri)
	{
		try{
        
        	$statement = $this->DB->query($queri);
       	    $rows = $statement->fetchAll(PDO::FETCH_CLASS);
			return $rows;
	    }catch( PDOException $exception ){
	        $this->error_PDO($exception,$queri);

	    }
		
	}

	function ejecuta_query($queri,$valid = '1',$retorna='count')
	{	
		$cant = 0;
		try{
			$cant=$this->DB->exec($queri);
			if($valid != '1')
			{
				if($cant <= 0 )
				{
					$this->nochange();
				}
			}
		}catch( PDOException $exception ){
			$this->error_PDO($exception,$queri);
		}
		
		if($retorna=='count') {return $cant;}
		else{return $this->DB->lastInsertId();}
	}

	public function ejecuta($query,$params,$valid='1',$retorna='count')
	{
		try{
			$statement = $this->DB->prepare($query);
			$cant = $statement->execute($params);
			if($valid != '1')
			{
				if($cant <= 0 )
				{
					$this->nochange();
				}
			}
		}catch( PDOException $exception ){
	        $this->error_PDO($exception,$query);
	    }

	    if($retorna=='count') {return $cant;}
		else{return $this->DB->lastInsertId();}
	}

	public function ultimoID()
	{
		return $this->DB->lastInsertId();
	}
	function begin_work()
	{
		$this->DB->beginTransaction();
	}
	function commit()
	{
		$this->DB->commit();
	}
	function rollback()
	{
		$this->DB->rollback();
	}
	function ejecuta_sp($queri)
	{
		try{
        $statement = $this->DB->query($queri);
	    }catch( PDOException $exception ){
	        $this->error_PDO($exception,$queri);

	    }
		$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
		return trim($rows[0]['']);
	}

	function trae_uno($query){
		$mat=$this->consultar_datos($query);
		if(count($mat) > 0 ){
			return $mat[0];	
		}else{
			return array();
		}
		
	}
	
	function total_registros($campo, $tabla, $where = "")
	{
		$q = "SELECT count(".$campo.") num FROM "
        . $tabla . " " . $where ;
		try{
        $statement = $this->DB->query($q);
	    }catch( PDOException $exception ){
	        $this->error_PDO($exception,$q);

	    }
		$rows = $statement->fetchAll(PDO::FETCH_CLASS);
		$num = $rows[0]->num;
		return $num;
	}

	public function ValidateForm($datos,$tipe = 0)
	{
		$arr_valid = array();
		$arr_lenght = array();
		$arr_dist = array();
		$response = array();
		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
				  WHERE table_name = '$this->table'";
		$tabla = $this->consultar_datos($query);

		$sqpri = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
				  WHERE table_name = '$this->table' AND COLUMN_KEY = 'PRI'";

		$dpri = $this->consultar_datos($sqpri);
		$key = $dpri[0]->column_name;

		for ($i=0; $i < count($tabla); $i++) {
			$columna = $tabla[$i]->column_name;
			if($tabla[$i]["COLUMN_KEY"] == "UNI"){
				$sql = "SELECT $key FROM $this->table WHERE $columna = '$datos[$columna]'";
				$cant = $this->consultar_datos($sql);
				// Que se diferente id
				if(count($cant) > 0){
					if($cant[0]->$key != isset($datos[$key])){
						$arr_dist[] = $columna;
					}
				}
			}
			
			if($tabla[$i]->data_type == "char"){
				$maxlen = $tabla[$i]->character_maximun_length;
				$mxdate = strlen($datos[$columna]);
				if($mxdate > $maxlen){
					$arr_lenght[] = $columna;
				}
			}
			if($tabla[$i]->is_nullable == "NO"){	
			    if($tipe == 1){
				    if(empty(trim($datos[$columna]))){	
			  	 	   $arr_valid[] = $columna;
			  	 	}
			  	}else{
			  		   if($tabla[$i]->column_key != "PRI"){
			  		   		if(empty(trim($datos[$columna]))){	
					  	 	  $arr_valid[] = $columna;
					  	   }
			  		   }
			  		}
			  		 
				}
		}
		$texto = "Por favor rellenar los campos vacios.";
		foreach($arr_valid as $value) {
			$response = array("state" => -2, "msg" => $texto, "v4l1d4" => $arr_valid);
		}
		foreach ($arr_lenght as $value) {
			$response = array("l4rg0" => $arr_lenght);
		}
		foreach ($arr_dist as $value) {
			$response = array("d1st1n" => $arr_dist);
		}

		return $response;
	}
	///// funcion para cifrar el password 
	function movpass($work, $n = 7) {
		$set_cant = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$cant = sprintf('$2a$%02d$', $n);
		for($i = 0; $i < 22; $i++)
		{
			$cant .= $set_cant[mt_rand(0, 22)];
		}
		return crypt($work, $cant);
	}
	 function InyeccionSql($cadena)
	 { 
		$invalido=array(";"=>" ","'"=>" "," alter "=>" "," drop "=>" "," select "=>" "," from "=>" "," where "=>" "," insert "=>" "," delete "=>" "," * "=>" "," or "=>""," and "=>" ","%27"=>" "," table "=>" "); 
		$correcto=strtr($cadena,$invalido); 
		$correcto=strip_tags($correcto); 
		return $correcto;
	} 

	function Guardar_Auditoria($id,$tabla,$tipo)
	{

		$tabla_auditoria = "audi_".$tabla;
		$existe_tabla = $this->total_registros("TABLE_NAME","INFORMATION_SCHEMA.TABLES"," WHERE TABLE_SCHEMA = '".$this->db."' AND TABLE_NAME = '$tabla_auditoria'");
		if($existe_tabla == 0)
		{
			$crear_tabla = 'CREATE TABLE IF NOT EXISTS '.$tabla_auditoria.' LIKE '.$tabla.'';
			$this->ejecuta_query($crear_tabla);
			$alter_table = "ALTER TABLE ".$tabla_auditoria."
								ADD COLUMN id_".$tabla." INT(11) NOT NULL ,
								ADD COLUMN fecha_audi DATE NOT NULL ,
								ADD COLUMN hora_audi TIME NOT NULL,
								ADD COLUMN usuario_audi INT(11) NOT NULL,
								ADD COLUMN tipo_audi INT(10) NOT NULL;";
		    $this->ejecuta_query($alter_table);
		}
		// INSERTA LOS DATOS QUE SE ENVIARON.!
		$consulta_columnas_tabla = "SELECT COLUMN_NAME nombre FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$this->db."' AND TABLE_NAME = '$tabla'
						   and COLUMN_NAME not in ('id','act_pass') order by ORDINAL_POSITION";
		$columnas = $this->consultar_datos($consulta_columnas_tabla);
		$str_columnas = "";
		for ($i=0; $i < count($columnas); $i++) { 
			$str_columnas .= $columnas[$i]->nombre.",";
		}
		$usuario = $_SESSION['usu_id'];
		if($id == "")
			{$id = 1;}
		$str_columnas = substr($str_columnas, 0, -1);
		$inserta_audi = "INSERT INTO ".$tabla_auditoria."
						 SELECT 0,$str_columnas,id,CURDATE(),CURTIME(),$usuario,$tipo FROM ".$tabla." WHERE id = '$id'";
		$this->ejecuta_query($inserta_audi);
	}

}

?>

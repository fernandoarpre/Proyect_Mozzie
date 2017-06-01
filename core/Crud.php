<?php 

class Crud {

	private $db;

	public $variables;

	public function __construct($data = array()) {
		$this->db =  new DB();	
		$this->variables  = $data;
	}

	public function __set($name,$value){
		if(strtolower($name) === $this->pk) {
			$this->variables[$this->pk] = $value;
		}
		else {
			$this->variables[$name] = $value;
		}
	}

	public function __get($name)
	{	
		if(is_array($this->variables)) {
			if(array_key_exists($name,$this->variables)) {
				return $this->variables[$name];
			}
		}

		return null;
	}


	public function update($id = "0") {
		$this->variables[$this->pk] = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

		$fieldsvals = '';
		$columns = array_keys($this->variables);

		foreach($columns as $column)
		{
			if($column !== $this->pk)
			$fieldsvals .= $column . " = :". $column . ",";
		}

		$fieldsvals = substr_replace($fieldsvals , '', -1);

		if(count($columns) > 1 ) {

			$sql = "UPDATE " . $this->table .  " SET " . $fieldsvals . " WHERE " . $this->pk . "= :" . $this->pk;
			if($id === "0" && $this->variables[$this->pk] === "0") { 
				unset($this->variables[$this->pk]);
				$sql = "UPDATE " . $this->table .  " SET " . $fieldsvals;
			}

			return $this->exec($sql);
		}

		return null;
	}

	public function create() { 
		$bindings   	= $this->variables;

		if(!empty($bindings)) {
			$fields     =  array_keys($bindings);
			$fieldsvals =  array(implode(",",$fields),":" . implode(",:",$fields));
			$sql 		= "INSERT INTO ".$this->table." (".$fieldsvals[0].") VALUES (".$fieldsvals[1].")";
		}
		else {
			$sql 		= "INSERT INTO ".$this->table." () VALUES ()";
		}

		return $this->exec($sql);
	}

	public function delete($id = "") {
		$id = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

		if(!empty($id)) {
			$sql = "DELETE FROM " . $this->table . " WHERE " . $this->pk . "= :" . $this->pk. " LIMIT 1" ;
		}

		return $this->exec($sql, array($this->pk=>$id));
	}

	public function find($id = "") {
		$id = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];

		if(!empty($id)) {
			$sql = "SELECT * FROM " . $this->table ." WHERE " . $this->pk . "= :" . $this->pk . " LIMIT 1";	
			
			$result = $this->db->row($sql, array($this->pk=>$id));
			$this->variables = ($result != false) ? $result : null;
		}
	}
	/**
	* @param array $fields.
	* @param array $sort.
	* @return array of Collection.
	* Example: $user = new User;
	* $found_user_array = $user->search(array('sex' => 'Male', 'age' => '18'), array('dob' => 'DESC'));
	* // Will produce: SELECT * FROM {$this->table_name} WHERE sex = :sex AND age = :age ORDER BY dob DESC;
	* // And rest is binding those params with the Query. Which will return an array.
	* // Now we can use for each on $found_user_array.
	* Other functionalities ex: Support for LIKE, >, <, >=, <= ... Are not yet supported.
	*/
	public function search($fields = array(), $sort = array()) {
		$bindings = empty($fields) ? $this->variables : $fields;

		$sql = "SELECT * FROM " . $this->table;

		if (!empty($bindings)) {
			$fieldsvals = array();
			$columns = array_keys($bindings);
			foreach($columns as $column) {
				$fieldsvals [] = $column . " = :". $column;
			}
			$sql .= " WHERE " . implode(" AND ", $fieldsvals);
		}
		
		if (!empty($sort)) {
			$sortvals = array();
			foreach ($sort as $key => $value) {
				$sortvals[] = $key . " " . $value;
			}
			$sql .= " ORDER BY " . implode(", ", $sortvals);
		}
		return $this->exec($sql);
	}

	public function consulta($query)
	{
		return $this->db->query($query);
	}

	public function all(){
		return $this->db->query("SELECT * FROM " . $this->table);
	}
	
	public function min($field)  {
		if($field)
		return $this->db->single("SELECT min(" . $field . ")" . " FROM " . $this->table);
	}

	public function max($field)  {
		if($field)
		return $this->db->single("SELECT max(" . $field . ")" . " FROM " . $this->table);
	}

	public function avg($field)  {
		if($field)
		return $this->db->single("SELECT avg(" . $field . ")" . " FROM " . $this->table);
	}

	public function sum($field)  {
		if($field)
		return $this->db->single("SELECT sum(" . $field . ")" . " FROM " . $this->table);
	}

	public function count($field)  {
		if($field)
		return $this->db->single("SELECT count(" . $field . ")" . " FROM " . $this->table);
	}

	public  function begin()
	{
		$this->db->begin();
	}
	public  function commit()
	{
		$this->db->commit();
	}
	public  function rollback()
	{
		$this->db->rollback();
	}
	
	function getEdad($fecha){
		$date2 = date('Y-m-d');//
		$diff = abs(strtotime($date2) - strtotime($fecha));
		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		return $years;
	}

	public function exec($sql, $array = null) {	
		if($array !== null) {
			$result =  $this->db->query($sql, $array);	
		}
		else {
			$result =  $this->db->query($sql, $this->variables);	
		}
		$this->variables = array();
		return $result;
	}

	public function ValidateForm($datos,$tipe = 0)
	{
		$arr_valid = array();
		$arr_lenght = array();
		$arr_dist = array();
		$response = array();
		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
				  WHERE table_name = '$this->table'";
		$tabla = $this->consulta($query);

		$sqpri = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
				  WHERE table_name = '$this->table' AND COLUMN_KEY = 'PRI'";

		$dpri = $this->consulta($sqpri);
		$key = $dpri[0]["COLUMN_NAME"];
		for ($i=0; $i < count($tabla); $i++) {
			$columna = $tabla[$i]["COLUMN_NAME"];
			if($tabla[$i]["COLUMN_KEY"] == "UNI"){
				$sql = "SELECT $key FROM $this->table WHERE $columna = '$datos[$columna]'";
				$cant = $this->consulta($sql);
				// Que se diferente id
				if(count($cant) > 0){
					if($tipe == 1){
					  if(isset($datos[$key])){
						 if($cant[0][$key] != $datos[$key]){
							$arr_dist[] = $columna;
						 }
					   }
				  	}else{
				  		$arr_dist[] = $columna;
				  	}
					
					
				}
			}
			if($tabla[$i]["DATA_TYPE"] == "char"){
				$maxlen = $tabla[$i]["CHARACTER_MAXIMUM_LENGTH"];
				$mxdate = strlen($datos[$columna]);
				if($mxdate > $maxlen){
					$arr_lenght[] = $columna;
				}
			}
			if($tabla[$i]["IS_NULLABLE"] == "NO"){	
			    if($tipe == 1){
				    if(empty(trim($datos[$columna]))){	
			  	 	   $arr_valid[] = $columna;
			  	 	}
			  	}else{
			  		   if($tabla[$i]["COLUMN_KEY"] != "PRI"){
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

}
?>
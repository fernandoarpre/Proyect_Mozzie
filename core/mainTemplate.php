<?php
class Template {
	public $modulo;
	public $template="gvc/core.php";
	public $VistaModulo='vista.html';
	private $arrayHead = array ();
	private $arrayBody = array ();
	private $arrayFooter = array ();

	public function __construct(){
	}
	public function AgregarCSS($file){
		$templateCSS="<link rel='stylesheet' type='text/css' href='$file'/>";
		$this->AgregarHead($templateCSS);
	}
	public function AgregarJS($file){
		$templateCSS="<script type='text/javascript' src='".$file.'?'.date("YmdHis")."'></script>";
		$this->AgregarFooter($templateCSS);
	}
	public function AgregarJShead($file){
		$templateCSS="<script type='text/javascript' src='$file'></script>";
		$this->AgregarHead($templateCSS);
	}


	private function AgregarHead ($html){
		$this->arrayHead[]=$html;
	}
	private function AgregarFooter ($html){
		$this->arrayFooter[]=$html;
	}
	
	public function getFile ($file){
		$link = @fopen($file,'r');
		if ($link){
			$size=filesize($file);
			if($size==0) $size=1;
			$data = fread($link,$size);
			fclose($link);
		}
		return $data;
	}
	
	public function putFile ($file,$data,$method='a+'){
		$link = @fopen($file,$method);
		if ($link){
			$data = fputs($link,$data);
			fclose($link);
		}
		@chmod($file, 0777);
	}

	public function makeDir($dir){
		if(!file_exists($dir)){
			if(@mkdir($dir)!==false){
			}else{
				die("Problema con permisos en las  carpetas, verifique");
			}
			@chmod($dir, 0777);
		}
	}

	public function cargarTemplate(){
		$dataVista = "";
		$error = "";
		$menu_html = "";
		#trae template
		$modulo = MODULE_PATH;
		$mod = $this->modulo;
		//Permisos..!
		if($mod != "login" ){
			//Permisos::ValidarPermisos($modulo);
			// cargar el menu..!
			$permisos = new Permisos();
			$menu_html = $permisos->CargarMenu();
		}
		$pos = strrpos($this->template, "../");
		if ($pos === false) { // nota: tres signos de igual
		   $fileTemplate = TEMPLATE_PATH.$this->template;
		   $error = "404.html";
		}
		
		$dataTemplate=$this->getFile($fileTemplate);
		#trae vista del modulo
		$fileVista = $modulo.$this->modulo."/".$this->VistaModulo;
		if(file_exists($fileVista))
		{
			$dataVista=$this->getFile($fileVista);	
		}
		else
		{
			$fileVista = $error;
			if(file_exists($fileVista))
			{
				$this->modulo = 'Error';
				$dataVista=$this->getFile($fileVista);	
			}
		}
		
		if($dataTemplate=='') $dataTemplate=$dataVista;

		//prueba controlador js. getfile permisos.!

		$ControllerJS = $modulo.$this->modulo."/controlador.js";
		$dataController = "";
		if(file_exists($ControllerJS)){
			//$this->AgregarJS(BASE_URL_PATH."modulos/".$this->modulo."/controlador.js");
			$dataController = $this->getFile($modulo.$this->modulo."/controlador.js");
		}
		$titulo =  strtoupper($this->modulo);
		//
		$dataTemplate=str_replace("<menapp>", $menu_html, $dataTemplate);
		
		$dataTemplate=str_replace("//<scriptController>", $dataController, $dataTemplate);
		$dataTemplate=str_replace("</head>", implode("\n", $this->arrayHead)."</head>", $dataTemplate);
		$dataTemplate=str_replace("</body>", implode("\n", $this->arrayFooter)."</body>", $dataTemplate);
		
		// APlica los permisos de la app.!
		$html_btn_insert = '<a class="btn btn-primary btn_insert" id="gvc_insert"><i class="material-icons">add</i> Insertar<div class="ripple-container"></div></a>';
		
		$html_btn_edit = '<a class="btn btn-info btn-simple btn-fab btn-fab-mini edit"><i class="material-icons">edit</i><div class="ripple-container"></div></a>';

		$html_btn_creacat = '<a class="btn btn-info btn-simple btn-fab crecat"><i class="material-icons">developer_board</i><div class="ripple-container"></div></a>';

		$html_btn_creacur = '<a class="btn btn-info btn-simple btn-fab crecur"><i class="material-icons">assignment</i><div class="ripple-container"></div></a>';

		$html_btn_active = '<a class="btn btn-simple btn-fab btn-fab-mini active"><i class="material-icons">album</i><div class="ripple-container"></div></a>';

		if($mod != "login" && $mod != "principal"){
			// Permisos del modulo.!
			$per_mod = $permisos->PermisosModulos($mod);
			if(count($per_mod) > 0){
				$dataTemplate=str_replace("</modulo>", $dataVista."</modulo>", $dataTemplate);
				if ($per_mod->per_insertar == "1") {
				$dataTemplate=str_replace("<btnInsert>", $html_btn_insert, $dataTemplate);
				}
				if($per_mod->per_editar == "1"){
					$dataTemplate = str_replace("<btnEdit>", $html_btn_edit, $dataTemplate);
					$dataTemplate = str_replace("<btnCreaCat>", $html_btn_creacat, $dataTemplate);
					$dataTemplate = str_replace("<btnCreaCur>", $html_btn_creacur, $dataTemplate);
				}
				if ($per_mod->per_active == "1") {
					$dataTemplate = str_replace("<btnActive>", $html_btn_active, $dataTemplate);
				}
			}else{
				$dataTemplate=str_replace("</modulo>", "<h1>No cuenta con permisos para este modulo.</h1>"."</modulo>", $dataTemplate);
			}
			
		}

		$dataTemplate=str_replace("paht_gvc",BASE_URL_PATH."static", $dataTemplate);
		//agregar nombre de usuario.
		if(isset($_SESSION['gvcID']) != "")
		{
			$img = "static/img/usuarios/".$_SESSION['gvcID'].".png";
			if(file_exists($img))
			{
				$dataTemplate=str_replace("[Img_Usuario]", $_SESSION['gvcID'], $dataTemplate);
			}
			else
			{
				$dataTemplate=str_replace("[Img_Usuario]", "user", $dataTemplate);
			}
			$dataTemplate=str_replace("</nousuario>", $_SESSION['nombre']."</nousuario>", $dataTemplate);
			$dataTemplate=str_replace("</naplicacion>", $_SESSION['nombre_app']."</naplicacion>", $dataTemplate);
			$dataTemplate=str_replace("</siapp>", $_SESSION['siglas_app']."</siapp>", $dataTemplate);
			
		}	
		echo $dataTemplate;
	} 
}

class Permisos extends mainModel{

	function __construct()
	{
		$this->Conectarse();
		$this->user = $_SESSION["gvc_user"];
		$this->role = $_SESSION["gvc_rol"];
		$this->datos_menu = array();
		$this->html_menu = "";
	}

	public function PermisosModulos($modulo)
	{
		#Verifica los permisos del modulo en la aplicacion.!
		$con = "SELECT per_consultar, per_editar, per_insertar, per_active FROM gvc_permisos
				INNER JOIN gvc_modulos ON mod_id = per_mod_id
				INNER JOIN gvc_roles ON rol_id = per_rol_id
				WHERE mod_estado = 1 AND rol_estado = 1 
				AND rol_id = '$this->role' AND mod_url = '$modulo'";
		$datos_per = $this->trae_uno($con);
		return $datos_per;
	}
	public function CargarMenu()
	{
		$consulta = "SELECT mod_id,mod_padre,mod_nombre,mod_descripcion,mod_url,mod_icono FROM gvc_usuarios
                   INNER JOIN gvc_roles ON rol_id = usu_role
                   INNER JOIN gvc_permisos ON per_rol_id = rol_id
                   INNER JOIN gvc_modulos ON mod_id = per_mod_id
                   WHERE usu_id = '$this->user' AND usu_role = '$this->role'
                   AND mod_estado = '1' AND rol_estado = '1'
                   AND usu_estado = '1' ORDER BY 2,3";
        $this->datos_menu = $this->consultar_datos($consulta);
        $this->Html_Menu();

        return $this->html_menu;
	}

	public function Html_Menu($id_padre = 0)
	{
		
		if($this->Menu_Tiene_Hijos($id_padre) > 0){
			foreach ($this->datos_menu as $key => $value) {
			   $id_menu = $value->mod_id;
               $padre = $value->mod_padre;
			   
			   if($padre == $id_padre){
					
					if($padre == 0){
						if($this->Menu_Tiene_Hijos($id_menu) > 0){
							$this->html_menu .= "<li><a data-toggle='collapse' href='#mod".$value->mod_id."' class='collapsed' aria-expanded='false'><i class='material-icons'>image</i><p>".$value->mod_nombre."<b class='caret'></b></p></a><div class='collapse' id='mod".$value->mod_id."' aria-expanded='false' style='height: auto;'><ul class='nav'>";
							$this->Html_Menu($id_menu);
						}else{
							$this->html_menu .= "<li><a href='./".$value->mod_url."'>".$value->mod_nombre."</a></li>";
						}
						if($this->Menu_Tiene_Hijos($id_menu) > 0)
							$this->html_menu .= "</ul></div></li>";
					}else{
						if($this->Menu_Tiene_Hijos($id_menu) > 0){
							$this->html_menu .= "<li><a data-toggle='collapse' href='#mod".$value->mod_id."' class='collapsed' aria-expanded='false'><i class='material-icons'>image</i><p>".$value->mod_nombre."<b class='caret'></b></p></a><div class='collapse' id='mod".$value->mod_id."' aria-expanded='false' style='height: auto;'><ul class='nav'>";
							$this->Html_Menu($id_menu);
						}else{
							$this->html_menu .= "<li><a href='./".$value->mod_url."'>".$value->mod_nombre."</a></li>";
						}
						if($this->Menu_Tiene_Hijos($id_menu) > 0)
							$this->html_menu .= "</ul></div></li>";
					}
			   }
			   
			}
			
		}else{
			foreach ($this->datos_menu as $key => $value) {
				$idmen = $value->mod_id;
				$padre = $value->mod_padre;
				if($idmen == $padre){
					$this->html_menu .= "<li class='active'>
                        <a href='./".$value->mod_url."'>
                            <i class='material-icons'>dashboard</i>
                            <p>".$value->mod_nombre."</p>
                        </a>
                    </li>";
				}
			}
		}
			}

	public function Menu_Tiene_Hijos($id_padre) {
		  $n = 0;
		  $menu = $this->datos_menu;
		  foreach ($menu as $key => $value) {

		  	 $padre = $value->mod_padre;
		  	 if($padre == $id_padre){
		  	 	$n += 1;
		  	 }
		  }
		  return $n;
	}

	public function ValidarPermisos($modelo)
	{
		$consulta = "Select * from ";
	}
}
?>
<?php

namespace formulaire\php;

require 'Field.php';
use formulaire\php\Field;

require 'FormulaireDriver.php';
use formulaire\php\FormulaireDriver;

require 'FormulaireDriverPGSQL.php';
use formulaire\php\FormulaireDriverPGSQL;

require 'FormulaireDriverMYSQL.php';
use formulaire\php\FormulaireDriverMYSQL;
use formulaire\php\exception\PDOConnectionException;

require 'tools.php';

/**
 * Classe principale du plugin "Formulaire".
 * @author Sébastien JOLY
 * @author Jonathan SANTONI
 */
class Formulaire {

	// Langues disponibles
	public static $FRANCAIS 		= "fr";
	public static $ENGLISH 			= "en";
	
	// Paramètres par défaut
	protected static $BDD_URL 		= "127.0.0.1";
	protected static $BDD_LOGIN 	= "root";
	protected static $BDD_PASSWORD 	= "";
	protected static $BDD_DATABASE 	= "";
	protected static $BDD_DRIVER	= "pgsql";
	protected static $LANGUAGE 		= "fr";
	protected static $ENCODING 		= "UTF-8";
	protected static $MODE			= "insert";
	
	// Paramètres de la base de données
	public $url 		= "";
	public $login 		= "";
	public $password 	= "";
	public $database 	= "";
	public $table		= "";
	public $driver 		= "";
	public $language 	= "";
	public $encoding 	= "";
	public $mode		= "";

	// Attributs privés
	private $connect 	= null;
	private $concreteDriver = null;
	private $request	= "";
	private $error 		= "";
	
	// Attributs de la classe "Formulaire"
	public $id;
	public $fieldList;
	public $labelSize = array();
	public $inputSize = array();
	
	// Constructeur
	function __construct() {
		
		$this->url 		= Formulaire::$BDD_URL;
		$this->driver 	= Formulaire::$BDD_DRIVER;
		$this->database = Formulaire::$BDD_DATABASE;
		$this->login 	= Formulaire::$BDD_LOGIN;
		$this->password = Formulaire::$BDD_PASSWORD;
		$this->language = Formulaire::$LANGUAGE;
		$this->encoding = Formulaire::$ENCODING;
		$this->mode		= Formulaire::$MODE;
		
		$this->fieldList = new \ArrayObject();
	}
	
	/**
	 * Initialisation de la connexion à la base de données
	 * @param $url			URL du serveur de la base de données
	 * @param $driver		Type de la base de données (MySQL, PostgreSQL, ...)
	 * @param $database		Nom de la base de données
	 * @param $table		Nom de la table
	 * @param $login		Login de l'utilisateur
	 * @param $password		Password de l'utilisateur
	 * @return Formulaire
	 */
	public function init($url = "", $driver = "", $database = "",  $table, $login = "", $password = "") {
	
		if (!empty($url)) 		$this->url = $url;
		if (!empty($driver)) 	$this->driver = $driver;
		if (!empty($database)) 	$this->database = $database;
								$this->table = $table;
		if (!empty($login)) 	$this->login = $login;
		if (!empty($password)) 	$this->password = $password;
		
		$this->id = time();
		
		$this->setConcreteDriver();
		
		$this->initGridSize();
				
		return $this;
	}
	
	public function initGridSize() {
		$this->labelSize['col-xs-'] = 4;
		$this->labelSize['col-sm-'] = 4;
		$this->labelSize['col-md-'] = 4;
		$this->labelSize['col-lg-'] = 4;
		$this->inputSize['col-xs-'] = 8;
		$this->inputSize['col-sm-'] = 8;
		$this->inputSize['col-md-'] = 8;
		$this->inputSize['col-lg-'] = 8;
	}
	
	public function setAllGridColumn($label, $input) {
		foreach ($this->labelSize as $key => $value) {
			$this->labelSize[$key] = $label;
		}
		
		foreach ($this->inputSize as $key => $value) {
			$this->inputSize[$key] = $input;
		}
	}
	
	public function setGridColumnXs($label, $input) {
		$this->labelSize['col-xs-'] = $label;
		$this->inputSize['col-xs-'] = $input;
	}
	
	public function setGridColumnSm($label, $input) {
		$this->labelSize['col-sm-'] = $label;
		$this->inputSize['col-sm-'] = $input;
	}
	
	public function setGridColumnMd($label, $input) {
		$this->labelSize['col-md-'] = $label;
		$this->inputSize['col-md-'] = $input;
	}
	
	public function setGridColumnLg($label, $input) {
		$this->labelSize['col-Lg-'] = $label;
		$this->inputSize['col-Lg-'] = $input;
	}
	
	public function getLabelSize() {
		$labelsPrint = " ";
	
		foreach ($this->labelSize as $key => $value) {
			$labelsPrint .= $key . $value . " ";
		}
	
		return $labelsPrint;
	}
	
	public function getInputSize() {
		$inputsPrint = " ";
	
		foreach ($this->inputSize as $key => $value) {
			$inputsPrint .= $key . $value . " ";
		}
	
		return $inputsPrint;
	}
	
	
	/**
	 * Connexion à la base de données via l'outil PDO
	 * @return PDO
	 */
	private function connect() {
		try {
			$this->connect = new \PDO($this->driver . ':host=' . $this->url . ';dbname=' . $this->database . '', $this->login, $this->password);
			$this->connect->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}catch (\PDOException $e) {
			$this->connect = null;
			print_exception("PDOException", $e->getMessage());
		}
	}
	
	/**
	 * Retourne la liste des champs du formulaire
	 * @return ArrayObject
	 */
	private function getFieldList() {
		return $this->fieldList;
	}
	
	/**
	 * Retourne le champs dont le nom est passé en paramètre
	 * @param $name 	Nom du champs
	 * @return Field
	 */
	public function getField($name) {
		foreach ($this->fieldList as $field)
			if ($field->name == $name)
				return $field;
		return null;
	}
	
	/**
	 * Ajoute un champs au formulaire
	 * @param $name		Nom du champs dans la base de données
	 * @param $label	Libellé du champs
	 */
	public function addField($name, $label, $defaultValue = null) {
		
		$this->connect();
		$requete = $this->concreteDriver->getFieldType($name);
		$response = $this->prepareExecute($requete['requete'], $requete['parameters']);
		
		// On regarde s'il existe déjà un champs qui porte ce nom
		$field = $this->getField($name);
		
		// S'il existe, on crache une erreur
		if ($field instanceof Field) {
			throw new \Exception("Error exception : the field '".$name."' already defined !");
		// Sinon on le crée
		}else{
			// Si le champs est une clé primaire
			if ($this->concreteDriver->isIndex($name)) {
				$field = new Field($this, $this->table, $name, $label, $response[0]['data_type'], true);
				// Si la clé est autogénérée on ne l'affiche pas
				if ($this->concreteDriver->isAutoGenerated($name)) {
					$field->hidden = true;
				}
			}else{
				$field = new Field($this, $this->table, $name, $label, $response[0]['data_type'], false);
			}
			$field->defaultValue = $defaultValue;
			$this->fieldList->append($field);
		}
		
		return $field;
	}
	
	/**
	 * Retourne les informations concernant la base de données
	 */
	public function printInfoBDD() {
		print("__________Connexion__________<br/><br/>");
		print("URL : ".$this->url."<br/>");
		print("Type de base : ".$this->driver."<br/>");
		print("Langue : ".$this->language."<br/>");
		print("Encodage : ".$this->encoding."<br/>");
		print("Nom de la base : ".$this->database."<br/>");
		print("Nom de la table : ".$this->table."<br/>");
		print("Login : ".$this->login."<br/>");
		print("Password : ".$this->password."<br/>");
		print("_____________________________");
	}
	
	/**
	 * 
	 * @param unknown $requete
	 * @param unknown $parameters
	 */
	public function prepareExecute($requete, $parameters) {
		try {
			if ($this->connect) {
				$req = $this->connect->prepare($requete);
				$req->execute($parameters);
				$data = new \ArrayObject();
	
				if (substr($requete, 0, strlen("SELECT")) == "SELECT") {
					while ($row = $req->fetch()) {
						$data->append($row);
					}
				}
				$req->closeCursor();
			}
		}
		catch (\Exception $e) {
			$this->connect = null;
			$this->error = 'Exécution de la requete: ' . $e->getMessage();
		}
		
		return $data;
	}
	
	/**
	 * On va créer un driver selon le type de base de données
	 */
	private function setConcreteDriver() {
		if ($this->concreteDriver == null) {
			try {
				switch ($this->driver) {
					case "pgsql":
						$this->concreteDriver = new FormulaireDriverPGSQL();
						break;
					case "mysql":
						$this->concreteDriver = new FormulaireDriverMYSQL();
						break;
					default:
						//$this->concreteDriver = new FormulaireDriverDefault();
						break;
				}
			}catch (\Exception $e) {
				print("Exception : " . $e->getMessage());
			}
			$this->concreteDriver->setFormulaire($this);
		}
	}
	
	/**
	 * On change la table courante
	 * @param $table	Le nom de la nouvelle table
	 */
	public function setTable($table) {
		$this->table = $table;
	}
	
	/**
	 * Retourne le formulaire au format HTML
	 */
	public function show() {
		//$html = '<div id="alert-success-insert" class="alert-success" style="display:none;">INSERTION REUSSI</div>';
		//$html .= '<div id="alert-success-update" class="alert-success" style="display:none;">UPDATE REUSSI</div>';
		$html .= '<form id="'.$this->id.'" class="form-horizontal" method="post" action="">';
		foreach ($this->fieldList as $field)
			$html .= $field->showField();
		
		$html .= '<div class="form-group"><div class="col-sm-offset-4 col-sm-10 col-md-offset-4 col-md-10">';
		$html .= '<button type="submit" class="btn btn-primary" name="request" id="request" value="'.$this->mode.'">Envoyer</button></div>';
		$html .= '</div></div>';
		$html .= '<input type="hidden" id="formID" name="formID" value="'.$this->id.'">';
		$html .= '</form>';
		print($html);
		
		$this->check();
	}
	
	public function insert($values) {
		
		$request = "INSERT INTO " . $this->table . " (";
		$cptField = 0;
		$boolPosition = array();
		foreach ($this->fieldList as $field) {
			$request .= $field->name . ", ";

			if(stristr($field->type,"boolean") || stristr($field->type,"tinyint")){
				array_push($boolPosition, $cptField);
			}
			
			$cptField++;
		}
		
		$request = substr($request, 0, strlen($request)-2);
		$request .= ") VALUES (";
		
		for($i = 0; $i < sizeof($values); $i++){
			if(in_array($i, $boolPosition))
				$request .= $values[$i].", ";
			else
				$request .= "'".str_replace("'", "''", $values[$i])."', ";
		}
			
		$request = substr($request, 0, strlen($request)-2);
		$request .= ");";

		//file_put_contents("/home/jonathan/Workspace/workspacePJI/formulaire/temp/request.txt",$request);
		
		return $this->connect->exec($request);
		
	}

	public function update($values) {

		$request = "UPDATE " . $this->table . " SET ";
		$requestCondition = " WHERE ";
		$cptField = 0;

		foreach ($this->fieldList as $field) {
			if(!$field->primaryKey) {
				if(stristr($field->type,"boolean") || stristr($field->type,"tinyint"))
					$request .= $field->name . " = " . $values[$cptField] . ", ";
				else
					$request .= $field->name . " = " . "'".str_replace("'", "''", $values[$cptField])."', ";
			} else {
				$requestCondition .= $field->name . " = " . "'".str_replace("'", "''", $values[$cptField]) ."', ";
			}
			$cptField++;
		}

		$request = substr($request, 0, strlen($request)-2);
		$requestCondition = substr($requestCondition, 0, strlen($requestCondition)-2);
		$request .= $requestCondition  . ";";

		//file_put_contents("/home/jonathan/Workspace/workspacePJI/formulaire/temp/update.txt",$request);
		return $this->connect->exec($request);
	}
	
	public function setHidden($fieldname, $bool) {
		$this->getField($fieldname)->hidden = $bool;
		if ($bool) { $this->getField($fieldname)->required = false; }
	}
	
	public function setRequired($fieldname, $bool) {
		if (!$this->getField($fieldname)->primaryKey) { $this->getField($fieldname)->required = $bool; }
		if ($bool) { $this->getField($fieldname)->hidden = false; }
	}
	
	public function setAllRequired($bool) {
		foreach ($this->fieldList as $field) {
			$this->setRequired($field->name, $bool);
		}
	}
	
	public function setDisabled($fieldname, $bool) {
		$this->getField($fieldname)->disabled = $bool;
	}
	
	public function loadValuesFromIndex($id_column, $id_value) {
		$fields = "";
		$tables = "";
		foreach ($this->fieldList as $field) {
			$fields .= $field->table . "." . $field->name . ", ";
			
			if(strpos($tables,$field->table.", ") === false)
				$tables .= $field->table . ", ";
		}
		$fields = substr($fields, 0, strlen($fields)-2);
		$tables = substr($tables, 0, strlen($tables)-2);
		
		$request = "SELECT ".$fields." FROM ".$tables." WHERE $id_column = '".$id_value."';";

		//file_put_contents("/home/jonathan/Workspace/workspacePJI/formulaire/temp/load.txt",$request);

		$req = $this->connect->query($request);
		
// 		if ($req->rowCount() == 0)
		
		$row = $req->fetch();
		
		foreach ($this->fieldList as $field) {
			$this->getField($field->name)->defaultValue = $row[$field->name];
		}
		
	}
	
	public function isUpdateForm($id_column, $id_value) {
		$this->mode = "update";
		$this->addField($id_column,"ID for update", $id_value);
	}

	public function __sleep() {
		return array(
			'url',
			'login',
			'password',
			'database',
			'table',
			'driver',
			'language',
			'encoding',
			'mode',
			'fieldList'
		);
	}
	
	public function __wakeup() {
		$this->connect();
	}
	
	public function check() {
		file_put_contents("./temp/".$this->id, serialize($this));
	}
	
}
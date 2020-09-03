<?php

class Storage {

	private $className;
	private $classLang;
	private $classData = array();
	private $classJSON = "storage";

	public function __construct() {
		$this->initClass();
		$this->loadJSON();
	}

	final protected function initClass() {
		$this->classLang = (session("lang")) ? session("lang") : "ru";
		$this->className = get_class($this);
		$this->classJSON = __DIR__ . "/$this->classJSON/" . strtolower($this->className) . ".$this->classLang.json";
		if (!file_exists(dirname($this->classJSON))) mkdir(dirname($this->classJSON), 0755, true);
		if (!file_exists($this->classJSON)) $this->saveJSON();
	}

	final protected function loadJSON() {
		$json = file_get_contents($this->classJSON);
		$this->classData = json_decode($json, JSON_OBJECT_AS_ARRAY);
	}

	final protected function saveJSON() {
		$json = json_encode($this->classData, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		if (!file_put_contents($this->classJSON, $json, LOCK_EX)) exit("JSON_WRITE_ERROR");
	}

	final public function selectData($key = false) {
		if ($key == false) return $this->classData;
		else if (array_key_exists($key, $this->classData)) return $this->classData[$key];
		else return false;
	}

	final public function insertData($key, $value) {
		$this->classData[$key] = $value;
		$this->saveJSON();
	}

	final public function deleteData($key) {
		unset($this->classData[$key]);
		$this->saveJSON();
	}

}

class Editable extends Storage {

	final public function show($key, $editable = true) {
		$request = $GLOBALS["request"];
		$value = $this->selectData($key);

		if ($editable == true) {

			if (session("admin") == "true") $editable = " contenteditable=\"false\"";
			else {
				$editable = null;
				ob_start();
				eval("\$request = \"$request\";");
				eval("?>" . $value);
				$value = ob_get_clean();
			}

			$html = "<data id=\"$key\"$editable>$value</data>\n";
			return $html;

		} else {
			ob_start();
			eval("?>" . $value);
			$value = ob_get_clean();
			return $value;
		}
	}
}

$data = new Editable();

if (isset($_POST["key"]) && session("admin") == "true") {

	if ($_POST["value"] == null) $data->deleteData($_POST["key"]);
	else {
		$value = $_POST["value"];
		$data->insertData($_POST["key"], $value);
	}

	exit("200 OK");
}

if (session("admin") == "true") $html .= "<script>const editable = true</script>";
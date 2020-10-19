<?php

// Sitar Web Engine
// Dmitriy Pavlov (@dmitriypavlov)
// https://github.com/dmitriypavlov/sitar-engine

define("SITAR", "1.1");

// DEBUGGER
// https://github.com/nette/tracy

require_once(__DIR__ . "/component/tracy.phar");

use Tracy\Debugger;
Debugger::$strictMode = true;
// Debugger::enable(Debugger::DETECT, __DIR__ . "/storage/log");
// Debugger::enable(Debugger::DEVELOPMENT);

// client request

$request = urldecode($_SERVER["REQUEST_URI"]);
$request = explode("?", $request)[0];

if ($request != "/") $request = rtrim($request, "/");

// ignore existing file

if (file_exists(__DIR__ . $request) && is_file(__DIR__ . $request)) return false;

// redirect not [/][alphanumeric][-][.]

if (!preg_match("/^[\/\w\-\.]+$/", $request)) {

	// header("Location: /");
	header("HTTP/1.1 404 Not Found");
	exit();
}

$html = null; // epilogue

// load modules

require_once(__DIR__ . "/session.php");

// admin logout

if ($request == "/logout") {

	session("*", -1);
	header("Location: /");
	exit();
}

// load modules

require_once(__DIR__ . "/editable.php");
require_once(__DIR__ . "/email.php");

// search template

$path = null;
$file = "template.html";

foreach (explode("/", $request) as $subpath) {

	if ($subpath != null) $path .= "/$subpath";
	if (file_exists(__DIR__ . "$path/$file")) $template = "$path/$file";
}

if (isset($template)) require_once(__DIR__ . $template);
else exit("TEMPLATE_READ_ERROR");

// NOTE: debug
// $html .= "<script>console.log(" . json_encode($_SESSION). ")</script>";

echo $html;
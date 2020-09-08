<?php

// start session

$name = "sitar";
$time = 86400; // session lifetime seconds

ini_set("session.cookie_lifetime", $time);
ini_set("session.gc_maxlifetime", $time);

session_name($name);
session_start();

function session($key, $value = null) {

	// get session value

	if ($value === null) {

		if (key_exists($key, $_SESSION)) return $_SESSION[$key];
		else return false;
	}

	// set session value

	if ($value != null && $value != -1) {

		$_SESSION[$key] = $value;
		return true;
	}

	// unset session value

	if ($value === -1) {

		// unset all

		if ($key == "*") {

			$_SESSION = [];
			return true;
		}

		// unset one

		else {

			unset($_SESSION[$key]);
			return true;
		}
	}
}
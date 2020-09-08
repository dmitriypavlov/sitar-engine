<?php

$to = "admin@sitar.one"; // $to = "pavlov@kubik.com.ua";
$subject = "Комментарий посетителя";

if (isset($_POST["name"]) && $_POST["name"] != "" &&
	isset($_POST["email"]) && $_POST["email"] != "" &&
	isset($_POST["phone"]) && $_POST["phone"] != "" &&
	isset($_POST["comment"]) && $_POST["comment"] != "")
	
{	
	$headers = "From: " . $_POST["email"] . PHP_EOL;
	$headers .= "Reply-To: " . $_POST["email"] . PHP_EOL;
	$headers .= "Content-Type: text/plain; charset=utf-8";

	$message = "Имя: " . $_POST["name"] . PHP_EOL;
	$message .= "E-mail: " . $_POST["email"] . PHP_EOL;
	$message .= "Телефон: " . $_POST["phone"] . PHP_EOL;
	$message .= "IP: " . $_SERVER["REMOTE_ADDR"] . PHP_EOL . PHP_EOL;
	$message .= $_POST["comment"] . PHP_EOL;

	if (mail($to, $subject, $message, $headers)) $html .= "<script>alert('" . $data->show("mail-success", false) . "')</script>";
	else $html .= "<script>alert('" . $data->show("mail-failed", false) . "')</script>";
}
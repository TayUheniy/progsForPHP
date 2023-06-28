<?php
require_once __DIR__.'\vendor\autoload.php';
session_start();

$ob = new Parse;
 foreach(['marks','model'] as $parameterName) {
	$$parameterName = isset($_REQUEST[$parameterName]) ? trim($_REQUEST[$parameterName]) : '';
}
echo $ob->parsing();
<?php
//Массив для проверки всех ссылок по заданию
$arrayLinkedArr = array(
	"http://http.ru/folder/subfolder/../././script.php?var1=val1&var2=val2",
	"https://http.google.com/folder//././?var1=val1&var2=val2",
	"ftp://mail.ru/?hello=world&url=https://http.google.com/folder//././?var1=val1&var2=val2",
	"mail.ru/?hello=world&url=https://http.google.com/folder//././?var1=val1&var2=val2",
	"?mail=ru",
	"domain2.zone:8080/folder/subfolder/../././../asdss/.././//////../myfolder/script.php?var1=val1&var2=val2",
	"http://dom.dom.domain2.com:8080/folder/subfolder/./myfolder/script.php?var1=val1&var2=val2?var1=val1&var2=val2",
);

//Вывод на экран массива разбора ссылок
foreach ($arrayLinkedArr as $key => $val) {
	$arraysOut = myUrlParse($arrayLinkedArr[$key]);
	var_dump($arraysOut);
}

/**Функция поиска реального пути.
*
*
*@param type $__path Введенный путь.
*@return string
*/
function searchDirectoryPath($__path)
{
	$finalPath      = "";
	$arraySubPath   = explode("/", $__path);
	$countOfSubPath = count($arraySubPath);
	
	//Проверка на то, что за элемент нам попался и удаление либо добавление пути
	for ($indexOfSubPath = 0; $indexOfSubPath < $countOfSubPath; ++$indexOfSubPath) {
		if (strlen($arraySubPath[$indexOfSubPath]) === 0 || $arraySubPath[$indexOfSubPath] === "." || ($arraySubPath[$indexOfSubPath] === ".." && $indexOfSubPath === 0)) {
			array_splice($arraySubPath, $indexOfSubPath, 1);
			--$indexOfSubPath;
			$countOfSubPath = count($arraySubPath);
		}
		
		//Если встретились 2 подряд идущие точки в массиве разбора пути, то значит поднимается на уровень вверх нашего пути
		elseif ($arraySubPath[$indexOfSubPath] === ".." && $indexOfSubPath >= 1) {
			array_splice($arraySubPath, $indexOfSubPath, 1);
			--$indexOfSubPath; 
			array_splice($arraySubPath, $indexOfSubPath, 1);
			--$indexOfSubPath;
			$countOfSubPath = count($arraySubPath);
		}
	}
	
	//Итоговый путь
	$finalPath  = implode("/", $arraySubPath);
	$finalPath .= "/";
	
	
	//Вывод итогового пути на экран
	return $finalPath;
}

/**Функция разбива ссылки на части.
* 
* @param type $link Введенная ссылка.
* @return string
*/
function myUrlParse($link)
{
	$isError            = "false";
	$isPHP = "false";
	$searchParameters   = strstr($link, "?");
	
	//Поиск параметров
	if (strlen($searchParameters) !== 0) {
		$newLink           = strstr($link, "?", true);
		$searchParameters  = substr($searchParameters, 1);
		$severalParameters = explode("&", $searchParameters);
		
		//Проверка на то существует ли несколько параметров и закидывание в итоговый массив параметров
		if (isset($severalParameters)) {
			foreach ($severalParameters as $key => $value) {
				$totals        = explode("=", $value, 2);
				$totalArrays[] = $totals;
			}
		
			//Заполнение итогового массива параметров
			$countTotalArrays = count($totalArrays);
			for ($indexEvaluationParameters = 0; $indexEvaluationParameters < $countTotalArrays; ++$indexEvaluationParameters) {
				$evaluationParameters[$totalArrays[$indexEvaluationParameters][0]] = $totalArrays[$indexEvaluationParameters][1];
			}
		} 
		else
		{
			foreach ($searchParameters as $key => $value) {
				$totalArrays = explode("=", $value);
			}
			
			//Заполнение итогового массива параметров
			for ($indexEvaluationParameters = 0, $indexTotalArrays = 1; $indexEvaluationParameters < $countTotalArrays; $indexEvaluationParameters += 2, $indexTotalArrays += 2) {
				$evaluationParameters[$totalArrays[$indexEvaluationParameters]] = $totalArrays[$indexTotalArrays];
			}
		}
	}
	else {
		$evaluationParameters = "false";
		$newLink = $link;
	}
	
	//Поиск пртокола
	$protocol = strstr($newLink, "://", true);
	if (strlen($protocol) === 0) { 
		$protocol = "false"; 
	} 
	else { 
		$newLink = strstr($newLink, "://");
		$newLink = substr($newLink, 3);
	}
	
	//Поиск домена
	if ($protocol !== "false") {
		$domain  = strstr($newLink, "/", true);
		$newLink = strstr($newLink, "/");
		$newLink = substr($newLink, 1);
		
		//Если строка домена пуста то заполняем ее как false
		if (strlen($domain) === 0) {
			$domain = "false";
		}
		
		//Поиск порта
		$port = strstr($domain, ":");
		if (strlen($port) !== 0) {
			$port   = substr($port, 1);
			$domain = strstr($domain, ":", true);
		} 
		else { 
			$port = "false"; 
		}
		
		//Проверка на ошибку
		$subDomain = explode(".", $domain);
		if (count($subDomain) > 5) {
			$isError = "true";
		}
		
		//Заполнение зоны и домена 2 уровня
		$zone = $subDomain[count($subDomain) - 1];
		if (count($subDomain) === 1) { 
			$twoLevelDomain = "false"; 
		} 
		else {
			$twoLevelDomain = $subDomain[count($subDomain) - 2].".".$zone; 
		}
	}
	else 
	{
	    $domain = "false";
	}
	
	//Если нет домена то и порт и зона и домена второго уровня тоже не будет
	if ($domain === "false") {
		$zone           = "false";
		$twoLevelDomain = "false";
		$port			= "false";
	}

	//Поиск названия скрипта
	$scriptName = strrchr($newLink, "/");
	$scriptName = substr($scriptName, 1);
	if (strlen($scriptName) === 0 && $evaluationParameters !== "false") {
		$scriptName         = "index.php"; 
		$isPHP = "true"; 
		$rawFolder          = $newLink;
	}
	
	//Если нету названия скрипта то заполняем его как false
	elseif (strlen($scriptName) === 0 && $evaluationParameters === "false") {
		$scriptName = "false"; 
		$rawFolder  = $newLink;
	}
	else {
		
		//Проверка на расширение php
		$subScriptName = explode(".", $scriptName); 
		if ($subScriptName[count($subScriptName) - 1] === "php") {
			$isPHP = "true";
		} 
		
		//Поиск изначального пути	
		$rawFolder = substr($newLink, 0, strlen($newLink) - strlen($scriptName));
	}
	if (strlen($rawFolder) === 0 || $rawFolder === "false") {
		$rawFolder = "false";
		$folder    = "false"; 
	} 
	else {
		$folder = searchDirectoryPath($rawFolder); 
	}
	
	//Если скрипта нету то и пути к скрипту тоже нет
	if ($scriptName === "false") {
		$scriptPath = "false";
	}
	elseif ($folder === "false") {
		$scriptPath = $scriptName;
	} 
	else {
		$scriptPath = $folder.$scriptName;
	}
	
	//Заполнение итогового массива разбора ссылки
	$arrayParseArr             = array();
	$arrayParseArr["protocol"] = $protocol;
	
	//Заполнение домена и его частей
	$arrayParseArr["domain"]         = $domain;
	$arrayParseArr["zone"]           = $zone;
	$arrayParseArr["2_level_domain"] = $twoLevelDomain;
	$arrayParseArr["port"]           = $port;
	
	//Заполнение путей
	$arrayParseArr["raw_folder"]  = $rawFolder;
	$arrayParseArr["folder"]      = $folder;
	$arrayParseArr["script_path"] = $scriptPath;
	$arrayParseArr["script_name"] = $scriptName;
	
	//Заполнение ошибок и параметров
	$arrayParseArr["is_php"]     = $isPHP;
	$arrayParseArr["parameters"] = $evaluationParameters;
	$arrayParseArr["is_error"]   = $isError;
	return $arrayParseArr;
}

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

//Вывод результатов на экран.
foreach ($arrayLinkedArr as $key => $val) {
	$arraysOut = my_url_pcre_parse($arrayLinkedArr[$key]);
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
	$arraySubPath   = preg_split("#(/)#ix", $__path);
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
* @param type $link
* @return type
*/
function my_url_pcre_parse($link)
{
	preg_match_all(
		"'#
		(?: 
			(?:
				(?:^(\w*)	#Протокол
				(?:://?))?	#Символ ограничитель протокола от всего остального
			)
			(?:
				((?:.*?)\/)	#Домен
			) 
		)?
			(.*?)			#Все символы до последнего слеша(Абсолютный путь)
			((?:[^/?])*)\?	#Последний элемент ссылки от слеша до знака вопроса
			(.*)			#Параметры
		'xis", $link, $parsedLink);
	$resultingParsedLink['protocol'] = $parsedLink[1][0];
	
	//Проверка на протокол, с помощью которой определяется, что будет являться абсолютным путем.
	if ($resultingParsedLink['protocol'] === '') {
		$resultingParsedLink['raw_folder'] = $parsedLink[2][0].$parsedLink[3][0];
	} 
	else {
		$resultingParsedLink['raw_folder'] = $parsedLink[3][0];
	}
	
	//Вычисление реального пути.
	if ($resultingParsedLink['raw_folder'] !== "") {
		$resultingParsedLink['folder'] = searchDirectoryPath($resultingParsedLink['raw_folder']);
	}
	else {
		$resultingParsedLink['folder']	   = 'false';
		$resultingParsedLink['raw_folder'] = 'false';
	}
	
	//Определение имя скрипта.
	if ($parsedLink[4][0] !== "") {
		$resultingParsedLink['script_name'] = $parsedLink[4][0];
	} 
	else {
		if ($parsedLink[5][0] !== "") {
			$resultingParsedLink['script_name'] = 'index.php'; 
		} 
		else {
			$resultingParsedLink['script_name'] = 'false';
		}
	}
	
	//Определение папки скрипта.
	if ($resultingParsedLink['script_name'] !== 'false' && $resultingParsedLink['folder'] !== 'false') {
		$resultingParsedLink['script_path'] = $resultingParsedLink['folder'].$resultingParsedLink['script_name'];
	} 
	else {
		$resultingParsedLink['script_path'] = $resultingParsedLink['script_name'];
	}
	
	//Если протокол пустой то до абсолютного пути все пусто.
	if ($resultingParsedLink['protocol'] === '') {
		$resultingParsedLink['protocol']       = 'false';
		$resultingParsedLink['domain']         = 'false';
		$resultingParsedLink['zone']           = 'false';
		$resultingParsedLink['2_level_domain'] = 'false';
		$resultingParsedLink['2_level_domain'] = 'false';
		$resultingParsedLink['port']           = 'false';
		$resultingParsedLink['is_error']       = 'false';
	} 
	else {
		
		//Поиск домена и порта
		$port = preg_split("#(:|/)#", $parsedLink[2][0]);
		array_pop($port);
		if ($port[0] !== "") {
			$resultingParsedLink['domain'] = $port[0];
		} 
		else{
			$resultingParsedLink['domain'] = 'false';
		}
		if (isset($port[1])) {
			$resultingParsedLink['port'] = $port[1];
		} 
		else {
			$resultingParsedLink['port'] = 'false';
		}
		
		//Разделение на поддомены.
		$subDomain                   = preg_split("#(\.)#", $port[0]);
		$resultingParsedLink['zone'] = $subDomain[count($subDomain) - 1];
		if (count($subDomain) > 5) {
			$resultingParsedLink['is_error'] = 'true';
		}
		else {
			$resultingParsedLink['is_error'] = 'false';
		} 
		
		//Поиск домена 2 уровня.
		if (count($subDomain) >= 2) {
			$resultingParsedLink['2_level_domain'] = $subDomain[count($subDomain) - 2].".".$subDomain[count($subDomain) - 1];
		} 
		else {
			$resultingParsedLink['2_level_domain'] = 'false';
		}
	}
	
	//Определение расширения скрипта.
	if ($resultingParsedLink['script_name'] !== 'false') {
		$php = preg_split("#(\.)#", $resultingParsedLink['script_name']);
		if (isset($php[1]) && $php[1] === 'php') {
			$resultingParsedLink['is_php'] = 'true';
		} 
		else {
			$resultingParsedLink['is_php'] = 'false';
		}
	}
	else {
		$resultingParsedLink['is_php'] = 'false';
	}
	
	//Разделение параметров.
	$parameter = preg_split("#(&)#", $parsedLink[5][0]);
	foreach ($parameter as $indexOfParameter => $valueOfParameter) {
		$parsedParameters[] = preg_split("#(=)#", $valueOfParameter, 2);
	}
	foreach ($parsedParameters as $indexOfParameter => $valueOfParameter) {
		$resultParameters[$parsedParameters[$indexOfParameter][0]] = $parsedParameters[$indexOfParameter][1];
	}
	$resultingParsedLink['parameters'] = $resultParameters;
	return $resultingParsedLink;
}

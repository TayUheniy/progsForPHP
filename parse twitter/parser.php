<?php

//Библиотеки для работы с запросами и с html кодом.
include_once ('simple_html_dom.php');
include_once ('curl_query.php');



//Вход в базу данных и установка кодировки.
$db = new mysqli("localhost", "bdAdmin", "localhost", "users" );
mysqli_set_charset($db, "utf8");

//Создание таблицы с нужными столбцами.
$sql = "CREATE TABLE IF NOT EXISTS `parse` (
  `Имя` varchar(255) NOT NULL,
  `Имя-ссылка` varchar(255) NOT NULL,
  `Твитов` bigint NOT NULL,
  `Читаемые` bigint NOT NULL,
  `Читатели` bigint NOT NULL,
  `Нравится` bigint NOT NULL,
  `Статус` varchar(255) NOT NULL,
  `Год регистрации` bigint NOT NULL,
  `Активничают` varchar(255) NOT NULL
)";      
$db->query($sql);

//Парсинг и заполнение таблицы в базе данных.
$countOfTwits   = "Твитов";
$countOfRead    = "Читаемые";
$countOfReaders = "Читатели";
$countOfLike    = "Нравится";
$status         = "Статус";
$name           = "Имя";
$nameOfUrl      = "Имя-ссылка";
$yearsRegistr   = "Год регистрации";
$activeUser     = "Активничает на странице";
$result         = parse_all("https://twitter.com/billgates");
var_dump($result);
for ($index = 0; $index < count($result);++$index) {
	$nowUser = $result[$index];
	settype($nowUser[$countOfTwits],"int");
	settype($nowUser[$countOfRead],"int");
	settype($nowUser[$countOfReaders],"int");
	settype($nowUser[$countOfLike],"int");
	settype($nowUser[$yearsRegistr],"int");
	$query = "INSERT INTO `parse` (`Имя`, `Имя-ссылка`, `Твитов`, `Читаемые`, `Читатели`, `Нравится`, `Статус`, `Год регистрации`, `Активничают`) VALUES (";
	$query .= "'$nowUser[$name]'" .","."'$nowUser[$nameOfUrl]'".",".$nowUser[$countOfTwits] .","."'$nowUser[$countOfRead]'" .","."'$nowUser[$countOfReaders]'" .","."'$nowUser[$countOfLike]'" .","."'$nowUser[$status]'" .","."'$nowUser[$yearsRegistr]'".","."'$nowUser[$activeUser]'".")";
	$db->query($query);
}

//Функция поиска подстроки внутри строки
function searchInSite($nameСlass, $finishСlass, $html) {
	$resultStr = strstr($html,$nameСlass);
	$resultStr = strstr($resultStr,$finishСlass,true);
	$resultStr = substr($resultStr, strlen($nameСlass));
	return $resultStr;
}

//Парсинг одной ссылки
function parse($url){
	static $arrayOfSite = array();
	$arrayOfSite[]=$url;
	static $count = 1;
	$max = 20;
	$html       = file_get_contents($url);
	$htmlObject = str_get_html($html);
	$infoUser   =  $htmlObject->find('.ProfileNav-value');
	$arrayOfInfoForUser = array("Твитов", "Читаемые", "Читатели", "Нравится");
		for ($index = 0; $index < count($infoUser)-1; ++$index ) {
			if (isset($infoUser[$index]) && isset($infoUser[$index]->attr['data-count']) && isset($arrayOfInfoForUser[$index])) {
				$allInfoOfUser[$arrayOfInfoForUser[$index]] = $infoUser[$index]->attr['data-count'];
			}
		}
	$nameUser = searchInSite('ProfileHeaderCard-nameLink u-textInheritColor js-nav">','<',$htmlObject);
	$urlUser  = searchInSite('u-linkComplex-target">','<',$htmlObject);
	if (searchInSite('class="ProfileHeaderCard-bio u-dir" dir="ltr">','<',$htmlObject)!== null ) {
		$status                  = searchInSite('class="ProfileHeaderCard-bio u-dir" dir="ltr">','<',$htmlObject);
		$allInfoOfUser['Статус'] = $status;
	}
	$dateRegistr             = searchInSite('Дата регистрации:', '<', $htmlObject);
	$allInfoOfUser['Имя']    = $nameUser;
	$allInfoOfUser['Ссылка'] = $urlUser;
	preg_match('#(\d+)#', $dateRegistr, $yearsRegistr);
	$allInfoOfUser['Год регистрации'] = $yearsRegistr[1];
	preg_match_all('#href="/(\w+)/status(?:.*?)"#ixs', $html, $urlUserActive);
	array_shift($urlUserActive);
	$userActive = implode(";",$urlUserActive[0]);
	$AllUserStr = "[:|||:]".implode("[:||:]", $allInfoOfUser);
	$countAllUserStr = count($urlUserActive[0]);
	$infoNewUser     = "";
	
	for ($index = 0; $index < $countAllUserStr; ++$index) {
		if ($count === $max) {
			break;
		}
		if (!in_array("https://twitter.com/".$urlUserActive[0][$index], $arrayOfSite) && $urlUserActive[0][$index] !== $allInfoOfUser['Ссылка']) {
			++$count;
			$arrayOfSite[] = "https://twitter.com/".$urlUserActive[0][$index];
			$infoNewUser .= parse("https://twitter.com/".$urlUserActive[0][$index]);
			
		}
	}
	$resultStrOfUser = $AllUserStr;
	if (isset($userActive)) {
		$resultStrOfUser .= "[:||:]".$userActive;
	}
	if (isset($infoNewUser)) {
		$resultStrOfUser .= $infoNewUser;
	}
	return $resultStrOfUser;
}

//Рекурсивный парсинг сайтов, так как внутри каждого сайта могут оставлять твиты люди, которых нужно будет запарсить.
function parse_all($str) {
	$strOfUser = parse($str);
	$arrayUser = explode("[:|||:]", $strOfUser);
	array_shift($arrayUser);
	$arrayUser = array_unique($arrayUser);
	foreach ($arrayUser as $key => $value) {
		$finishArrayUser[$key] = explode ("[:||:]", $value);
	}
	$arrayOfInfoUser = array("Твитов", "Читаемые", "Читатели", "Нравится","Статус","Имя", "Имя-ссылка", "Год регистрации", "Активничает на странице");
	for ($index = 0; $index < count($finishArrayUser); ++$index) {
		for ($secondIndex = 0; $secondIndex < 9; ++$secondIndex) {
			$result[$index][$arrayOfInfoUser[$secondIndex]] = $finishArrayUser[$index][$secondIndex];
		}
	}
	return $result;
}
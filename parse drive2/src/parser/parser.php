<?php


class Parse 
{
/**Функция отправки curl запросов.
* 
* @param type $url
* @param type $referer
* @return type
*/
public function curl_get($url, $referer = 'https://yandex.ru') {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
	curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

/**Функция парсит название марок автомобилей с сайта.
* 
* @return type
*/
public function parse_cars() {
	$html = $this->curl_get('https://www.drive2.ru');
	preg_match_all('#"c-link" href="/cars(.*?)"(?:.*?)>(.*?)</a>#ius', $html, $cars);
	array_shift($cars);
	for ($indexOfCars = 0; $indexOfCars < count($cars[0]); ++$indexOfCars) {
		$carsArr[$cars[1][$indexOfCars]] = $cars[0][$indexOfCars];
	}
	return $carsArr;
}

/**Функия парсит название моделей автомобилей с сайта.
* 
* @param type $markOfCars
* @return type
*/
public function parse_model_cars($markOfCars) {
	$url = 'https://www.drive2.ru'.'/cars'.$markOfCars;
	$html = $this->curl_get($url);
	$pattern = '#href="/cars'.$markOfCars.'(.*?)"(?:.*?)>(.*?)</a>#ius';
	preg_match_all($pattern, $html, $carsModels);
	array_shift($carsModels);
	for ($indexCarsModels = 0; $indexCarsModels < count($carsModels[0]); ++$indexCarsModels) {
		$carsModelsArr[$carsModels[1][$indexCarsModels]] = $carsModels[0][$indexCarsModels];
	}
	return $carsModelsArr;
}

/**Функция находит ссылки отзывов пользователей на странице
* 
* @param type $urlcars
* @return type
*/
public function parse_name_user($urlcars) {
	$url = 'https://www.drive2.ru'.'/cars'.$urlcars;
	$html = $this->curl_get($url);
	$pattern = '#"c-link(?:.*?)" href="/r(.*?)"(?:.*?)>(.*?)</a>#is';
	preg_match_all($pattern, $html, $reviewUsersUrl);
	array_shift($reviewUsersUrl);
	for ($indexOfReviewUsersUrl = 0; $indexOfReviewUsersUrl < count($reviewUsersUrl[0])-1; ++$indexOfReviewUsersUrl) {
		$reviewUsersUrlArr[$reviewUsersUrl[1][$indexOfReviewUsersUrl]] = $reviewUsersUrl[0][$indexOfReviewUsersUrl];
	}
	return $reviewUsersUrlArr;
}

/**Функция парсит отзывы пользователей.
* 
* @param type $urlReview
* @param type $carMarks
* @return string
*/
function review($urlReview, $carMarks) {
	$url = 'https://www.drive2.ru'.'/r'.$urlReview;
	$html = $this->curl_get($url);
	preg_match_all('#<div\sdata-slot="translate(?:.*?)>(.*?)</div>#ius', $html, $mastches);
	preg_match_all('#<p>(.*?)</p>#ius', $mastches[1][0], $reviewArr);
	preg_match('#"c-link(?:.*?)" href="/users/(.*?)/"#is', $html, $userName);
	for ($indexReview = 0; $indexReview< count($reviewArr[1]); ++$indexReview) {
		$reviewArrResult[] = preg_replace('#<br />#is', " ", $reviewArr[1][$indexReview]);
	};
		$reviewResult = implode(".",$reviewArrResult);
	preg_match_all('#<li>(.*?)</li>#is', $html, $passData);
	for ($indexPassData = 0; $indexPassData < count($passData[1]); ++$indexPassData) {
		$passArr[] = strip_tags($passData[1][$indexPassData]);
	};
	$passResult   = implode("; ",$passArr);
	$review       = strip_tags($reviewResult).". \n Паспортные данные: \n".strip_tags($passResult).".";
	$resultArr[1] = $userName[1];
	$resultArr[0] = $carMarks;
	$resultArr[2] = $review;
	return $resultArr;
}

/**Html-код заголовков.
* 
* @return string
*/
function headlines()
{
	return "<head><link href='src/parser/style.css' rel='stylesheet' type='text/css' /> </head><body>";
}

/**Html-код формы.
 * 
 * @return string
 */
function form() {
	return "<div class = 'form'> Поиск информации о любом автомобиле <br/>";
}

/**Итоговый HTML код.
 * 
 * @return string
 */
function result_form() {
	$form = "";
if (isset($_POST["action"])) {
	if ($_POST["action"] === "Выгрузить базу данных") {
		if (file_exists('analytics.sqlite')) {
			$db = new SQLite3('analytics.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->query('CREATE TABLE IF NOT EXISTS "visits" (
					"Автомобиль" VARCHAR,
					"Имя пользователя" VARCHAR,
					"Отзыв" VARCHAR
					)');
				$sql = "SELECT * FROM visits ORDER BY 'Автомобиль', 'Имя пользователя', 'Отзыв'";
				$rez = $db->query($sql);
				while ($row = $rez->fetchArray()) {
					$str[1] = $row['Имя пользователя'];
					$str[0] = $row['Автомобиль'];
					$str[2] = $row['Отзыв'];
					$result[] = $str;
				}
				$str = "";
					foreach ($result as $indexResult => $value) {
						$str .= "<div class ='cars'><div class ='car'>Имя пользователя: ".$result[$indexResult][1]. "</div><div class ='car'> Автомобиль: ".$result[$indexResult][0]."</div><div class ='car'> Отзыв: ".$result[$indexResult][2]."</div></div><br/><br/>";
					}
				return  $str."</body>";
		}
		else{
			return "База данных не была создана";
		}
	}
	
	if ($_POST["action"] === "Информация всех моделей") {
		$db = new SQLite3('analytics.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->query('CREATE TABLE IF NOT EXISTS "visits" (
					"Автомобиль" VARCHAR,
					"Имя пользователя" VARCHAR,
					"Отзыв" VARCHAR
					)');
				$sql = "SELECT * FROM visits ORDER BY 'Автомобиль', 'Имя пользователя', 'Отзыв'";
				$rez = $db->query($sql);
				while ($row = $rez->fetchArray()) {
					$str[1] = $row['Имя пользователя'];
					$str[0] = $row['Автомобиль'];
					$str[2] = $row['Отзыв'];
					$result[] = $str;
				}
				unset($db);
				unset($rez);
				unlink('analytics.sqlite');
		
		$modelArr = $this->parse_model_cars($_POST["marks"]);
		foreach ($modelArr as $indexmodel => $valuemodel) {
			$nameUserArr = $this->parse_name_user($_POST["marks"].$valuemodel);
			foreach ($nameUserArr as $nameUserIndex => $nameUser) {
			$result[] = $this->review($nameUser, $nameUserIndex);
		}
		}
		$db = new SQLite3('analytics.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->query('CREATE TABLE IF NOT EXISTS "visits" (
					"Автомобиль" VARCHAR,
					"Имя пользователя" VARCHAR,
					"Отзыв" VARCHAR
					)');
				for($index = 0; $index<count($result); ++$index) {
					$equal = false;
					for ($newindex = $index+1; $newindex<count($result);  ++ $newindex ) {
						if ($result[$index][2] === $result[$newindex][2]) {
							$equal = true;
						}
					}
					if ($equal === false) {
						$resultArr[] = $result[$index];
					}
				}
				$this->write_in_sql($resultArr , $db);
				$str = "";
					foreach ($resultArr as $indexResult => $value) {
						$str .= "<div class ='cars'><div class ='car'>Имя пользователя: ".$resultArr[$indexResult][1]. "</div><div class ='car'> Автомобиль: ".$resultArr[$indexResult][0]."</div><div class ='car'> Отзыв: ".$resultArr[$indexResult][2]."</div></div><br/><br/>";
					}
				return  $str."</body>";
			
	}
}	

//Информация обо всех марках
if (isset($_POST["action"])) {
		if ($_POST["action"] === "Информация обо всех марках") {
			$str = "";
			$db = new SQLite3('analytics.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->query('CREATE TABLE IF NOT EXISTS "visits" (
					"Автомобиль" VARCHAR,
					"Имя пользователя" VARCHAR,
					"Отзыв" VARCHAR
					)');
			$marksArr = $this->parse_cars();
			foreach ($marksArr as $indexMarksArr => $valueMarksArr) {
					if ($valueMarksArr === "/other/" || $valueMarksArr === "/selfmade/") {
							$nameUserArr = $this->parse_name_user($valueMarksArr);
							} 
							else {
									$modelArr = $this->parse_model_cars($valueMarksArr);	
									foreach ($modelArr as $indexmodel => $valuemodel) {
										$nameUserArr = $this->parse_name_user($valueMarksArr.$valuemodel);
									}
								}
					foreach ($nameUserArr as $nameUserIndex => $nameUser) {
							$result[] = $this->review($nameUser, $nameUserIndex);
						}
					}
				
				$this->write_in_sql($result, $db);
					foreach ($result as $indexResult => $value) {
						$str .= "<div class ='cars'><div class ='car'>Имя пользователя: ".$result[$indexResult][1]. "</div><div class ='car'> Автомобиль: ".$result[$indexResult][0]."</div><div class ='car'> Отзыв: ".$result[$indexResult][2]."</div></div><br/><br/>";
					}
					return  $str."</body>";
			}
	}
	//Если выбрана марка "другие" или "самоделки" то происходит итоговый парсинг и записывается результат в базу данных.
	if (isset($_POST["marks"])) {
		if ($_POST["marks"] === "/other/" || $_POST["marks"] === "/selfmade/") {
			if (!isset($_POST["action"]) || $_POST["action"] !== 'Отправить в базу данных') {
				$form .= $this->form();
				$marksArr = $this->parse_cars();
				$form .= "<form method='POST' ><p>Выберите марку: <select name='marks' onchange='this.form.submit()'>";
				if (!isset($_POST["marks"]) || $_POST["marks"] === " ") {
					$form .= "<option value=' ' selected='selected'></option>";
				}
				foreach ($marksArr as $marksIndex => $mark) {
					$form .= "<option value='$mark'";
					if (isset($_POST["marks"])) {
						if (($_POST["marks"]) === $mark) {
							$form .= "selected='selected'";
						}
					}
					$form .=">$marksIndex</option>";
				}
					$form .= "</select><br/><br/>";
					$form .= "<input class='submit' type='submit' name='action' value='Информация обо всех марках' /> <br/><br/>";
					$form .= "<input class='submit' type='submit' name='action' value='Отправить в базу данных' /> <br/><br/>";
					$form .= "<input class='submit' type='submit' name='action' value='Выгрузить базу данных' /> <br/><br/>";
					$form .="</form>";
					return $form;
			}
			else {
				if ($_POST["action"] === 'Отправить в базу данных') {
					$nameUserArr = $this->parse_name_user($_POST["marks"]);
					$db = new SQLite3('analytics.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->query('CREATE TABLE IF NOT EXISTS "visits" (
					"Автомобиль" VARCHAR,
					"Имя пользователя" VARCHAR,
					"Отзыв" VARCHAR
					)');
				$sql = "SELECT * FROM visits ORDER BY 'Автомобиль', 'Имя пользователя', 'Отзыв'";
				$rez = $db->query($sql);
				while ($row = $rez->fetchArray()) {
					$str[1] = $row['Имя пользователя'];
					$str[0] = $row['Автомобиль'];
					$str[2] = $row['Отзыв'];
					$result[] = $str;
				}
				unset($db);
				unset($rez);
				unlink('analytics.sqlite');
				
				
				foreach ($nameUserArr as $nameUserIndex => $nameUser) {
					$result[] = $this->review($nameUser, $nameUserIndex);
				}
				
				$db = new SQLite3('analytics.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->query('CREATE TABLE IF NOT EXISTS "visits" (
					"Автомобиль" VARCHAR,
					"Имя пользователя" VARCHAR,
					"Отзыв" VARCHAR
					)');
				for($index = 0; $index<count($result); ++$index) {
					$equal = false;
					for ($newindex = $index+1; $newindex<count($result);  ++ $newindex ) {
						if ($result[$index][2] === $result[$newindex][2]) {
							$equal = true;
						}
					}
					if ($equal === false) {
						$resultArr[] = $result[$index];
					}
				}
				$this->write_in_sql($resultArr , $db);
				$str = "";
					foreach ($resultArr as $indexResult => $value) {
						$str .= "<div class ='cars'><div class ='car'>Имя пользователя: ".$resultArr[$indexResult][1]. "</div><div class ='car'> Автомобиль: ".$resultArr[$indexResult][0]."</div><div class ='car'> Отзыв: ".$resultArr[$indexResult][2]."</div></div><br/><br/>";
					}
				return  $str."</body>";
			}
			}
		}
	}
	
	//Если выбрана модель и марка то происходит итоговый парсинг и записывается результат в базу данных.
	if (isset($_POST["model"])) {
		if ($_POST["model"] !== " " && isset($_POST["action"]) ){
			if ($_POST["action"] === 'Отправить в базу данных') {
				$nameUserArr = $this->parse_name_user($_POST["marks"].$_POST["model"]);
				$db = new SQLite3('analytics.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->query('CREATE TABLE IF NOT EXISTS "visits" (
					"Автомобиль" VARCHAR,
					"Имя пользователя" VARCHAR,
					"Отзыв" VARCHAR
					)');
				$sql = "SELECT * FROM visits ORDER BY 'Автомобиль', 'Имя пользователя', 'Отзыв'";
				$rez = $db->query($sql);
				while ($row = $rez->fetchArray()) {
					$str[1] = $row['Имя пользователя'];
					$str[0] = $row['Автомобиль'];
					$str[2] = $row['Отзыв'];
					$result[] = $str;
				}
				unset($db);
				unset($rez);
				unlink('analytics.sqlite');
				
				
				foreach ($nameUserArr as $nameUserIndex => $nameUser) {
					$result[] = $this->review($nameUser, $nameUserIndex);
				}
				
				$db = new SQLite3('analytics.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->query('CREATE TABLE IF NOT EXISTS "visits" (
					"Автомобиль" VARCHAR,
					"Имя пользователя" VARCHAR,
					"Отзыв" VARCHAR
					)');
				for($index = 0; $index<count($result); ++$index) {
					$equal = false;
					for ($newindex = $index+1; $newindex<count($result);  ++ $newindex ) {
						if ($result[$index][2] === $result[$newindex][2]) {
							$equal = true;
						}
					}
					if ($equal === false) {
						$resultArr[] = $result[$index];
					}
				}
				$this->write_in_sql($resultArr , $db);
				$str = "";
					foreach ($resultArr as $indexResult => $value) {
						$str .= "<div class ='cars'><div class ='car'>Имя пользователя: ".$resultArr[$indexResult][1]. "</div><div class ='car'> Автомобиль: ".$resultArr[$indexResult][0]."</div><div class ='car'> Отзыв: ".$resultArr[$indexResult][2]."</div></div><br/><br/>";
					}
				return  $str."</body>";
			}
			
			
		} 
		
		//Если выбрана модель, но не выбрана марка то выводится форма с выбором марки.
		else {
			$form    .= $this->form();
			$marksArr = $this->parse_cars();
			$form    .= "<form method='POST' ><p>Выберите марку: <select name='marks' onchange='this.form.submit()'>";
			if (!isset($_POST["marks"]) || $_POST["marks"] === " ") {
				$form .= "<option value=' ' selected='selected'></option>";
			}
			foreach ($marksArr as $marksIndex => $mark) {
				$form .= "<option value='$mark'";
				if (isset($_POST["marks"])) {
					if (($_POST["marks"]) === $mark) {
						$form .= "selected='selected'";
					}
				}
				$form .=">$marksIndex</option>";
			}
			$form .= "</select><br/><br/>";
					$form .= "<input class='submit' type='submit' name='action' value='Информация обо всех марках' /> <br/><br/>";
			$form .= "<input class='submit' type='submit' name='action' value='Выгрузить базу данных' /> <br/><br/>";
			
			//Если марка выбрана, но не выбрана модель то в форму записываются возможный выбор моделей.
			if (isset($_POST["marks"])) {
				if ($_POST["marks"] !== " " && $_POST["marks"] !== "/other/" && $_POST["marks"] !== "/selfmade/") {
					$modelArr = $this->parse_model_cars($_POST["marks"]);
					$form .= "<p>Выберите модель: <select name='model'>";
					$form .= "<option value=' ' selected='selected'></option>";
					foreach ($modelArr as $modelIndex => $modeles) {
						$form .= "<option value='$modeles'";
						$form .=">$modelIndex</option>";
					}
					$form .= "</select><br/><br/>";
					$form .= "<input class='submit' type='submit' name='action' value='Отправить в базу данных' /> <br/><br/>
					<input class='submit' type='submit' name='action' value='Информация всех моделей' />";
					$form .= "<input class='submit' type='submit' name='action' value='Выгрузить базу данных' /> <br/><br/>";
				}
			}
			if (isset($_POST["marks"])) {
				$marks = $_POST["marks"];
			}
			$form .="</form>";
		}
	} 
	
	//Если не выбрана марка то выводится форма с выбором.
	else {
		$form .= $this->form();
		$marksArr = $this->parse_cars();
		$form .= "<form method='POST' ><p>Выберите марку: <select name='marks' onchange='this.form.submit()'>";
		if (!isset($_POST["marks"]) || $_POST["marks"] === " ") {
			$form .= "<option value=' ' selected='selected'></option>";
		}
		foreach ($marksArr as $marksIndex => $mark) {
			$form .= "<option value='$mark'";
			if (isset($_POST["marks"])) {
				if (($_POST["marks"]) === $mark) {
					$form .= "selected='selected'";
				}
			}
			$form .=">$marksIndex</option>";
		}
		$form .= "</select><br/><br/>";
			$form .= "<input class='submit' type='submit' name='action' value='Информация обо всех марках' /> <br/><br/>";
			$form .= "<input class='submit' type='submit' name='action' value='Выгрузить базу данных' /> <br/><br/>";
		
		//Если марка есть, то вывод моделей в форму.
		if (isset($_POST["marks"])) {
			if ($_POST["marks"] !== " " && $_POST["marks"] !== "/other/" && $_POST["marks"] !== "/selfmade/") {
				$modelArr = $this->parse_model_cars($_POST["marks"]);
				$form .= "<p>Выберите модель: <select name='model'>";
				$form .= "<option value=' ' selected='selected'></option>";
				foreach ($modelArr as $modelIndex => $modeles) {
					$form .= "<option value='$modeles'";
					$form .=">$modelIndex</option>";
				}
				$form .= "</select><br/><br/>";
				
				$form .= "<input class='submit' type='submit' name='action' value='Отправить в базу данных' /> <br/><br/>";
				$form .= "<input class='submit' type='submit' name='action' value='Информация всех моделей' />";
				$form .= "<input class='submit' type='submit' name='action' value='Выгрузить базу данных' /> <br/><br/>";
				
				
			}
		}
		if (isset($_POST["marks"])) {
			$marks = $_POST["marks"];
		}
		$form .="</form>";
	} 
	return $form;
}

/**Функция парсинга сайта и вывода на экран.
* 
* @return type
*/
function parsing() {
	return $this->headlines().$this->result_form()."</div></body>";
}

/**Функция записи результата в базу данных.
* 
* @param type $resultArr
* @param type $db
* @return type
*/
function write_in_sql($resultArr,$db) {
	$stmn = $db->prepare('INSERT INTO "visits" ("Автомобиль", "Имя пользователя", "Отзыв") VALUES (:auto, :user, :review)');
	for ($resultIndexArr = 0; $resultIndexArr<count($resultArr); ++$resultIndexArr ) {
		$stmn->bindParam(':auto', $resultArr[$resultIndexArr][0], SQLITE3_TEXT);
		$stmn->bindParam(':user', $resultArr[$resultIndexArr][1], SQLITE3_TEXT);
		$stmn->bindParam(':review', $resultArr[$resultIndexArr][2], SQLITE3_TEXT);
		$stmn->execute();
		$stmn->reset();
	}
	return;
}
}
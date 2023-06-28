<?php
session_start();

function form()
{
	return file_get_contents('index.html');
}

if (isset($_POST['back'])) {
	echo form();
}
if (isset($_POST['buttoninput'])) {
	$db = new mysqli("localhost", "root", "", "users" );
	mysqli_set_charset($db, "utf8");
	$sql = "CREATE TABLE IF NOT EXISTS `books` (
  `Название книги` varchar(255) NOT NULL,
  `Автор книги` varchar(255) NOT NULL,
  `Год создания` bigint NOT NULL,
  `Жанр` varchar(255) NOT NULL,
  `Год прочтения` bigint NOT NULL,
  `Аннотация` varchar(255) NOT NULL
)";      
	$db->query($sql);
	settype($_POST['yearcreate'],"int");
	settype($_POST['yearread'],"int");
	$nameBook = $_POST['namebook'];
	$authorBook = $_POST['authorbook'];
	$yearCreate = $_POST['yearcreate'];
	$ganre = $_POST['ganre'];
	$yearRead = $_POST['yearread'];
	$annotation = $_POST['annotation'];
	$query = "INSERT INTO `books` (`Название книги`, `Автор книги`, `Год создания`, `Жанр`, `Год прочтения`, `Аннотация`) VALUES (";
	$query .= "'$nameBook'" .","."'$authorBook'".",".$yearCreate .","."'$ganre'" .","."'$yearRead'" .","."'$annotation'".")";
	$db->query($query);
	echo form();
}
else {
	if (isset($_POST['buttonresearch'])){
		$db = new mysqli("localhost", "root", "", "users");
		mysqli_set_charset($db, "utf8");
		$sql = "SELECT * FROM books";
		$rez = $db->query($sql);
		if ($rez != false) {
			$rows = mysqli_num_rows($rez);
			for ($i = 0 ; $i < $rows ; ++$i) {
				$result[] = mysqli_fetch_row($rez);
			}
			if (isset($_POST['namebook'])) {
				$nameBook = $_POST['namebook'];
				$userRequest[] = $nameBook;
			}
			else {
				$nameBook = "";
				$userRequest[] = $nameBook;
			}
			if (isset($_POST['authorbook'])) {
				$authorBook = $_POST['authorbook'];
				$userRequest[] = $authorBook;
			}
			else {
				$authorBook = "";
				$userRequest[] = $authorBook;
			}
			if (isset($_POST['yearcreate'])) {
				$yearCreate = $_POST['yearcreate'];
				$userRequest[] = $yearCreate;
			}
			else {
				$yearCreate = "";
				$userRequest[] = $yearCreate;
			}
			if (isset($_POST['ganre'])) {
				$ganre = $_POST['ganre'];
				$userRequest[] = $ganre;
			}
			else {
				$ganre = "";
				$userRequest[] = $ganre;
			}
			if (isset($_POST['yearread'])) {
				$yearRead = $_POST['yearread'];
				$userRequest[] = $yearRead;
			}
			else {
				$yearRead = "";
				$userRequest[] = $yearRead;
			}
			if (isset($_POST['annotation'])) {
				$annotation = $_POST['annotation'];
				$userRequest[] = $annotation;
			}
			else {
				$annotation  = "";
				$userRequest[] = $annotation;
			}
			for ($i = 0 ; $i < $rows ; ++$i) {
				$flag = true;
				for ($j = 0; $j < 6; ++$j) {
					if ($userRequest[$j] != "") {
						if ($userRequest[$j] != $result[$i][$j]) {
							$flag = false;
						}
					}
				}
				if ($flag) {
					$myBook[] = $result[$i];
				}
			}
			if (isset($myBook)) {
				$htmlButton = "<p>Найдены совпадения:</p>";
				for ($i = 0; $i < count($myBook); ++$i ) {
					$htmlButton .= "<p>1.Название книги: ".$myBook[$i][0]."</p>";
					$htmlButton .= "<p>2.Автор книги: ".$myBook[$i][1]."</p>";
					$htmlButton .= "<p>3.Год создания: ".$myBook[$i][2]."</p>";
					$htmlButton .= "<p>4.Жанр: ".$myBook[$i][3]."</p>";
					$htmlButton .= "<p>5.Год прочтения: ".$myBook[$i][4]."</p>";
					$htmlButton .= "<p>6.Аннотация: ".$myBook[$i][5]."</p>";
					$htmlButton .= "__________________________________________";
				}
				$htmlButton .= "<form><input id='back' name='back' type='submit' value='Вернуться обратно'></form>";
				echo $htmlButton;
			}
			else {
				$htmlButton = "<p>Cовпадения не найдены!</p>"; 
				$htmlButton .= "<form><input id='back' name='back' type='submit' value='Вернуться обратно'></form>";
				echo $htmlButton;
			}
		}
		else {
			$htmlButton = "<p>База данных пуста!</p>";
			$htmlButton .= "<form><input id='back' name='back' type='submit' value='Вернуться обратно'></form>";
			echo $htmlButton;
		}
	} else {
		echo form();
	}
}

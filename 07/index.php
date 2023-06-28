<?php

// хорошо, но дизайн, конечно... Развивайте чувство прекрасного

session_start();

//Считывание данных из форм
foreach(['login', 'password', 'action', 'message', 'delete', 'guest', 'page', 'create', 'redactor','redMessage'] as $parameterName) {
	$$parameterName = isset($_REQUEST[$parameterName]) ? trim($_REQUEST[$parameterName]) : '';
}
$messages_on_page = 5;
$file = fopen('users.txt', "r+");
if (flock($file,LOCK_SH)) {
	$arrayOfUsersArr  = unserialize(file_get_contents('users.txt'));
	flock($file,LOCK_UN);
}
fclose($file);
$form             = headlines();
$moderatorsArr    = array("admin");

//Проверка действия и если нужно выйти то удаляем сессию и выходим на изначальную форму
if ($action === 'Выйти') {
	$form .= inputWindow();
	session_destroy();
	$_SESSION = [];
	header("Location: http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]);
	die();
}

//Проверка на то, было ли редактирование и изменение сообщения.
if (isset($redactor) && $redactor !== "") {
	if (isset($redMessage) && $redMessage !== "") {
		$messageCreate         = explode(' ', $_SESSION['message']);
		$numberOfCreateMessage = substr($messageCreate[0], 1);
		$newCreate             = strstr($_SESSION['message'], "<br/>");
		$newCreate             = substr($newCreate, 5);
		$newCreate             = strstr($newCreate, "<br/>", true);
		if ($redMessage !== $newCreate) {
			createMessageDate($numberOfCreateMessage - 1, $messageCreate[1], $redMessage);
		}
	}
	$redactor = null;
}

//Проверка на то, была ли нажата кнопка редактирование для открытия новой формы редактирования.
if (isset($create) && $create !== "") {
	$newCreate           = strstr($create, "<br/>");
	$newCreate           = substr($newCreate, 5);
	$newCreate           = strstr($newCreate, "<br/>", true);
	$form               .= createForm($newCreate);
	$_SESSION['message'] = $create;
	$create              = null;
} 
else {

	//Проверка на то, чтобы был не гость, а пользователь, также в последущем проверка на отправку сообщения
	if (isset($_SESSION['login']) && !(isset($_SESSION['guest']) && $_SESSION['guest'] === "guest" )) {
		if ($action === 'Отправить' && ($message !== "" && $message !== null)) {
			header("Location: http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]);
			sendingMessage($message);
		}
		if (isset($delete) && $delete !== "" && in_array($_SESSION['login'], $moderatorsArr)) {
			$deleteArrayOfMessage  = explode(' ', $delete);
			$numberOfDeleteMessage = substr($deleteArrayOfMessage[0], 1);
			deleteMessage($numberOfDeleteMessage - 1, $deleteArrayOfMessage[1]);
			$delete = null;
			header("Location: http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]);
		}

		//Удаления сообщения пользователем
		if (isset($delete) && $delete !== "" && $_SESSION['login'] !== 'admin') {
			$deleteArrayOfMessage = explode(' ', $delete);
			if ($_SESSION['login'] === $deleteArrayOfMessage[1]) {
				$numberOfDeleteMessage = substr($deleteArrayOfMessage[0], 1);
				deleteMessage($numberOfDeleteMessage - 1, $deleteArrayOfMessage[1]);
			}
			$delete = null;
			header("Location: http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]);
		}

		//Редактирование формы вывода сообщений
		$action = '';
		$form  .= "<div class='formuser'>";
		$form  .= "<div class='message'>";

		//Вывод сообщений
		if (!empty(readFromFile())) {
			$conversionMessageArray = readFromFile();
			if (is_array($conversionMessageArray)) { 
				$countConversionMessageArray = count($conversionMessageArray);
				$countPage = ceil($countConversionMessageArray / 5);
				settype($countPage, "int");
				if (isset($page) && $page !== '') {
					$page = strval($page);
				}
				else {
					$page = '1';
				}
				$messageDifferencePossible = 0; 
				if ($page === '1') {
					$messageDifferencePossible = $countPage * 5 - $countConversionMessageArray; 
				}

				//Вывод сообщений  и разделение их на свои и чужие	
				for ($indexConversionMessageArray = $countConversionMessageArray - (($page - 1) * $messages_on_page) - 1; $indexConversionMessageArray > $countConversionMessageArray - (($page - 1) * 5) - 6; --$indexConversionMessageArray) {
					if ($indexConversionMessageArray < 0 ) {
						break;
					}
					if ($_SESSION['login'] === $conversionMessageArray[$indexConversionMessageArray][1] && in_array($_SESSION['login'], $moderatorsArr)) {
						$form .= ownMessage(messageStructure($conversionMessageArray[$indexConversionMessageArray]));
					}
					elseif(in_array($_SESSION['login'], $moderatorsArr)) {
						$form .= someoneMessage(messageStructure($conversionMessageArray[$indexConversionMessageArray]));
					}
					else {
						if ($_SESSION['login'] === $conversionMessageArray[$indexConversionMessageArray][1]) {
							$form .= ownMessage(messageStructureGuest($conversionMessageArray[$indexConversionMessageArray]));
						} 
						else {
							$form .= someoneGuestMessage(messageStructureGuest($conversionMessageArray[$indexConversionMessageArray]));
						}
					}
				}
			}  
		}

		//Добавление отправки и выхода
		$form .= "</div>";
		$form .= userExit();
		settype($page, "int");
		if (!empty(readFromFile())) {
			$form .= openPage($countPage + 1, $page);
		}
		$form .= "</div>";
	} 

	//Проверка на гостя
	elseif(isset($_SESSION['guest']) && $_SESSION['guest'] === "guest") {
		if ($action === 'Отправить' && $message !== "" && $message !== null && $login !== "" && $login !== null) {
			$_SESSION['login'] = "Гость-".$login;
			header("Location: http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]);
			sendingMessage($message);
		}
		$action = '';
		$form .= "<div class='formuser'>";
		$form .= "<div class='message'>";

		//Вывод сообщения для гостя
		if (!empty(readFromFile())) {
			$conversionMessageArray = readFromFile(readFromFile());
			if (is_array($conversionMessageArray)) {
				$countConversionMessageArray = count($conversionMessageArray);
				$countPage                   = ceil($countConversionMessageArray / 5);
				settype($countPage, "int");
				if (isset($page) && $page !== '') {
					$page = strval($page);
				}
				else {
					$page = '1';
				}
				$messageDifferencePossible = 0; 
				if ($page === '1') {
					$messageDifferencePossible = $countPage * 5 - $countConversionMessageArray; 
				}

				//Распределение сообщений на собственные и чужие
				for ($indexConversionMessageArray = $countConversionMessageArray - (($page - 1) * $messages_on_page) - 1; $indexConversionMessageArray > $countConversionMessageArray - (($page - 1) * 5) - 6; --$indexConversionMessageArray) {
					if ($indexConversionMessageArray < 0 ) {
						break;
					}
					if (isset($_SESSION['login']) && $_SESSION['login'] === $conversionMessageArray[$indexConversionMessageArray][1]) {
						$form .= ownGuestMessage(messageStructureGuest($conversionMessageArray[$indexConversionMessageArray]));
					}
					else {
						$form .= someoneGuestMessage(messageStructureGuest($conversionMessageArray[$indexConversionMessageArray]));
					}
				}
			}
		}
		$form .= "</div>";
		$form .= guestExit();
		settype($page, "int");
		if (!empty(readFromFile())) {
			$form .= openPage($countPage + 1, $page);
		}
		$form .= "</div>";
	}
	else {
		$form .= inputWindow();

		//Вход пользователя и проверка на корректность введенных данных
		if ($action === 'Войти как пользователь') {
			if (!array_key_exists($login, $arrayOfUsersArr) || $arrayOfUsersArr[$login] !== md5($password)) {
				$form .= "Неверный логин или пароль </form></div>";
			}
			else {
				$_SESSION['login'] = $login;
				header("Location: http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]);
				die();
			}
		}

		//Проверка на вход как гость
		elseif ($action === 'Войти как гость') { 
			$_SESSION['guest'] = "guest";
			header("Location: http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]);
			die();
		}
	}
}
print $form."</body>";

/**Функция добавление css файла.
* 
* @return string
*/
function headlines()
{
		return "<head><link href='style.css' rel='stylesheet' type='text/css' /> </head><body>";
}

/**Функция страницы входа.
* 
* @return string
*/
function inputWindow() 
{
    return "<div class = 'login'><form method='post' > Войдите в систему<br/><br/> <input type='text' name='login' placeholder='Логин'/> <br/><br/>
  <input type='password'  name='password'  placeholder='Пароль'/> <br/><br/>
  <input class='submit' type='submit' name='action' value='Войти как пользователь' /> <br/><br/>
  <input class='submit' type='submit' name='action' value='Войти как гость' /> <br/><br/>";
}

/**Функция получения сообщений из файла.
* 
* @return array
*/
function readFromFile()
{
	$arrayOfMessages = NULL;
	$file = fopen('guestbook.txt', "r+");
	if (flock($file,LOCK_SH)) {
		$read= file_get_contents('guestbook.txt');
		flock($file,LOCK_UN);
	}
	fclose($file);
	if (!empty($read) && $read !== "") {
		$arrayOfMessages = unserialize($read);
	}
	return $arrayOfMessages;
}

/**Функция преобразования ячеек сообщения в структурное сообщение для вывода на экран для админа. 
* 
* @param type $message
* @return string
*/
function messageStructure($message){
	$structuredMessage  = "#".$message[0];
	$structuredMessage .= " ".$message[1]." "."ip: ".$message[4]."<br/>";
	$structuredMessage .= $message[3]."<br/>";
	$structuredMessage .= "Дата отправки: ".$message[2];
	if (isset($message[5])) {
		$structuredMessage .= "<br/>".$message[5];
	}
	return $structuredMessage;
}

/**Функция преобразования ячеек сообщения в структурное сообщение для вывода на экран для всех остальных. 
* 
* @param type $message
* @return string
*/
function messageStructureGuest($message) {
	$structuredMessage  = "#".$message[0];
	$structuredMessage .= " ".$message[1]." "."<br/>";
	$structuredMessage .= $message[3]."<br/>";
	$structuredMessage .= "Дата отправки: ".$message[2];
	if (isset($message[5])) {
		$structuredMessage .= "<br/>".$message[5];
	}
	return $structuredMessage;
}



/**Функция отправки сообщения.
* 
* @param type $message
* @return type
*/
function sendingMessage($message)
{
	if (!empty(readFromFile())) {
		$numberMessage          = 1;
		$conversionMessage      = readFromFile();
		$countConversionMessage = count($conversionMessage);
		
		//Изменение номера сообщения при наличии сообщений от данного пользователя
		for ($indexConversionMessage = 0; $indexConversionMessage < $countConversionMessage; ++$indexConversionMessage) {
			if ($conversionMessage[$indexConversionMessage][1] === $_SESSION['login']) {
				$numberMessage = $conversionMessage[$indexConversionMessage][0] + 1;
			}
		}
	}
	else {
		$numberMessage = 1;
	}
	
	//Итоговое сообщение и запись его в текстовый файл
	$numberMessage = strval($numberMessage);
	$summaryMessage[0] = $numberMessage;
	$summaryMessage[1] = $_SESSION['login'];
	$summaryMessage[2] = date("d.m.Y H:i:s");
	$summaryMessage[3] = $message;
	$summaryMessage[4] = $_SERVER['REMOTE_ADDR'];
	$conversionMessage[] = $summaryMessage;
	file_put_contents('guestbook.txt', serialize($conversionMessage), LOCK_EX);
	return;
}



/**Функция удаления сообщения.
* 
* @param type $number
* @param type $name
* @return type
*/
function deleteMessage($number, $name)
{
	$summaryMessage           = "";
	$conversionToMessage      = readFromFile();
	$countConversionToMessage = count($conversionToMessage);
	$numberOfMessage          = strval($number + '1');
	
	//Удаление нужного нам сообщения
	for ($indexConversionToMessage = 0; $indexConversionToMessage < $countConversionToMessage; ++$indexConversionToMessage) {
		if (($conversionToMessage[$indexConversionToMessage][0] === $numberOfMessage) && ($conversionToMessage[$indexConversionToMessage][1] === $name)) {
			array_splice($conversionToMessage, $indexConversionToMessage, 1);
			break;
		}
	}
	
	//Изменение номеров сообщений после удаления
	$countConversionToMessage = count($conversionToMessage);
	for ($indexConversionToMessage = 0; $indexConversionToMessage < $countConversionToMessage; ++$indexConversionToMessage) {
		if ($numberOfMessage <= $conversionToMessage[$indexConversionToMessage][0] && $conversionToMessage[$indexConversionToMessage][1] === $name) {
			$conversionToMessage[$indexConversionToMessage][0] -= '1';
			$conversionToMessage[$indexConversionToMessage][0]  = strval($conversionToMessage[$indexConversionToMessage][0]);
		}
	}
	file_put_contents('guestbook.txt', serialize($conversionToMessage),LOCK_EX);
	return;
}

/**Функция вывода собственного сообщения.
* 
* @param type $message
* @return type
*/
function ownMessage($message)
{
	return "<div class='ownUser'><form method='post' ><button name='delete' value='$message'>X</button><button name='create' value='$message'>Редактирование</button></form>$message</div>";
}

/**Функция вывода собственного сообщения у гостя
* 
* @param type $message
* @return type
*/
function ownGuestMessage($message)
{
	return "<div class='ownUser'><form method='post' ></form>$message</div>";
}

/**Функция вывода чужого сообщения у гостя
* 
* @param type $message
* @return type
*/
function someoneGuestMessage($message)
{
	return "<div class='someoneUser'><form method='post' ></form>$message</div>";
}

/**Функция вывода чужого сообщения.
* 
* @param type $message
* @return type
*/
function someoneMessage($message)
{
	return "<div class='someoneUser'><form method='post' ><button name='delete' value='$message'>X</button><button name='create' value='$message'>Редактирование</button></form>$message</div>";
}

/**Функция редактирования сообщения.
* 
* @param type $number
* @param type $name
* @param type $createMessage
* @return type
*/
function createMessageDate($number, $name, $createMessage)
{
	$summaryMessage           = "";
	$conversionToMessage      = readFromFile();
	$countConversionToMessage = count($conversionToMessage);
	$numberOfMessage          = strval($number + '1');
	$dateCreate = date("d.m.Y")." в ".date("H:i:s"); 
	$addCreateMessage = "Отредактировано пользователем ".$_SESSION['login'].' '.$dateCreate;
	
	//Редактирование нужного нам сообщения
	for ($indexConversionToMessage = 0; $indexConversionToMessage < $countConversionToMessage; ++$indexConversionToMessage) {
		if (($conversionToMessage[$indexConversionToMessage][0] === $numberOfMessage) && ($conversionToMessage[$indexConversionToMessage][1] === $name)) {
			$conversionToMessage[$indexConversionToMessage][3] = $createMessage;
			$conversionToMessage[$indexConversionToMessage][5] = $addCreateMessage;
			break;
		}
	}
	file_put_contents('guestbook.txt', serialize($conversionToMessage), LOCK_EX);
	return;
}

/**Форма редактирования сообщения.
* 
* @param type $message
* @return type
*/
function createForm($message){
return	"<div class='redactor'><form method='post'> Редактирование сообщения <br/>
		<input type='text' name='redMessage' value = '$message' /> <br/><br/>
		<input class = 'submit' type='submit' name='redactor' value='Отправить' /> </form></div>";
}

/**Функция вывода формы для гостя где находится отправка и выход.
* 
* @return string
*/
function guestExit(){
	return "<div class='user'><form method='post'> <input type='text' name='login' placeholder='Имя гостя' />
		<input type='text' name='message' placeholder='Сообщение' /> 
		<input class = 'submit' type='submit' name='action' value='Отправить' />
	<input class = 'submit' type='submit' name='action' value='Выйти' /> </form></div>";
}

/**Функция вывода формы для пользователя где находится отправка и выход.
* 
* @return string
*/
function userExit(){
	return "<div class='user'><form method='post'> <input type='text' name='message' placeholder='Сообщение' /> 
		<input class = 'submit' type='submit' name='action' value='Отправить' />
	<input class = 'submit' type='submit' name='action' value='Выйти' /> </form></div>";
}

/**Функция вывода номера страницы сообщений
* 
* @param type $countPage
* @return type
*/
function openPage($countPage, $numberPageActive){
	$pagesAround = 2;
	$finish = false;
	$resultString = "<div><form method='post'>";
	for ($numberPage = 1; $numberPage < $countPage; ++$numberPage) {
		if ($numberPage === $numberPageActive) {
			$resultString .= "<button class='page' name='page' value='$numberPage'>$numberPage</button>";
		}
		else {
			if ($numberPage === 1 || $numberPage === $countPage - 1) {
				$resultString .= "<button name='page' value='$numberPage'>$numberPage</button>";
			} 
			elseif ($numberPage <= $numberPageActive + 2 && $numberPage >= $numberPageActive - 2) {
				$resultString .= "<button name='page' value='$numberPage'>$numberPage</button>";
				$finish = false;
			}
			else {
				if ($finish === false) {
					$resultString .= "...";
					$finish = true;
				} 
				else {
					continue;
				}
			}
		}
	}
	return $resultString."</form></div>";
}

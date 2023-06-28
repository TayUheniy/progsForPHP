<?php

//Массив систем счисления
$basesArr = array(
	"bin" => array("0", "1"), 
	"oct" => array("0", "1", "2", "3", "4", "5", "6", "7"), 
	"dec" => array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"),
	"hex" => array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"),
	"q.w.e.r.t.y" => array("q", "w", "e", "r", "t", "y"),
	"english-language" => array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", 
	"m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"),
);

//Общий массив систем счисления 
$commonBasesArr = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f","g","h", "i", "j", "k","l", 
	"m", "n", "o", "p","q","r","s","t","u","v","w","x","y","z");
$form           = "<head><link href='style.css' rel='stylesheet' type='text/css' /> </head><body>";
$form          .= "<form name = 'calculator' method = 'post'><table ><tr><td><select name='selectbase'>
<option >Система счисления</option>";

//Перебор всех возможных систем счисления, которые были добавлены в массив basesArr и проверка на то, какая выбрана система счисления ранее
foreach ($basesArr as $systemBase => $base) {
	$form .= "<option value='$systemBase'";
	if (isset($_POST["selectbase"])) {
		if ($_POST["selectbase"] === $systemBase) {
			$form .= "selected='selected'";
		}
	}
$form .= ">$systemBase</option>";
}

//Оформление калькулятора с операциями и аргументами
$form .= "</td></tr> 
<tr><td> <p><input placeholder = 'Первый аргумент' type='text' name = 'input1' size='36'></p></td></tr>
<tr><td><p><input placeholder = 'Второй аргумент' type='text' name = 'input2' size='36'></p></td></tr>
<tr><td>  <table><tr><td><button name = 'operand' value = '+'>+</button></td> <td><button name = 'operand' value = '-'>-</button></td></tr>
<tr><td><button  name = 'operand' value = '*'>*</button></td> <td><button name = 'operand' value = '/'>/</button></td> </tr></table>  </td></tr>";
	
//Считывание введенных данных из калькулятора
$inputOne   = filter_input(INPUT_POST, "input1");
$inputTwo   = filter_input(INPUT_POST, "input2");
$operation  = filter_input(INPUT_POST, "operand");
$selectBase = filter_input(INPUT_POST, "selectbase");
if ($inputOne === null) {
	$inputOne = "";
}
if ($inputTwo === null) {
	$inputTwo = "";
}
if ($selectBase === null) {
	$selectBase = "Система счисления";
}

//Проверка возможно ли совершать действие если не заполнен аргумент при наличии результата
if ($inputOne === "" || $inputTwo === "" || $operation === null) {
	if (!isset($_POST["result"])) {
		$result = "";
	}
	else {
		$result = $_POST["result"];
	}
	$error = "";
	if ($result !== "") {
		if ($inputOne === "" && $inputTwo !== "") {
			$inputOne = $result;
		}
		elseif ($inputTwo === "" && $inputOne !== "") {
				$inputTwo = $inputOne;
				$inputOne = $result;
		}
		else {
			$error = "Оба параметра пусты";
		}
	}
}

//Проверка на то, достаточно ли данных введено в калькулятор для его функционирования
if ($operation !== null && ((($inputOne === "" || $inputTwo === "") && $result === "") || $selectBase === "Система счисления" || ($inputOne === "" && $inputTwo === ""))) {
	$error  = "Ошибка, недостаточно данных";
	$result = "";
}
else {
		
	//Проверка заполнения первого аргумента
	$numberSystemError = false;
	if ($inputOne !== "") {
		$inputOne       = strtolower($inputOne);
		$lengthInputOne = strlen($inputOne);
			
		//Проверка системы счисления первого аргумента
		for ($characterIndex = 0; $characterIndex < $lengthInputOne; ++$characterIndex) {
			if (!in_array($inputOne[$characterIndex], $basesArr[$selectBase], true) && $inputOne[0] !== '-') {
				$numberSystemError = true;
			}
		}
	}
		
	//Проверка заполнения второго аргумента
	if ($inputTwo !== "") {
		$inputTwo       = strtolower($inputTwo);
		$lengthInputTwo = strlen($inputTwo);
			
		//Проверка системы счисления второго аргумента
		for ($characterIndex = 0; $characterIndex < $lengthInputTwo; ++$characterIndex) {
			if (!in_array($inputTwo[$characterIndex], $basesArr[$selectBase], true) && $inputTwo[0] !== '-') {
				$numberSystemError = true;
			}
		}
	}
		
	//Проверка была ли найдена ошибка
	if ($numberSystemError === true) {
		$error  = "Аргумент не в той системе счисления";
		$result = "";
	}
	else {
		if ($selectBase !== "Система счисления") {
			$numberSystemLength = count($basesArr[$selectBase]);
			$commonInputOne     = "";
			$commonInputTwo     = "";
			$minusInputOne      = 1;
			$minusInputTwo      = 1;
		
			//Проверка отрицательности аргументов
			if ($inputOne[0] === "-") {
				$minusInputOne = -1;
			}
			if ($inputTwo[0] === "-") {
				$minusInputTwo = -1;
			}
			$lengthInputOne = strlen($inputOne);
		
			//Перевод первого аргумента из введенной системы счисления в удобную нам общую(числовую) систему счисления
			for ($keyInputOne = 0; $keyInputOne < $lengthInputOne; ++$keyInputOne) {
				$keyCharacterInputOne = array_search($inputOne[$keyInputOne], $basesArr[$selectBase]);
				$commonInputOne      .= $commonBasesArr[$keyCharacterInputOne];
			}
			$lengthInputTwo = strlen($inputTwo);
		
			//Перевод второго аргумента из введенной системы счисления в удобную нам общую(числовую) систему счисления
			for ($keyInputTwo = 0; $keyInputTwo < $lengthInputTwo; ++$keyInputTwo) {
				$keyCharacterInputTwo = array_search($inputTwo[$keyInputTwo], $basesArr[$selectBase]);
				$commonInputTwo      .= $commonBasesArr[$keyCharacterInputTwo];
			}		
		
			//Перевод из общей системы счисления в десятичную
			$bufferInputOne  = base_convert($commonInputOne, $numberSystemLength, 10);
			$bufferInputTwo  = base_convert($commonInputTwo, $numberSystemLength, 10);
			$bufferInputOne *= $minusInputOne;
			$bufferInputTwo *= $minusInputTwo;
			
			//Выполнения операции при нажатии на определенную кнопку
			if ($operation === "+") {
				$result = $bufferInputOne + $bufferInputTwo;
				$error  = "";
			}
			if ($operation === "-") {
				$result = $bufferInputOne - $bufferInputTwo;
				$error  = "";
			}
			if ($operation === "*") {
				$result = $bufferInputOne * $bufferInputTwo;
				$error  = "";
			}
			if ($operation === "/") {
				if ($bufferInputTwo === 0) {
					$result = "";
					$error  = "Ошибка, на ноль делить нельзя";
				}
				else {
					$result = intdiv($bufferInputOne, $bufferInputTwo);
					$error  = "";
				}
			} 
	
			//Проверка на отрицательность
			$minusResult = false;
			if ($result < 0) {
				$minusResult = true;
			}
	
			//Перевод из 10 системы счисления в общую(числовую) систему счисления
			$commonResult       = base_convert($result, 10, $numberSystemLength);
			$bufferResult       = "";
			$result             = "";
			$lengthCommonResult = strlen($commonResult);
	
			//Перевод из общей(числовой) системы счисления в введенную нами систему счисления	
			for ($keyInputResult = 0; $keyInputResult < $lengthCommonResult; ++$keyInputResult) {
				$keyResult     = array_search($commonResult[$keyInputResult], $commonBasesArr);
				$bufferResult .= $basesArr[$selectBase][$keyResult];
			}
		
			//Проверка на выполнение отрицательности
			if ($minusResult === true) {
				$result = "-".$bufferResult;
			}
			else {
				$result = $bufferResult;
			}
		}
	}
}
if ($error === "Ошибка, на ноль делить нельзя") {
	$result = "";
};

//Итоговый вывод
$form .= "<tr><td> <p><input name = 'result' type='text' size='36' readonly value = '$result'></p></td></tr>
	<tr><td><p><input type='text' size='36' readonly value = '$error'></p></td></tr>
	</table></form></body>";
echo $form;

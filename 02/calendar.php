<?php
$form = "<head><link href='calend.css' rel='stylesheet' type='text/css' /> </head><body>";

//создание массива месяцев
$monthArray = array(
	"1"  => "Январь", 
	"2"  => "Февраль", 
	"3"  => "Март", 
	"4"  => "Апрель",
	"5"  => "Май",
	"6"  => "Июнь", 
	"7"  => "Июль", 
	"8"  => "Август", 
	"9"  => "Сентябрь",
	"10" => "Октябрь",
	"11" => "Ноябрь", 
	"12" => "Декабрь"
);

//Заполнение селектов из массива
$form .= "<form method='GET' ><p>Месяц: <select name='month' ><option disabled selected='selected' ></option>";
for ($index = 1; $index < 13; ++$index) {
	$form .= "<option value='$index'>$monthArray[$index]</option> ";
}

//заполнение селектов 
$form .= "</select>
<br> <br>
Год: <select name='years' onchange='this.form.submit()'>
<option disabled selected='selected'></option>";
for ($index = 2000; $index < 2030; ++$index) {	
	$form .= "<option value='$index'>$index</option> ";
}

//Закрытие селектов лет
$form .= "</select> </p></form>";
echo $form;

//Заполение переменных из формы для дальнейшей работы с ними и вывод на экран
$month = filter_input(INPUT_GET, "month");
$years = filter_input(INPUT_GET, "years");
if (!isset($month) || !isset($years)) {
	$month = date("n"); 
	$years = date("Y");	
}

//вывод выбранного года и месяца
$monthOut   = date("F", mktime(0, 0, 0, $month, 1, $years));
$yearsOut   = date("Y", mktime(0, 0, 0, $month, 1, $years));
$monthLabel = date("n", mktime(0, 0, 0, $month, 1, $years));
echo "<p>$monthArray[$monthLabel] $yearsOut </p>";

//Таблица с днями неделями и массив с праздниками
$tableStart    = "<table border='5' align='center'><tr>
<td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td class='weekend'>Сб</td><td class='weekend'>Вс</td></tr><tr>";
$holidaysArr   = array('01.01', '09.05', '07.01', '23.02', '08.03', '01.05', '12.07', '04.11');
$dayOfTheWeek  = date("N", mktime(0, 0, 0, $month, 1, $years));
$tableFillable = "";

//Переменная для определения пустых ячеек
$tableFillable .= "</tr>";
$emptyCell      = 0;
$lastWeek       = 0;

//Заполнение таблицы
for ($number = 1,$variableDay = 0; $variableDay < 6; ++$variableDay) {	
	if ($variableDay == 5 && $emptyCell != 0 || $lastWeek != 0) {
		break;
	}
	 
	//заполнение недели
	$tableFillable .= "<tr>";
	for ($variableLines = 1; $variableLines < 8; $variableLines++, ++$number) {
		if ($variableDay == 0) {
			while ($variableLines < $dayOfTheWeek) {
				
				//проверка на выпадение ячейки под столбец с выходными днями до 1 
				if ($variableLines == 6) {
					$tableFillable .= "<td class='weekend'></td>";
				}
				else 
				{ $tableFillable .= "<td></td>";	
				} 
				
				//завершение цикла
				++$variableLines;
			}
		}
		
		//проверка на то, является ли этот день сегодняшним
		if (checkdate($month, $number, $years) == true) {
			if (date("d.m.y") == (date("d.m.y", mktime(0, 0, 0, $month, $number, $years)))) {
				$tableFillable .= "<td class='today'>$number</td>";
			} 
			
			//если не прошла проверка на сегодняшний день, идет проверка на праздник
			else 
			{	if (in_array(date("d.m", mktime(0, 0, 0, $month, $number, $years)), $holidaysArr)) {
					$tableFillable .= "<td class='holiday'>$number</td>";
			} 
			
			//проверка на выходной день 
			else
			{ $number         = date("j", mktime(0, 0, 0, $month, $number, $years));
				$dayOfTheWeek = date("N", mktime(0, 0, 0, $month, $number, $years));
				if ($dayOfTheWeek >= 6) {
					$tableFillable .= "<td class='weekend'>$number</td>";
				}
				
				//если не выходной то обычный день
				else	
				{ $tableFillable .= "<td>$number</td>";
				}
			}
			}	
		}
		else 
		{
			$weekLastDay = date("N", mktime(0, 0, 0, $month, --$number, $years));
			
			//проверка на то если последний день месяца воскресение, то убрать ненужную пустую строку 
			if (date("N", mktime(0, 0, 0, $month, $number, $years)) != 7) {
				$weekLastDay = date("N", mktime(0, 0, 0, $month, $number, $years));
				if (7 - $weekLastDay - $emptyCell <= 2) {
					$tableFillable .= "<td class='weekend'></td>";
					++$emptyCell;
				}
				
				//оставшееся ячейки после последнего дня месяца будут пустыми
				else 
				{
					$tableFillable .= "<td></td>"; 
					++$emptyCell;
				}
			}
			else 
			{
				
				//если последний день воскресение
				++$lastWeek;
				break;
			}
		} 
	}
	
	//Заканчиваем последнюю строку
	$tableFillable .= "</tr>";
}

//Итоговое схлопывание
$tableEnd     = "</table></body>";
$summaryTable = $tableStart.$tableFillable.$tableEnd;
echo $summaryTable;
?>
<?php

//Массив слов
$wordArrays			 = array(
	array("word1.1", "bigword1.2", "moreword1.3", "word1.4"),
	array("word2.1", "bigword2.2", "moreword2.3", "moreword2.4", "worde2.5"),
    array("word3.1", "bigword3.2", "moreword3.3"),
    array("word4.1", "bigword4.2", "moreword4.3", "morewdorders4.4"),
    array("word5.1", "bigword5.2", "moreword5.3", "moreword5.4"),
    array("word6.1", "bigword6.2", "word6.3", "bigword6.4", "moreword6.5", "moreword6.6", "word6.7"),
);

//Объявление переменных для сохранения длин максимальных слов в каждом массиве и вывода на экран
$output            = "<pre>";
$maxLengthInStrArr = array();
$maxLengthWord     = 0;
$maxCountOfWord    = 0;

//Цикл заполнения массива максимальных длин слов
foreach ($wordArrays as $key => $array) {
	$countWordsInArray = 0;
	foreach ($array as $wordKey => $value) {
		$length = strlen($value);
		
		//проверка имеет ли слово максимальную длину в своем массиве 
		if ($maxLengthWord < $length) {
			$maxLengthWord = $length;
		}
	}
	
	//проверка на то, сколько максимально слов из всех массивов 
	$countWordsInArray = count($array);
	if ($maxCountOfWord < $countWordsInArray) {
		$maxCountOfWord = $countWordsInArray;
	}
	
	//заполняем массив максимальных длин слов всего массива, длиной максимального слова в одном массиве
	$maxLengthInStrArr[] = $maxLengthWord;
	$maxLengthWord       = 0;
}
	
//Дополняем подмассивы недостающими словами, чтобы количество элементов у каждого подмассива было одинаково
$countWordArrays = count($wordArrays);
for ($indexWord = 0; $indexWord < $countWordArrays; ++$indexWord) {
	$maxWordsArray = count($wordArrays[$indexWord]);
	for ($indexWordsInArray = $maxWordsArray; $indexWordsInArray < $maxCountOfWord; ++$indexWordsInArray) {
		$wordArrays[$indexWord][] = " ";
	}
}
	
//Объявление переменных для нового массива	
$newWordArrays = array();
$indexSubArrayWords   = -1;
	
//Заполняем новый массив слов для отображения их в нужном нам порядке(требовалось изначально вывести вертикально)	
foreach ($wordArrays as $key => $array) {
	++$indexSubArrayWords;
	$indexArrayWords = 0;
	foreach ($array as $wordKey => $value) {
		$newWordArrays[$indexArrayWords][$indexSubArrayWords] = $value;
		++$indexArrayWords;
	}
}
	
//выравнивание массива слов
foreach ($newWordArrays as $secondKey => $secondArray) {
	$indexArrayMaxWords = 0;
	foreach ($secondArray as $secondWordKey => $secondValue) {
		
		//проверка на то, является ли столбец нечетным и заполнение его слева пустыми пробелами
		if ($secondWordKey % 2 != 0) {
			$output .= str_repeat(" ", $maxLengthInStrArr[$indexArrayMaxWords] - strlen($newWordArrays[$secondKey][$secondWordKey]));
		}
		
		//проверка на то, является ли столбец четным и заполнение его справа пустыми пробелами
		$output .= $newWordArrays[$secondKey][$secondWordKey];
		if ($secondWordKey % 2 == 0) {
			$output .= str_repeat(" ", $maxLengthInStrArr[$indexArrayMaxWords] - strlen($newWordArrays[$secondKey][$secondWordKey]));
		}
		
		//сдвиг индекса и добавление пробела
		++$indexArrayMaxWords;
		$output .= " ";
	}
	$output .= "\n";
}

//итоговый вывод
$output .= "</pre>";
echo $output;

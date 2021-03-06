<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name . ' - О сайте';
$this->breadcrumbs=array(
	'О сайте',
);
?>
<h1>О сайте</h1>

<p>Данный сайт разработан в рамках тестового задания от wargaming.net на позицию PHP Developer.</p>
<p>
<br />Представьте, что вы принимаете участие в разработке приложения «Проведение турнира по шахматам по швейцарской системе». При этом ваша задача - подготовить интерфейс для судьи, по всем этапам проведения:
<br />1.	Ввод списка участников, с указанием их <a href="http://ru.wikipedia.org/wiki/Рейтинг_Эло">ELO рейтинга</a>
<br />2.	Вывод текущего прогресса турнира/финальные результаты.
<br />3.	Расчёт следующего тура, по правилам <a href="http://ru.wikipedia.org/wiki/Швейцарская_система">швейцарской системы</a>
<br />4.	Ввод результатов партий судьёй (победа=1, ничья=0.5, поражение=0).
<br />5.	Все кроме судьи не должны иметь права что-то вводить, только просматривать.
<br />6.	Перерасчет рейтинга ELO на основании результатов турнира.
</p>
<br />
<p>Решение:</p>
<p>Исходники последней версии вы найдёте здесь: <a href="https://github.com/bscheshirwork/cheferee">https://github.com/bscheshirwork/cheferee</a>
</p>

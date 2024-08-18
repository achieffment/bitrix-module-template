<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

echo GetMessage("TEMPLATE"); // Автоматически подключается языковой файл, откуда можно брать сообщения
echo "<br>";
if (!empty($arResult)) {
    foreach ($arResult as $item) {
        // Т.к. template это уже другой объект, то обратиться по this мы не можем, но есть переменная component, которая указывает на экземпляр компонента
        $component->printArray($item);
    }
}
?>

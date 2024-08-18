<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {
    if ($arParams["SET_TITLE"] == "Y") {
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент с использованием контроллеров");
    }

    $arResult["ITEMS"] = $this->getAll();

    // Подключим шаблон
    $this->IncludeComponentTemplate();
}
?>

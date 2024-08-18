<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {
    if ($arParams["SET_TITLE"] == "Y") {
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент с простым применением ajax");
    }

    $elementsCount = 0;
    // Если у компонента включен режим AJAX (AJAX_MODE="Y"), то компонент переходит в режим AJAX,
    // Встреченным ссылкам (<a href=""></a>) и формам автоматически приписывается специальный вычисляемый идентификатор на основе сессии и компонента
    // Клик по такой ссылке или подтверждении формы, bitrix отправляет AJAX запрос (BX.ajax) по этому адресу, вместо перехода
    // Более подробно обо всём - https://yunaliev.ru/file/bitrix_ajax_api.pdf
    if (!isset($_REQUEST["bxajaxid"])) {
        $elementsCount = ($arParams["PAGER_COUNT"]) ? $arParams["PAGER_COUNT"] : 5;
    }

    $arResult["ITEMS"] = $this->getAll($elementsCount);

    // Подключим шаблон
    $this->IncludeComponentTemplate();
}
?>

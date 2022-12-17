<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {

    if ($arParams["SET_TITLE"] == "Y")
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент с использованием контроллеров");

    $elementsCount = ($arParams["PAGER_COUNT"]) ? $arParams["PAGER_COUNT"] : 5;

    $arResult["ITEMS"] = $this->getAll($elementsCount);

    // Подключим шаблон
    $this->IncludeComponentTemplate();

}

?>
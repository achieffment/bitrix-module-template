<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {
    if ($arParams["SET_TITLE"] == "Y") {
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент с постраничной навигацией");
    }

    $elementsCount = ($arParams["PAGER_COUNT"]) ? $arParams["PAGER_COUNT"] : 5;

    // Создадим объект навигации и передадим параметр, который будет являться указателем постраничной навигации (если страница 2, то ?page=2)
    $nav = new \Bitrix\Main\UI\PageNavigation("page");
    $nav->allowAllRecords(true)       // Разрешим кнопку посмотреть всё
        ->setPageSize($elementsCount) // Количество элементов на одной странице
        ->initFromUri();              // Устанавливать ли из URL
    $arOrder = Array("ID" => "DESC");
    $result = \achieffment\books\BookTable::getList(array(
        "order" => $arOrder,
        "limit" => $nav->getLimit(),   // Получим лимит из навигации
        "offset" => $nav->getOffset(), // Получим оффсет из навигации
    ));

    // Максимальное количество элементов для навигации
    $nav->setRecordCount(\achieffment\books\BookTable::getList()->getSelectedRowsCount());

    // Передадим в $arResult выборку элементов и объект навигации
    $arResult["ITEMS"] = $result->fetchAll();
    $arResult["NAV"] = $nav;

    // Подключим шаблон
    $this->IncludeComponentTemplate();
}
?>

<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {

    if ($arParams["SET_TITLE"] == "Y")
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент с постраничной навигацией и кешированием");

    $elementsCount = ($arParams["PAGER_COUNT"]) ? $arParams["PAGER_COUNT"] : 10;

    // Создадим объект навигации и передадим параметр, который будет являться указателем постраничной навигации (если страница 2, то ?page=2)
    $nav = new \Bitrix\Main\UI\PageNavigation("page");
    $nav->allowAllRecords(true)       // Разрешим кнопку посмотреть всё
        ->setPageSize($elementsCount) // Количество элементов на одной странице
        ->initFromUri();              // Устанавливать ли из URL

    $arOrder = Array("ID" => "DESC");

    $bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];

    // Создадим кеш по тегу наших параметров, обязательно передавать навигацию или массив параметров навигации, т.к. результат должен кешироваться по его значениям тоже
    // Если будет меняться массив сортировки, например, добавим какой-нибудь фильтр, то он тоже должен участвовать в кешировании
    // Обязательно отключаем кеширование в компоненте, чтобы использовать своё, иначе полетят стили
    if ($this->StartResultCache(false, array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $bUSER_HAVE_ACCESS, $arOrder, $nav))) {
        $result = \chieff\books\BookTable::getList(array(
            "order" => $arOrder,
            "limit" => $nav->getLimit(),   // Получим лимит из навигации
            "offset" => $nav->getOffset(), // Получим оффсет из навигации
        ));

        // Максимальное количество элементов для навигации
        $nav->setRecordCount(\chieff\books\BookTable::getList()->getSelectedRowsCount());

        // Передадим в $arResult выборку элементов и объект навигации
        $arResult["ITEMS"] = $result->fetchAll();
        $arResult["NAV"] = $nav;

        // Подключим шаблон и закешируем результат
        $this->IncludeComponentTemplate();
    }

}

?>
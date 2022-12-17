<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->checkModule()) {

    if ($arParams["SET_TITLE"] == "Y")
        $APPLICATION->SetTitle("Скелет модуля - Модуль книг - Компонент с кешированием и постраничной навигацией");

    $elementsCount = ($arParams["PAGER_COUNT"]) ? $arParams["PAGER_COUNT"] : 5;
    $nav = new \Bitrix\Main\UI\PageNavigation("page");
    $nav->allowAllRecords(true)
    ->setPageSize($elementsCount)
    ->initFromUri();
    $arOrder = Array("ID" => "DESC");

    $ttl = $arParams["CACHE_TIME"];
    $bUSER_HAVE_ACCESS = $arParams["USE_PERMISSIONS"] ?? "";
    // Дополнительно передаем объект навигации, т.к. при смене его параметров должен меняться и ключ (например при посещении второй страницы пагинации)
    // Если возможно, что меняется массив сортировки, то можно передать и его
    $cacheKey = array(($arParams["CACHE_GROUPS"] === "N" ? false : $USER->GetGroups()), $bUSER_HAVE_ACCESS, $nav, $arOrder);
    $cachePath = "/" . SITE_ID . $this->GetRelativePath();
    if ($this->StartResultCache($ttl, $cacheKey, $cachePath)) {
        $result = \chieff\books\BookTable::getList(array(
            "order" => $arOrder,
            "limit" => $nav->getLimit(),
            "offset" => $nav->getOffset(),
        ));
        $nav->setRecordCount(\chieff\books\BookTable::getList()->getSelectedRowsCount());
        $arResult["ITEMS"] = $result->fetchAll();
        $arResult["NAV"] = $nav;
        $this->IncludeComponentTemplate();
    }

}

?>
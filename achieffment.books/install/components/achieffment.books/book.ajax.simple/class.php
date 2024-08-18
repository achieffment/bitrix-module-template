<?php

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type;
use \achieffment\books\BookTable;
use \achieffment\books\AuthorTable;

class Book extends CBitrixComponent
{
    protected function checkModule() {
        if (!Loader::includeModule("achieffment.books")) {
            ShowError(Loc::getMessage("ACHIEFFMENT_BOOKS_MODULE_NOT_INSTALLED"));

            return false;
        }

        return true;
    }

    function getAll($limit = 0) {
        $params = array(
            "select" => array("ID", "NAME_BOOK" => "NAME"),
            "filter" => array(),
            "order"  => array("ID" => "DESC"),
        );
        if ($limit) {
            $params["limit"] = $limit;
        }
        $result = BookTable::getList($params);

        return $result->fetchAll();
    }
}
?>

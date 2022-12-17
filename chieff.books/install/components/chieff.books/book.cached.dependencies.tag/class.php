<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type;
use \chieff\books\BookTable;
use \chieff\books\AuthorTable;
class Book extends CBitrixComponent {
    protected function checkModule() {
        if (!Loader::includeModule("chieff.books")) {
            ShowError(Loc::getMessage("CHIEFF_BOOKS_MODULE_NOT_INSTALLED"));
            return false;
        }
        return true;
    }
    function getAll() {
        $result = BookTable::getList(array(
            "select" => array("ID", "NAME_BOOK" => "NAME"),
            "filter" => array(),
            "order" => array("ID" => "DESC"),
        ));
        return $result->fetchAll();
    }
}
?>
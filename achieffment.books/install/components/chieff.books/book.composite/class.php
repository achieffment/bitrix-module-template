<?php

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type;
use \achieffment\books\BookTable;
use \achieffment\books\AuthorTable;

class Book extends CBitrixComponent {

    protected function checkModule() {
        if (!Loader::includeModule("achieffment.books")) {
            ShowError(Loc::getMessage("ACHIEFFMENT_BOOKS_MODULE_NOT_INSTALLED"));
            return false;
        }
        return true;
    }

    function addElement($active, $type, $name, $released, $isbn, $author_id, $time_arrival, $description) {
        $result = BookTable::add(
            array(
                "ACTIVE" => $active,
                "TYPE" => $type,
                "NAME" => $name,
                "RELEASED" => $released,
                "ISBN" => $isbn,
                "AUTHOR_ID" => intval($author_id),
                "TIME_ARRIVAL" => new Type\DateTime($time_arrival),
                "DESCRIPTION" => $description,
            )
        );
        return $result;
    }

    function getAll() {
        $result = BookTable::getList(array(
            "select" => array("ID", "NAME_BOOK" => "NAME"),
            "filter" => array(), // Описание поля WHERE и HAVING
            "order" => array("ID" => "DESC"),
            "runtime" => array(
                new Bitrix\Main\Entity\ExpressionField("CNT", "COUNT(*)")
            )
        ));
        return $result->fetchAll();
    }

    function getListWithReferences() {
        $result = BookTable::getList(array(
            "select" => array("NAME", "AUTHOR.NAME", "AUTHOR.LAST_NAME")
        ));
        return $result->fetchAll();
    }

    function getListBackReference() {
        $result = AuthorTable::getList(array(
            "select" => array(
                "NAME",
                "LAST_NAME",
                // Сначала пишется сущность с которой связана текущая (таблица авторов связана с книгой), через двоиточие связанное поле (автор), и через точку имя поля из связанной сущности (название книги)
                "BOOK_NAME" => "\achieffment\books\BookTable:AUTHOR.NAME"
            )
        ));
        return $result->fetchAll();
    }

    function checkResult($result, $operation = "add") {
        if ($result->isSuccess()) {
            if ($operation == "add")
                return ["Added_ID", $result->getId()];
            else
                return true;
        }
        return $result->getErrorMessages();
    }

    function printArray($array) {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
        echo "<br>";
    }

}

?>
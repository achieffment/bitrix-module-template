<?php

namespace achieffment\books;

use \Bitrix\Main\Entity;

// Вторая таблица, с которой связана первая
// Хранит информацию об авторах книг

class AuthorTable extends Entity\DataManager
{
    public static function getTableName() {
        return "achieffment_books_authors_table";
    }

    public static function getMap() {
        return Array(
            new Entity\IntegerField(
                "ID",
                Array(
                    "primary" => true,
                    "autocomplete" => true,
                )
            ),
            new Entity\StringField(
                "NAME",
                Array(
                    "required" => true,
                )
            ),
            new Entity\StringField("LAST_NAME")
        );
    }
}

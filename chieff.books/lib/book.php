<?php

namespace chieff\books;

// Создадим ORM-сущность для хранения информации о книгах, свяжем её со второй таблицей, где будет храниться информация об авторе

use \Bitrix\Main\Entity;
use \Bitrix\Main\Type;

// Имя файла задано book, но подключаться он сможет даже при условии, что класс называется не Book, а BookTable, но в ряде случаев все равно нужно будет использовать Book, например при регистрации событий
class BookTable extends Entity\DataManager {

    // Название таблицы в базе данных:
    // Если не указывать данную функцию, то таблица в бд сформируется автоматически из неймспейса
    // Например: b_chieff_books_book
    public static function getTableName() {
        return "chieff_books_books_table";
    }

    // Если не указывать, то будет использовано значение по умолчанию подключения к бд из файла .settings.php
    // Если указать, то можно выбрать подключение, которое может быть описано в .setting.php
    public static function getConnectionName() {
        return "default";
    }

    // Метод возвращающий структуру ORM-сущности
    public static function getMap() {
        /*
         * Типы полей:
         * DatetimeField
         * DateField
         * BooleanField
         * IntegerField
         * FloatField
         * EnumField
         * TextField
         * StringField
         */
        return Array(
            new Entity\IntegerField(
                "ID", // // Имя сущности
                Array(
                    // Указываем, что это первичный ключ
                    "primary" => true,
                    // AUTO INCREMENT
                    "autocomplete" => true,
                )
            ),
            // Полю типа boolean можно назначить зачения, которые будут храниться в бд
            // Но они всегда возвращаются в виде true или false, записывать можно и так, и так
            new Entity\BooleanField(
                'ACTIVE',
                Array(
                    "values" => Array('N','Y')
                )
            ),
            // Поле списка, можно передавать только заданные значения
            new Entity\EnumField(
                "TYPE", // // Имя сущности
                Array(
                    "values" => Array('Техническая литература', 'Художественная литература', 'Научная литература'),
                )
            ),
            new Entity\StringField(
                "NAME", // // Имя сущности
                Array(
                    // Обязательное поле
                    "required" => true,
                )
            ),
            new Entity\IntegerField(
                "RELEASED", // // Имя сущности
                Array(
                    // Обязательное поле
                    "required" => true,
                )
            ),
            new Entity\StringField(
                "ISBN", // // Имя сущности
                Array(
                    // Обязательное поле
                    "required" => true,
                    // Имя колонки в таблице
                    "column_name" => "ISBNCODE",
                    // Если необходима валидация поля, то используем массив валидации, можем передать сколько угодно валидаторов, использовать как штатные, так и самописные
                    "validation" => function() {
                        return Array(
                            // Первым укажем штаный валидатор проверки на уникальность поля
                            new Entity\Validator\Unique,
                            // Вторым напишем свою функцию, которая проверит на длину строки и очистит её
                            function ($value, $primary, $row, $field) {
                                // value - значение поля
                                // primary - массив с первичным ключом, в данном случае [ID => 1]
                                // row - весь массив данных, переданный в ::add или ::update
                                // field - объект валидируемого поля - Entity\StringField('ISBN', ...)
                                $clean = str_replace(['-', ' '], '', $value);
                                if (preg_match("/^\d{1,13}$/", $clean))
                                    return true;
                                else
                                    return "Код ISBN должен содержать не более 13 цифр, разделенных дефисом или пробелами";
                            }
                        );
                    }
                )
            ),
            // Поле для хранения айди автора, информация о которых будет храниться в другой таблице, свяжем данную с ней
            new Entity\IntegerField("AUTHOR_ID"),
            // Только айди не достаточно для связи двух таблиц, для этого нужно будет создать поле зависимости
            // Фактически такого поля нет в базе, оно является виртуальным
            new Entity\ReferenceField(
                "AUTHOR", // Имя сущности
                '\chieff\books\AuthorTable',         // Связываемая сущность
                array("=this.AUTHOR_ID" => "ref.ID") // this - текущая сущность, ref - связываемая
            ),
            // Поле даты времени, можно передавать только в формате даты и времени
            // Например: new \Bitrix\Main\Type\DateTime(date("d.m.Y H:i:s"))
            new Entity\DatetimeField("TIME_ARRIVAL"),
            new Entity\TextField("DESCRIPTION"),
            // Если требуется поле, которое не должно храниться в бд и вычисляться на стороне SQL, то есть специальный тип
            // Такое поле будет доступно только при выборке, т.к. оно нигде не хранится
            new Entity\ExpressionField(
                "AGE_YEAR",           // Имя сущности
                "YEAR(CURDATE())-%s", // Выражение для вычисления, в которое подставляются поля ORM сущности в формате sprintf из массива далее
                Array("RELEASED")     // Массив с именами сущностей, которые необходимо подставить в выражение
            ),
        );
    }

//    // События можно задавать прямо в ORM-сущности
//    // Для примера запретим изменять поле ISBN
//    public static function onBeforeUpdate(Entity\Event $event) {
//        $result = new Entity\EventResult;
//        $data = $event->getParameter("fields");
//        if (isset($data["ISBN"])) {
//            $result->addError(
//                new Entity\FieldError(
//                    $event->getEntity()->getField("ISBN"),
//                    "Запрещено менять ISBN код у существующих книг"
//                )
//            );
//        }
//        return $result;
//    }

}
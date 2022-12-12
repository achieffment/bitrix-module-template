<?php

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type;
use \chieff\books\BookTable;
use \chieff\books\AuthorTable;

class Book extends CBitrixComponent {

    // Логика в компоненте делится на class.php и component.php
    // class.php необходим в случае, если логику нужно вынести в основной класс, если этого не требуется, то файл class.php не обязателен
    // Или же наоборот, можно всё вынести в class.php и не создавать component.php

    protected function checkModule() {
        if (!Loader::includeModule("chieff.books")) {
            ShowError(Loc::getMessage("CHIEFF_BOOKS_MODULE_NOT_INSTALLED"));
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

        // Можно вызывать без параметров, тогда будет выбрано всё
        $result = BookTable::getList(array(
            // Если нужно использовать другое название поля для результирующего массива, то можно использовать алиас в селекте ("NAME_BOOK" => "NAME", вместо NAME, вернется NAME_BOOK)
            // Если нужно вывести все поля, то передаем один элемент "*", или не указывать
            "select" => array("ID", "NAME_BOOK" => "NAME"),
            // Можно использовать выражения AND, OR аналогично фильтрации в инфоблоках
            "filter" => array(), // Описание поля WHERE и HAVING
            // "group" => array(), // Явное указание полей для группировки
            "order" => array("ID" => "DESC"),
            // "limit" => 10, // Количество записей
            // "offset" => 0, // Номер записи с которой выбирать
            // runtime - дополнительное поле для вычислений при выборке и группировке, например подсчет количества элементов в SQL
            // После объявления такого поля, его необходимо указать в select, также можно использовать и в filter и т.п.
            // Вообще можно не указывать runtime, если необходим только в select, можно записать сразу туда
            // По сути, это поле, которое как бы добавляет колонку в ORM-сущность, если она не была там описана в getMap()
            // Например, если у нас появился дополнительный столбик в таблице, а мы его не описали, то его можно получить в runtime, то есть это не обязательно может быть expression поле, но и другое, например Main\Entity\IntegerField, как при описывании ORM-сущности
            "runtime" => array(
                new Bitrix\Main\Entity\ExpressionField("CNT", "COUNT(*)")
            )
        ));

        // Другие вызовы
        // getById($id) - возвращает объект по ключу
        // getByPrimary($primary, $parameters) - возвращает объект по ключу, но также может принимать параметры для выборки и есть возможность явно указать первичный ключ например ::getByPrimaty(array("ID" => 1));
        // getRowById($id) - возвращает сразу результирующий массив по ключу, а не объект
        // getRow($parametes) - тот же гетлист, только с ограничением в одну строку, но возвращает сразу результирующий массив

        // getList состоит из процессов над объектом Query
        // Query рационально использовать, только если используется много подготовительных работ и запрос собирается постепенно и долго
        // Разобрать его можно так:
        // $q = new Main\Entity\Query(BookTable::getEntity());
        // $q->setSelect(array("ID", "NAME_BOOK" => "NAME"));
        // $q->setFilter(array());
        // $q->setOrder(array("ID"=>"DESC"));
        // $q->setLimit(3);
        // $q->setOffset(2);
        // $result = $q->exec();
        // $result = $result->fetchAll();
        // Имеет и ряд других методов, типа addSelect, getSelect, getFilter, regsiterRuntimeField и другие

        return $result->fetchAll();
    }

    function getListPager($count = 5) {

    }

    function getListWithReferences() {

        // Выборка с полями связанной сущности
        $result = BookTable::getList(array(
            "select" => array("NAME", "AUTHOR.NAME", "AUTHOR.LAST_NAME")
        ));
        return $result->fetchAll();

    }

    function getListBackReference() {

        // Если у нас есть связь одной таблицы с другой, а нужно получить из второй элементы, и связанные с ними элементы
        // То есть первая таблица книги, у которой есть привязка к автору, там всё понятно, но если нужно получить авторов и все связанные книги? А в таблице авторов даже не хранятся айди книг
        // Тогда используем такой синтаксис
        $result = AuthorTable::getList(array(
            "select" => array(
                "NAME",
                "LAST_NAME",
                // Сначала пишется сущность с которой связана текущая (таблица авторов связана с книгой), через двоиточие связанное поле (автор), и через точку имя поля из связанной сущности (название книги)
                "BOOK_NAME" => "\chieff\books\BookTable:AUTHOR.NAME"
            )
        ));
        // Также можно сделать связи многим ко многим через создание промежуточной таблицы - https://www.youtube.com/watch?v=1k-OnyUr13I&list=PLzPivwyXljVVdpY3tRZun3XeuI0L4bF8x&index=32
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

//    // Если у нас существует component.php, то функцию вызова компонента использовать нельзя
//    // Но если у нас его нет, то в эту функцию нужно поместить всё, что должно работать при вызове
//    // Здесь же подключается $this->IncludeComponentTemplate(); (в конце) для подключения шаблона, в случае если используем
//    // Внимание! При выполнении компонента в аяксовом режиме метод CBitrixComponent::executeComponent() не запускается.
//    public function executeComponent() {
//        $this->includeComponentLang("class.php");
//        if ($this->checkModules()):
//            $this->IncludeComponentTemplate();
//        endif;
//    }

}
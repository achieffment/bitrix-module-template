<?php

namespace chieff\books;

// Класс агента
// Для примера функция пишет в папку модуля время

class Agent {

    static public function superAgent() {
        if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/chieff.books/"))
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/chieff.books/superAgentLog.txt", date("Y-m-d H:i:s"), FILE_APPEND);
        elseif (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/chieff.books/"))
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/local/modules/chieff.books/superAgentLog.txt", date("Y-m-d H:i:s"), FILE_APPEND);
        // Функция обязательно должна возвращать своё имя, иначе удалится
        return "superAgent();";
    }

}
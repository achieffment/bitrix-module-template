<?php

namespace achieffment\books;

// Класс агента
// Для примера функция пишет в папку модуля время

class Agent
{
    public static function superAgent() {
        if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/achieffment.books/")) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/achieffment.books/superAgentLog.txt", date("Y-m-d H:i:s"), FILE_APPEND);
        } elseif (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/")) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/local/modules/achieffment.books/superAgentLog.txt", date("Y-m-d H:i:s"), FILE_APPEND);
        }

        return "\achieffment\books\Agent::superAgent();"; // Функция обязательно должна возвращать имя по которому вызывается, иначе битрикс её удаляет
    }
}

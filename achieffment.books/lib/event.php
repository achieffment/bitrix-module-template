<?php

namespace achieffment\books;

// Класс события
// Для примера выводит поля при каком-либо действии (в регистраторе задано перед добавлением)

class Event
{
    public static function eventHandler(\Bitrix\Main\Entity\Event $event) {
        $fields = $event->getParameter("fields");
        echo "<pre>";
        echo "Обработчик события";
        var_dump($fields);
        echo "</pre>";
    }
}

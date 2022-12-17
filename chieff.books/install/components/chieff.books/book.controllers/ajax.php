<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Engine\Controller;

// Пример использования контроллеров в компоненте

// Объявляется класс контроллера
class CustomAjaxController extends Controller
{

    public function testAction($param1 = '') {
        $result = [
            'param1' => $param1,
        ];
        // Получаем передаваемые параметры из script.js
        $parameters = $this->getUnsignedParameters();
        // Если строка не пустая, то возвращаем её вместе с результатом
        if ($parameters)
            $result['params'] = $parameters;
        return $result;
    }

    /*
        // Вызов метода контроллера, аналогичен class.php, заменяется только mode
        BX.ajax.runComponentAction('chieff.books:book.controllers', 'test', {
            // mode - метод вызова, может быть class.php или ajax.php, в зависимости от того, что хотим вызвать
            mode: 'ajax',
            // Указываем параметры, которые будем передавать
            data: {
                param1: 'testParam'
            }
        }).then(function (response) {
            // При удачном вызове
            console.log(response);
        }, function (response) {
            // При неудачном вызове
            console.log(response);
        });
     */

}
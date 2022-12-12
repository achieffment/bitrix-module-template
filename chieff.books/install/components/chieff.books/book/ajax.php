<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Engine\Controller;

// Пример использования Ajax в компоненте

// Объявляется класс контроллера
class CustomAjaxController extends Controller
{

    // Есть возможность создавать классы-действия, это требуется, когда необходимо повторно использовать логику в нескольких контроллерах
    // Проще говоря - когда нужно использовать другой контроллер из другого класса, с возможностью ещё и переопределить параметры
    // public function configureActions()
    // {
    //     return [
    //         'testoPresto' => [
    //             'class' => \TestAction::class,
    //             'configure' => [
    //                 'who' => 'Me',
    //             ],
    //         ],
    //     ];
    // }

    // Описывается сам метод (должен иметь суффикс Action)
    public static function testAction($param1)
    {
        return [
            'param1' => $param1,
        ];
    }

    /*
     *  Вызов (указывается vendor, компонент, далее указывается действие (без суффикса)
        BX.ajax.runComponentAction('chieff.books:book', 'test', {
            // mode - метод вызова, может иметь второе значение - class, используется если описываем контроллер в классе компонента, а не в ajax.php
            mode: 'ajax',
            // Указываем параметры, которые будем передавать
            data: {
                param1: 'asd'
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
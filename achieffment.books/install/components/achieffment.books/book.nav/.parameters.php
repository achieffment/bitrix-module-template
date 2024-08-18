<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"GROUPS" => Array(),
	"PARAMETERS" => Array(
        "PAGER_COUNT" => array(
            "PARENT"  => "BASE",
            "NAME"    => "Количество элементов для вывода",
            "TYPE"    => "STRING",
            "DEFAULT" => 5,
        ),
        "AJAX_MODE" => array(),
        "SET_TITLE" => array(),
        "CACHE_TIME" => array(),
	),
);
?>

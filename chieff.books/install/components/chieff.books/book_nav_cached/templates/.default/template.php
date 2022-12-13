<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult["ITEMS"])): ?>
    <? foreach ($arResult["ITEMS"] as $item): ?>
        <pre><? print_r($item); ?></pre>
    <?php endforeach; ?>
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:main.pagenavigation",
        ".default",
        array(
            'NAV_TITLE'   => 'Элементы',
            "NAV_OBJECT"  => $arResult["NAV"],
            "SEF_MODE" => "N",
        ),
        null,
        false,
        true
    );
    ?>
<?php endif; ?>
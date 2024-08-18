<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<!--
Указываем любой айди, чтобы подвязаться к контейнеру, и в любой аттрибут выводим нашу функцию
Метод выведет зашифрованную строку, шифрование необходимо, чтобы никто не смог получить информацию о вызове
Передача параметров описана в script.js
-->
<div id="testControllerContainer" data-ar-params=<?= $component->getSignedParameters() ?>>
    <? if (!empty($arResult["ITEMS"])): ?>
        <? foreach ($arResult["ITEMS"] as $item): ?>
            <pre><? print_r($item); ?></pre>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

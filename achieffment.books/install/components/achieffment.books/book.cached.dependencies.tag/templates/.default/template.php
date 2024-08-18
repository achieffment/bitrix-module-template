<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Включаем голосование "за" технологии композитный сайт
$this->setFrameMode(true);
if (!empty($arResult["ITEMS"])): ?>
    <? foreach ($arResult["ITEMS"] as $item): ?>
        <pre><? print_r($item); ?></pre>
    <?php endforeach; ?>
<?php endif; ?>

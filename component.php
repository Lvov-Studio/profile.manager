<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

Loader::includeModule('iblock');

global $USER;
$userId = (int)$USER->GetID();

// Пользователь (для приветствия/формы)
$arResult['USER'] = [];
if ($userId > 0) {
    if ($rs = CUser::GetByID($userId)) {
        if ($u = $rs->Fetch()) {
            $arResult['USER'] = $u;
        }
    }
}

// ===== МОИ ПРОЕКТЫ (ИБ 5, свойство USERID) =====
$arResult['PROJECTS'] = [];
$prRes = CIBlockElement::GetList(
    ['ID' => 'DESC'],
    [
        'IBLOCK_ID'       => 5,
        'ACTIVE'          => 'Y',
        'PROPERTY_USERID' => $userId, // ВАЖНО: именно USERID
    ],
    false,
    false,
    ['ID','NAME','CODE']
);
while ($row = $prRes->GetNext()) {
    $arResult['PROJECTS'][] = $row;
}

// ===== МОИ УСЛУГИ (ИБ 7, свойство USER) =====
// ИБ 6 — справочник услуг (общие названия)
$arResult['SERVICES'] = [];
$mySrvRes = CIBlockElement::GetList(
    ['ID' => 'DESC'],
    [
        'IBLOCK_ID'     => 7,
        'ACTIVE'        => 'Y',
        'PROPERTY_USER' => $userId,
    ],
    false,
    false,
    [
        'ID','NAME','DETAIL_TEXT',
        'PROPERTY_SERVICE',
        'PROPERTY_DAYSMIN','PROPERTY_DAYSMAX',
        'PROPERTY_PRICEMIN','PROPERTY_PRICEMAX',
    ]
);

// Подтягиваем название услуги из справочника (ИБ 6)
$serviceNames = [];
while ($row = $mySrvRes->GetNext()) {
    $sid = (int)$row['PROPERTY_SERVICE_VALUE'];
    $row['SERVICE_NAME'] = '';
    if ($sid > 0) {
        if (!isset($serviceNames[$sid])) {
            $ref = CIBlockElement::GetList([], ['IBLOCK_ID'=>6,'ID'=>$sid,'ACTIVE'=>'Y'], false, false, ['ID','NAME'])->GetNext();
            $serviceNames[$sid] = $ref ? $ref['NAME'] : '';
        }
        $row['SERVICE_NAME'] = $serviceNames[$sid];
    }
    $arResult['SERVICES'][] = $row;
}

$this->IncludeComponentTemplate();

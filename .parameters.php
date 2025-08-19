<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    'PARAMETERS' => [
        'PROJECT_IBLOCK_ID' => [
            'PARENT'  => 'BASE',
            'NAME'    => 'IBLOCK ID проектов',
            'TYPE'    => 'STRING',
            'DEFAULT' => '5',
        ],
        'SERVICES_IBLOCK_ID' => [
            'PARENT'  => 'BASE',
            'NAME'    => 'IBLOCK ID справочника услуг',
            'TYPE'    => 'STRING',
            'DEFAULT' => '6',
        ],
        'USER_SERVICES_IBLOCK_ID' => [
            'PARENT'  => 'BASE',
            'NAME'    => 'IBLOCK ID услуг пользователя',
            'TYPE'    => 'STRING',
            'DEFAULT' => '7',
        ],
        'PROJECT_USER_PROP_CODE' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Код свойства проекта: пользователь',
            'TYPE'    => 'STRING',
            'DEFAULT' => 'USERID',
        ],
        'PROJECT_GALLERY_PROP_CODE' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Код свойства проекта: галерея',
            'TYPE'    => 'STRING',
            'DEFAULT' => 'GALLERY', // хранит ID файлов (тип N или F)
        ],
        'PROJECT_SERVICES_PROP_CODE' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Код свойства проекта: услуги',
            'TYPE'    => 'STRING',
            'DEFAULT' => 'SERVICES',
        ],
        'RATING_PROP_CODE' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Код свойства проекта: рейтинг',
            'TYPE'    => 'STRING',
            'DEFAULT' => 'RATING',
        ],
        'VOTES_PROP_CODE' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Код свойства проекта: кол-во голосов',
            'TYPE'    => 'STRING',
            'DEFAULT' => 'VOTES_COUNT',
        ],
        'GALLERY_LIMIT' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Максимум фото в галерее',
            'TYPE'    => 'STRING',
            'DEFAULT' => '20',
        ],
        'LOGIN_PAGE' => [
            'PARENT'  => 'ADDITIONAL_SETTINGS',
            'NAME'    => 'Страница логина',
            'TYPE'    => 'STRING',
            'DEFAULT' => '/login/',
        ],
    ],
];

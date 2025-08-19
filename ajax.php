<?php
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;

global $USER;
if (!$USER->IsAuthorized()) {
    die(json_encode(["status" => "error", "message" => "Вы не авторизованы"]));
}

if (!check_bitrix_sessid()) {
    die(json_encode(["status" => "error", "message" => "Ошибка сессии"]));
}

$request = Application::getInstance()->getContext()->getRequest();
$action = $request->getPost("action");
$response = ["status" => "error", "message" => "Неизвестное действие"];

switch ($action) {
    case "update_profile":
        $fields = [
            "NAME"        => $request->getPost("NAME"),
            "LAST_NAME"   => $request->getPost("LAST_NAME"),
            "EMAIL"       => $request->getPost("EMAIL"),
            "PERSONAL_PHONE" => $request->getPost("PHONE"),
            "UF_DESCRIPTION" => $request->getPost("DESCRIPTION"),
        ];

        if (!empty($_FILES["PHOTO"])) {
            $fileId = CFile::SaveFile($_FILES["PHOTO"], "main");
            if ($fileId) {
                $fields["PERSONAL_PHOTO"] = $fileId;
            }
        }

        $user = new CUser();
        if ($user->Update($USER->GetID(), $fields)) {
            $response = ["status" => "success", "message" => "Профиль обновлён"];
        } else {
            $response = ["status" => "error", "message" => $user->LAST_ERROR];
        }
        break;

    case "delete_profile":
        // Удаление проектов и услуг
        $res = CIBlockElement::GetList([], ["IBLOCK_ID" => 5, "PROPERTY_USER" => $USER->GetID()], false, false, ["ID"]);
        while ($pr = $res->Fetch()) CIBlockElement::Delete($pr["ID"]);

        $res = CIBlockElement::GetList([], ["IBLOCK_ID" => 7, "PROPERTY_USER" => $USER->GetID()], false, false, ["ID"]);
        while ($srv = $res->Fetch()) CIBlockElement::Delete($srv["ID"]);

        CUser::Delete($USER->GetID());
        $response = ["status" => "success", "message" => "Профиль удалён"];
        break;

    case "save_project":
        $el = new CIBlockElement();
        $projectId = (int)$request->getPost("ID");

        $fields = [
            "IBLOCK_ID" => 5,
            "NAME"      => $request->getPost("NAME"),
            "ACTIVE"    => "Y",
            "PROPERTY_VALUES" => [
                "USER" => $USER->GetID(),
                "SERVICES" => $request->getPost("SERVICES") ?: [],
            ]
        ];

        if ($projectId > 0) {
            $el->Update($projectId, $fields);
            $response = ["status" => "success", "message" => "Проект обновлён", "id" => $projectId];
        } else {
            $projectId = $el->Add($fields);
            if ($projectId) {
                $response = ["status" => "success", "message" => "Проект создан", "id" => $projectId];
            }
        }
        break;

    default:
        $response = ["status" => "error", "message" => "Неизвестное действие"];
}

header("Content-Type: application/json");
echo json_encode($response);

<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\SystemException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CustomProfileManagerComponent extends CBitrixComponent implements Controllerable
{
    // Без типизации для совместимости с PHP < 7.4
    protected $USER_ID = 0;

    /* ================== params / boot ================== */

    public function onPrepareComponentParams($p)
    {
        foreach ([
            'PROJECT_IBLOCK_ID','SERVICES_IBLOCK_ID','USER_SERVICES_IBLOCK_ID',
            'PROJECT_USER_PROP_CODE','PROJECT_GALLERY_PROP_CODE','PROJECT_SERVICES_PROP_CODE',
            'RATING_PROP_CODE','VOTES_PROP_CODE','GALLERY_LIMIT','LOGIN_PAGE',

            // коды свойств ИБ "услуги пользователя"
            'USVC_SERVICE_PROP_CODE','USVC_USER_PROP_CODE',
            'USVC_DAYSMIN_PROP_CODE','USVC_DAYSMAX_PROP_CODE',
            'USVC_PRICEMIN_PROP_CODE','USVC_PRICEMAX_PROP_CODE',
        ] as $k) { $p[$k] = trim((string)($p[$k] ?? '')); }

        $p['PROJECT_IBLOCK_ID']       = (int)$p['PROJECT_IBLOCK_ID'] ?: 5;
        $p['SERVICES_IBLOCK_ID']      = (int)$p['SERVICES_IBLOCK_ID'] ?: 6;
        $p['USER_SERVICES_IBLOCK_ID'] = (int)$p['USER_SERVICES_IBLOCK_ID'] ?: 7;

        $p['PROJECT_USER_PROP_CODE']     = $p['PROJECT_USER_PROP_CODE']     ?: 'USERID';
        $p['PROJECT_GALLERY_PROP_CODE']  = $p['PROJECT_GALLERY_PROP_CODE']  ?: 'GALLERY';
        $p['PROJECT_SERVICES_PROP_CODE'] = $p['PROJECT_SERVICES_PROP_CODE'] ?: 'SERVICES';
        $p['RATING_PROP_CODE']           = $p['RATING_PROP_CODE']           ?: 'RATING';
        $p['VOTES_PROP_CODE']            = $p['VOTES_PROP_CODE']            ?: 'VOTES_COUNT';

        // дефолты кодов свойств "услуги пользователя"
        $p['USVC_SERVICE_PROP_CODE']  = $p['USVC_SERVICE_PROP_CODE']  ?: 'SERVICE';
        $p['USVC_USER_PROP_CODE']     = $p['USVC_USER_PROP_CODE']     ?: 'USER';
        $p['USVC_DAYSMIN_PROP_CODE']  = $p['USVC_DAYSMIN_PROP_CODE']  ?: 'DAYSMIN';
        $p['USVC_DAYSMAX_PROP_CODE']  = $p['USVC_DAYSMAX_PROP_CODE']  ?: 'DAYSMAX';
        $p['USVC_PRICEMIN_PROP_CODE'] = $p['USVC_PRICEMIN_PROP_CODE'] ?: 'PRICEMIN';
        $p['USVC_PRICEMAX_PROP_CODE'] = $p['USVC_PRICEMAX_PROP_CODE'] ?: 'PRICEMAX';

        $p['GALLERY_LIMIT'] = (int)$p['GALLERY_LIMIT'] ?: 20;
        $p['LOGIN_PAGE']    = $p['LOGIN_PAGE'] ?: '/login/';

        return parent::onPrepareComponentParams($p);
    }

    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->needModules();
    }

    protected function needModules()
    {
        foreach (['iblock'] as $m) {
            if (!Loader::includeModule($m)) {
                throw new SystemException('Не удалось подключить модуль: '.$m);
            }
        }
    }

    protected function initUser()
    {
        global $USER;
        $this->USER_ID = (int)($USER ? $USER->GetID() : 0);
        if ($this->USER_ID <= 0) throw new SystemException('Требуется авторизация');
    }

    protected function assertAuthorized()
    {
        global $USER;
        if (!$USER->IsAuthorized()) LocalRedirect($this->arParams['LOGIN_PAGE']);
        $this->USER_ID = (int)$USER->GetID();
    }

    /* ================== helpers ================== */

    protected function normalize($s, $max = 500)
    {
        $s = trim(strip_tags((string)$s));
        if ($max > 0 && mb_strlen($s) > $max) $s = mb_substr($s, 0, $max);
        return $s;
    }

    protected function translitCode($projectName, $user)
    {
        $projectName = (string)$projectName;
        $base = $projectName;
        if (class_exists('\CUtil') && method_exists('\CUtil','translit')) {
            $base = \CUtil::translit($projectName,'ru',['replace_space'=>'-','replace_other'=>'-','max_len'=>80]);
            $uN   = \CUtil::translit((string)($user['NAME']??''),'ru',['replace_space'=>'-','replace_other'=>'-']);
            $uF   = \CUtil::translit((string)($user['LAST_NAME']??''),'ru',['replace_space'=>'-','replace_other'=>'-']);
            return strtolower(trim($base.'-'.$uN.'-'.$uF,'-'));
        }
        return strtolower(trim(preg_replace('~[^a-z0-9\-]+~iu','-',$projectName),'-'));
    }

    public function configureActions()
    {
        $csrf = new ActionFilter\Csrf();
        $auth = new ActionFilter\Authentication();
        $post = new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]);

        return [
            'saveProfile'          => ['prefilters' => [$auth, $post, $csrf]],
            'uploadAvatar'         => ['prefilters' => [$auth, $post, $csrf]],
            'saveProject'          => ['prefilters' => [$auth, $post, $csrf]],
            'uploadProjectImages'  => ['prefilters' => [$auth, $post, $csrf]],
            'deleteImage'          => ['prefilters' => [$auth, $post, $csrf]],
            'sortGallery'          => ['prefilters' => [$auth, $post, $csrf]],
            'saveServices'         => ['prefilters' => [$auth, $post, $csrf]],
            'deleteProject'        => ['prefilters' => [$auth, $post, $csrf]],
            'deleteAccount'        => ['prefilters' => [$auth, $post, $csrf]],
            'logout'               => ['prefilters' => [$auth, $post, $csrf]],
            'resetNewGallery'      => ['prefilters' => [$auth, $post, $csrf]],
        ];
    }

    protected function saveImage(array $file, $maxSize = 5242880)
    {
        if (!is_uploaded_file($file['tmp_name'] ?? '')) return ['error'=>'Файл не загружен'];
        if ((int)$file['size'] > $maxSize) return ['error'=>'Файл слишком большой (макс. 5 МБ)'];
        $check = \CFile::CheckImageFile($file,0,0,0);
        if ($check) return ['error'=>$check];
        $fid = \CFile::SaveFile($file,'usersprojects');
        return $fid ? ['id'=>(int)$fid] : ['error'=>'Не удалось сохранить файл'];
    }

    protected function getGalleryIds($projectId)
    {
        $ids = [];
        $pr = CIBlockElement::GetProperty($this->arParams['PROJECT_IBLOCK_ID'],$projectId,[],[
            'CODE'=>$this->arParams['PROJECT_GALLERY_PROP_CODE']
        ]);
        while ($p = $pr->Fetch()) if ((int)$p['VALUE']>0) $ids[] = (int)$p['VALUE'];
        return $ids;
    }

    protected function &getNewGalleryRef()
    {
        if (!isset($_SESSION['PM_NEW_GALLERY']) || !is_array($_SESSION['PM_NEW_GALLERY']))
            $_SESSION['PM_NEW_GALLERY'] = [];
        if (!isset($_SESSION['PM_NEW_GALLERY'][$this->USER_ID]) || !is_array($_SESSION['PM_NEW_GALLERY'][$this->USER_ID]))
            $_SESSION['PM_NEW_GALLERY'][$this->USER_ID] = [];
        return $_SESSION['PM_NEW_GALLERY'][$this->USER_ID];
    }
    protected function getNewGallery()
    {
        $ref = $this->getNewGalleryRef();
        return array_values(array_map('intval',$ref));
    }

    protected function assertProjectOwner($projectId)
    {
        $sel = ['ID','NAME','CODE','PROPERTY_'.$this->arParams['PROJECT_USER_PROP_CODE'].''];
        $flt = ['IBLOCK_ID'=>$this->arParams['PROJECT_IBLOCK_ID'],'=ID'=>$projectId];
        $res = CIBlockElement::GetList([], $flt, false, false, $sel);
        if (!($el = $res->GetNext())) throw new SystemException('Проект не найден');

        $propUser = 'PROPERTY_'.$this->arParams['PROJECT_USER_PROP_CODE'].'_VALUE';
        if ((int)$el[$propUser] !== $this->USER_ID) throw new SystemException('Нет доступа к проекту');
        return $el;
    }

    /* ================== actions ================== */

    public function saveProfileAction($name,$surname,$description,$contacts)
    {
        $this->initUser();
        $u = new CUser();
        $ok = $u->Update($this->USER_ID,[
            'NAME'=>$this->normalize($name,100),
            'LAST_NAME'=>$this->normalize($surname,100),
            'WORK_NOTES'=>$this->normalize($description,100),
            'WORK_PHONE'=>$this->normalize($contacts,200),
        ]);
        if (!$ok) throw new SystemException($u->LAST_ERROR ?: 'Не удалось обновить профиль');
        return ['status'=>'ok'];
    }

    public function uploadAvatarAction()
    {
        $this->initUser();
        $req  = Context::getCurrent()->getRequest();
        $file = $req->getFile('file');
        $saved = $this->saveImage($file);
        if (!isset($saved['id'])) throw new SystemException($saved['error']);

        $ar = CUser::GetByID($this->USER_ID)->Fetch();
        if ((int)$ar['PERSONAL_PHOTO']>0) \CFile::Delete((int)$ar['PERSONAL_PHOTO']);
        $u = new CUser();
        if (!$u->Update($this->USER_ID,['PERSONAL_PHOTO'=>$saved['id']]))
            throw new SystemException($u->LAST_ERROR ?: 'Не удалось обновить фото');

        $thumb = CFile::ResizeImageGet($saved['id'],['width'=>400,'height'=>400],BX_RESIZE_IMAGE_EXACT,false);
        return ['status'=>'ok','src'=>$thumb['src']??''];
    }

    public function saveProjectAction($projectId,$name,$desc,$clientName,$clientDesc,array $services=[])
    {
        $this->initUser();

        $projectId = (int)$projectId;
        $name      = $this->normalize($name,200);
        $user      = CUser::GetByID($this->USER_ID)->Fetch();
        $code      = $this->translitCode($name,$user);

        $fields = [
            'IBLOCK_ID'=>$this->arParams['PROJECT_IBLOCK_ID'],
            'ACTIVE'=>'Y',
            'NAME'=>$name ?: 'Без названия',
            'CODE'=>$code,
            'DETAIL_TEXT'=>$this->normalize($desc,300),
        ];
        $props = [
            $this->arParams['PROJECT_USER_PROP_CODE']=>$this->USER_ID,
            'CLIENT_NAME'=>$this->normalize($clientName,120),
            'CLIENT_DESC'=>$this->normalize($clientDesc,300),
            $this->arParams['PROJECT_SERVICES_PROP_CODE']=>array_values(array_map('intval',(array)$services)),
        ];

        $el = new CIBlockElement();

        if ($projectId > 0) {
            $this->assertProjectOwner($projectId);
            if (!$el->Update($projectId,$fields))
                throw new SystemException($el->LAST_ERROR ?: 'Не удалось обновить проект');
            CIBlockElement::SetPropertyValuesEx($projectId,$this->arParams['PROJECT_IBLOCK_ID'],$props);
            CIBlockElement::SetPropertyValueCode($projectId,$this->arParams['PROJECT_USER_PROP_CODE'],$this->USER_ID);
        } else {
            $projectId = (int)$el->Add($fields);
            if ($projectId <= 0)
                throw new SystemException($el->LAST_ERROR ?: 'Не удалось создать проект');
            $props[$this->arParams['PROJECT_GALLERY_PROP_CODE']] = $this->getNewGallery();
            CIBlockElement::SetPropertyValuesEx($projectId,$this->arParams['PROJECT_IBLOCK_ID'],$props);
            CIBlockElement::SetPropertyValueCode($projectId,$this->arParams['PROJECT_USER_PROP_CODE'],$this->USER_ID);
            $_SESSION['PM_NEW_GALLERY'][$this->USER_ID] = [];
        }

        if (function_exists('calcProjectRating')) {
            $r = (array)calcProjectRating($projectId);
            CIBlockElement::SetPropertyValueCode($projectId,$this->arParams['RATING_PROP_CODE'],(int)($r['value']??0));
            CIBlockElement::SetPropertyValueCode($projectId,$this->arParams['VOTES_PROP_CODE'],(int)($r['count']??0));
        }

        return ['status'=>'ok','projectId'=>$projectId,'code'=>$code];
    }

    public function uploadProjectImagesAction($projectId)
    {
        $this->initUser();
        $projectId = (int)$projectId;
        $limit = max(0,(int)$this->arParams['GALLERY_LIMIT']);

        if ($projectId>0) { $this->assertProjectOwner($projectId); $exist=$this->getGalleryIds($projectId); }
        else { $exist=$this->getNewGallery(); }
        $remain = max(0,$limit-count($exist));
        if ($remain<=0) throw new SystemException('Достигнут лимит изображений');

        $req = Context::getCurrent()->getRequest();
        $files = $req->getFile('files');
        $count = is_array($files['name']) ? count($files['name']) : 0;

        $added=[];
        for($i=0;$i<$count && count($added)<$remain;$i++){
            $one=['name'=>$files['name'][$i],'type'=>$files['type'][$i],'tmp_name'=>$files['tmp_name'][$i],'error'=>$files['error'][$i],'size'=>$files['size'][$i]];
            $saved = $this->saveImage($one);
            if(isset($saved['id'])) $added[]=(int)$saved['id'];
        }

        if ($projectId>0) {
            if ($added) {
                CIBlockElement::SetPropertyValues(
                    $projectId,$this->arParams['PROJECT_IBLOCK_ID'],
                    array_values(array_unique(array_merge($exist,$added))),
                    $this->arParams['PROJECT_GALLERY_PROP_CODE']
                );
            }
            $ids=$this->getGalleryIds($projectId);
        } else {
            $ref = &$this->getNewGalleryRef();
            $ref = array_values(array_unique(array_merge($exist,$added)));
            $ids=$ref;
        }

        $html='';
        foreach($ids as $fid){
            $r=CFile::ResizeImageGet($fid,['width'=>300,'height'=>200],BX_RESIZE_IMAGE_PROPORTIONAL,true);
            if(!empty($r['src'])) $html.='<a data-imgid="'.$fid.'" href="#"><img src="'.$r['src'].'"></a>';
        }
        return ['status'=>'ok','html'=>$html];
    }

    public function deleteImageAction($projectId,$fileId)
    {
        $this->initUser();
        $projectId=(int)$projectId; $fileId=(int)$fileId;

        if ($projectId>0){
            $this->assertProjectOwner($projectId);
            $vals=$this->getGalleryIds($projectId);
            if(!in_array($fileId,$vals,true)) throw new SystemException('Изображение не принадлежит проекту');
            \CFile::Delete($fileId);
            // без стрелочных функций:
            $vals=array_values(array_filter($vals, function($x) use ($fileId){ return (int)$x !== $fileId; }));
            CIBlockElement::SetPropertyValues($projectId,$this->arParams['PROJECT_IBLOCK_ID'],$vals,$this->arParams['PROJECT_GALLERY_PROP_CODE']);
        } else {
            $ref=&$this->getNewGalleryRef();
            $ref=array_values(array_filter($ref, function($x) use ($fileId){ return (int)$x !== $fileId; }));
            \CFile::Delete($fileId);
        }
        return ['status'=>'ok','deleted'=>$fileId];
    }

    public function sortGalleryAction($projectId,array $orderedIds)
    {
        $this->initUser();
        $projectId=(int)$projectId;

        if ($projectId>0){
            $this->assertProjectOwner($projectId);
            $data=[]; foreach($orderedIds as $id){ $id=(int)$id; if($id>0) $data[]=['VALUE'=>$id]; }
            CIBlockElement::SetPropertyValuesEx($projectId,$this->arParams['PROJECT_IBLOCK_ID'],[
                $this->arParams['PROJECT_GALLERY_PROP_CODE']=>$data
            ]);
        } else {
            $orderedIds=array_values(array_map('intval',$orderedIds));
            $ref=&$this->getNewGalleryRef();
            // оставляем только существующие в исходной очереди
            $intersect = array_values(array_intersect($orderedIds, $ref));
            $ref = $intersect;
        }
        return ['status'=>'ok'];
    }

    public function resetNewGalleryAction()
    {
        $this->initUser();
        $ref=&$this->getNewGalleryRef();
        foreach($ref as $fid) \CFile::Delete((int)$fid);
        $ref=[];
        return ['status'=>'ok'];
    }

    public function saveServicesAction(
        array $services,
        array $desc = [],
        array $daymin = [],
        array $daymax = [],
        array $pricemin = [],
        array $pricemax = []
    ) {
        $this->initUser();

        $iblockId = (int)$this->arParams['USER_SERVICES_IBLOCK_ID'];
        if ($iblockId <= 0) {
            throw new \Bitrix\Main\SystemException('Не задан инфоблок пользовательских услуг');
        }

        // маппинг кодов свойств
        $pc = [
            'SERVICE'  => $this->arParams['USVC_SERVICE_PROP_CODE'],
            'USER'     => $this->arParams['USVC_USER_PROP_CODE'],
            'DAYSMIN'  => $this->arParams['USVC_DAYSMIN_PROP_CODE'],
            'DAYSMAX'  => $this->arParams['USVC_DAYSMAX_PROP_CODE'],
            'PRICEMIN' => $this->arParams['USVC_PRICEMIN_PROP_CODE'],
            'PRICEMAX' => $this->arParams['USVC_PRICEMAX_PROP_CODE'],
        ];

        $errors  = [];
        $created = [];
        $el = new CIBlockElement();

        // создаём новые
        foreach ($services as $k => $sid) {
            $sid = (int)$sid;
            if ($sid <= 0) { continue; }

            $fields = [
                'IBLOCK_ID'         => $iblockId,
                'IBLOCK_SECTION_ID' => false,
                'ACTIVE'            => 'Y',
                'NAME'              => 'User '.$this->USER_ID.' - '.$sid,
                'DETAIL_TEXT'       => $this->normalize(($desc[$k] ?? ''), 100),
                'PROPERTY_VALUES'   => [
                    $pc['SERVICE']  => $sid,
                    $pc['USER']     => $this->USER_ID,
                    $pc['DAYSMIN']  => (int)($daymin[$k] ?? 0),
                    $pc['DAYSMAX']  => (int)($daymax[$k] ?? 0),
                    $pc['PRICEMIN'] => (int)str_replace(' ', '', (string)($pricemin[$k] ?? 0)),
                    $pc['PRICEMAX'] => (int)str_replace(' ', '', (string)($pricemax[$k] ?? 0)),
                ],
            ];

            $newId = (int)$el->Add($fields);
            if ($newId > 0) {
                $created[] = $newId;
            } else {
                $errors[] = $el->LAST_ERROR ?: 'Ошибка сохранения услуги (SERVICE='.$sid.')';
            }
        }

        // удаляем старые (все, кроме только что созданных)
        $rs = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'PROPERTY_'.$pc['USER'] => $this->USER_ID],
            false, false, ['ID']
        );
        while ($it = $rs->Fetch()) {
            $id = (int)$it['ID'];
            if (!in_array($id, $created, true)) {
                CIBlockElement::Delete($id);
            }
        }

        if ($errors) {
            throw new \Bitrix\Main\SystemException(implode('; ', $errors));
        }

        return ['status' => 'ok', 'created' => count($created)];
    }

    public function deleteProjectAction($projectId)
    {
        $this->initUser();
        $projectId = (int)$projectId;
        $this->assertProjectOwner($projectId);

        global $APPLICATION;
        $APPLICATION->ResetException();

        $ok = CIBlockElement::Delete($projectId);
        if ($ok) return ['status' => 'ok'];

        // если нельзя удалить — деактивируем
        $ex  = $APPLICATION->GetException();
        $msg = $ex ? $ex->GetString() : '';

        $el = new CIBlockElement();
        $upd = $el->Update($projectId, ['ACTIVE' => 'N']);
        if ($upd) {
            return ['status' => 'soft', 'message' => ($msg ?: 'Нет прав на удаление, проект деактивирован')];
        }

        $err = $el->LAST_ERROR ?: $msg ?: 'Не удалось удалить проект';
        throw new \Bitrix\Main\SystemException($err);
    }

    public function deleteAccountAction()
    {
        $this->initUser();

        $res=CIBlockElement::GetList([],[
            'IBLOCK_ID'=>$this->arParams['PROJECT_IBLOCK_ID'],
            'PROPERTY_'.$this->arParams['PROJECT_USER_PROP_CODE']=>$this->USER_ID
        ],false,false,['ID']);
        while($p=$res->Fetch()) CIBlockElement::Delete((int)$p['ID']);

        $s=CIBlockElement::GetList([],[
            'IBLOCK_ID'=>$this->arParams['USER_SERVICES_IBLOCK_ID'],
            'PROPERTY_'.$this->arParams['USVC_USER_PROP_CODE']=>$this->USER_ID
        ],false,false,['ID']);
        while($i=$s->Fetch()) CIBlockElement::Delete((int)$i['ID']);

        CUser::Delete($this->USER_ID);
        return ['status'=>'ok','redirect'=>'/'];
    }

    public function logoutAction()
    {
        $this->initUser();
        global $USER, $APPLICATION;
        $APPLICATION->RestartBuffer();
        $USER->Logout();
        return ['status'=>'ok','redirect'=>'/'];
    }

    /* ================== render ================== */

    public function executeComponent()
    {
        $this->assertAuthorized();

        // профиль
        $this->arResult['USER'] = CUser::GetByID($this->USER_ID)->Fetch();

        // справочник услуг
        $this->arResult['SERVICES']=[];
        $rs=CIBlockElement::GetList(['SORT'=>'ASC'],['IBLOCK_ID'=>$this->arParams['SERVICES_IBLOCK_ID'],'ACTIVE'=>'Y'],false,false,['ID','NAME']);
        while($it=$rs->GetNext()) $this->arResult['SERVICES'][]=$it;

        // мои проекты (превью)
        $this->arResult['PROJECTS']=[];
        $pr=CIBlockElement::GetList(['SORT'=>'ASC'],[
            'IBLOCK_ID'=>$this->arParams['PROJECT_IBLOCK_ID'],
            'ACTIVE'=>'Y',
            'PROPERTY_'.$this->arParams['PROJECT_USER_PROP_CODE']=>$this->USER_ID
        ],false,false,['ID','NAME','CODE']);
        while($p=$pr->GetNext()){
            $ids=$this->getGalleryIds((int)$p['ID']);
            $thumb=''; if($ids){ $r=CFile::ResizeImageGet((int)$ids[0],['width'=>800,'height'=>450],BX_RESIZE_IMAGE_EXACT,true); $thumb=(string)($r['src']??''); }
            $p['THUMB']=$thumb;
            $this->arResult['PROJECTS'][]=$p;
        }

        // если редактируем проект — подробности
        $pid=(int)Context::getCurrent()->getRequest()->get('projectid');
        if($pid>0){
            try{
                $this->assertProjectOwner($pid);
                $sel=['ID','NAME','CODE','DETAIL_TEXT'];
                $row = CIBlockElement::GetList([],['IBLOCK_ID'=>$this->arParams['PROJECT_IBLOCK_ID'],'ID'=>$pid],false,false,$sel)->GetNext();

                if ($row) {
                    $row['PROPERTY_CLIENT_NAME_VALUE']='';
                    $row['PROPERTY_CLIENT_DESC_VALUE']='';

                    $pr = CIBlockElement::GetProperty($this->arParams['PROJECT_IBLOCK_ID'],$pid,[],['CODE'=>'CLIENT_NAME']);
                    if ($p=$pr->Fetch()) $row['PROPERTY_CLIENT_NAME_VALUE'] = (string)$p['VALUE'];
                    $pr = CIBlockElement::GetProperty($this->arParams['PROJECT_IBLOCK_ID'],$pid,[],['CODE'=>'CLIENT_DESC']);
                    if ($p=$pr->Fetch()) $row['PROPERTY_CLIENT_DESC_VALUE'] = (string)$p['VALUE'];

                    $this->arResult['CURRENT_PROJECT']=$row;
                }

                // галерея
                $this->arResult['CURRENT_GALLERY']=[];
                foreach($this->getGalleryIds($pid) as $fid){ $f=CFile::GetFileArray($fid); if($f) $this->arResult['CURRENT_GALLERY'][]=$f; }

                // услуги проекта
                $this->arResult['CURRENT_SERVICES']=[];
                $ps=CIBlockElement::GetProperty($this->arParams['PROJECT_IBLOCK_ID'],$pid,[],['CODE'=>$this->arParams['PROJECT_SERVICES_PROP_CODE']]);
                while($pp=$ps->Fetch()) if((int)$pp['VALUE']>0) $this->arResult['CURRENT_SERVICES'][]=(int)$pp['VALUE'];
            } catch (\Throwable $e) {}
        }

        // ===== Мои услуги (надёжное чтение с произвольными кодами свойств)
        $this->arResult['USER_SERVICES'] = [];

        $ibU = (int)$this->arParams['USER_SERVICES_IBLOCK_ID'];
        $pcU = [
            'SERVICE'  => $this->arParams['USVC_SERVICE_PROP_CODE'],
            'USER'     => $this->arParams['USVC_USER_PROP_CODE'],
            'DAYSMIN'  => $this->arParams['USVC_DAYSMIN_PROP_CODE'],
            'DAYSMAX'  => $this->arParams['USVC_DAYSMAX_PROP_CODE'],
            'PRICEMIN' => $this->arParams['USVC_PRICEMIN_PROP_CODE'],
            'PRICEMAX' => $this->arParams['USVC_PRICEMAX_PROP_CODE'],
        ];

        $select = [
            'ID','NAME','DETAIL_TEXT',
            'PROPERTY_'.$pcU['SERVICE'],
            'PROPERTY_'.$pcU['DAYSMIN'],
            'PROPERTY_'.$pcU['DAYSMAX'],
            'PROPERTY_'.$pcU['PRICEMIN'],
            'PROPERTY_'.$pcU['PRICEMAX'],
        ];

        $filter = [
            'IBLOCK_ID'              => $ibU,
            'PROPERTY_'.$pcU['USER'] => $this->USER_ID,
            //'ACTIVE' => 'Y', // раскомментируйте, если нужно скрывать неактивные
        ];

        $us = CIBlockElement::GetList(['ID'=>'ASC'], $filter, false, false, $select);
        while ($row = $us->GetNext()) {
            $row['PROPERTY_SERVICE_VALUE']  = $row['PROPERTY_'.$pcU['SERVICE'].'_VALUE'];
            $row['PROPERTY_DAYSMIN_VALUE']  = (int)$row['PROPERTY_'.$pcU['DAYSMIN'].'_VALUE'];
            $row['PROPERTY_DAYSMAX_VALUE']  = (int)$row['PROPERTY_'.$pcU['DAYSMAX'].'_VALUE'];
            $row['PROPERTY_PRICEMIN_VALUE'] = (int)$row['PROPERTY_'.$pcU['PRICEMIN'].'_VALUE'];
            $row['PROPERTY_PRICEMAX_VALUE'] = (int)$row['PROPERTY_'.$pcU['PRICEMAX'].'_VALUE'];
            $this->arResult['USER_SERVICES'][] = $row;
        }

        $this->includeComponentTemplate();
    }
}

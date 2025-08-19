<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arResult */
$this->addExternalCss($templateFolder.'/style.css');
$isEdit = !empty($arResult['CURRENT_PROJECT']);
?>
<div class="pm-container">

<?php if (!$isEdit): ?>
  <h1 class="pm-title">ПРОФИЛЬ</h1>

  <div class="pm-section"><div class="pm-section__title">Данные</div></div>
  <div class="pm-card pm-profile">
    <div class="pm-row pm-row--head">
      <div class="pm-muted">Эти данные видны в портфолио</div>
      <button id="save-profile" class="pm-btn pm-btn--primary">Сохранить</button>
    </div>

    <div class="pm-profile-grid">
      <!-- Аватар + кнопка ниже -->
      <div class="pm-profile-left">
        <div class="pm-avatar" id="avatar-preview">
          <?php if (!empty($arResult['USER']['PERSONAL_PHOTO'])):
            $r = CFile::ResizeImageGet($arResult['USER']['PERSONAL_PHOTO'], ['width'=>220,'height'=>220], BX_RESIZE_IMAGE_EXACT, false);?>
            <img src="<?=$r['src']?>" alt="">
          <?php endif; ?>
        </div>
        <div class="pm-file" style="margin-top:10px">
          <label for="avatar-input" class="pm-btn pm-btn--ghost pm-btn--sm">Выбрать файл</label>
          <input type="file" id="avatar-input" accept="image/*" hidden>
          <span class="pm-hint">JPG/PNG/GIF до 5 МБ</span>
        </div>
      </div>

      <!-- Поля данных -->
      <div class="pm-profile-right">
        <div class="pm-row">
          <div class="pm-col">
            <label class="pm-label">Имя</label>
            <input class="pm-input" type="text" id="fn" value="<?=htmlspecialcharsbx($arResult['USER']['NAME'])?>">
          </div>
          <div class="pm-col">
            <label class="pm-label">Фамилия</label>
            <input class="pm-input" type="text" id="ln" value="<?=htmlspecialcharsbx($arResult['USER']['LAST_NAME'])?>">
          </div>
        </div>

        <div class="pm-row">
          <div class="pm-col pm-col--full">
            <label class="pm-label">О себе (до 100 символов)</label>
            <textarea class="pm-textarea" id="desc" maxlength="100"><?=htmlspecialcharsbx($arResult['USER']['WORK_NOTES'])?></textarea>
          </div>
        </div>

        <div class="pm-row">
          <div class="pm-col pm-col--full">
            <label class="pm-label">Ссылка для связи (Telegram/WhatsApp)</label>
            <input class="pm-input" type="text" id="contacts" value="<?=htmlspecialcharsbx($arResult['USER']['WORK_PHONE'])?>">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== Проекты ===== -->
  <div class="pm-section"><div class="pm-section__title">Проекты</div></div>
  <div class="pm-card pm-services-card">

    <div class="pm-projects" style="margin-bottom:14px">
      <?php foreach ($arResult['PROJECTS'] as $p): ?>
        <a href="?projectid=<?=$p['ID']?>" class="pm-project-card" title="Редактировать проект">
          <?php if (!empty($p['THUMB'])): ?>
            <div class="pm-project-thumb" style="background-image:url('<?=htmlspecialcharsbx($p['THUMB'])?>')"></div>
          <?php else: ?>
            <div class="pm-project-thumb pm-project-thumb--empty">
              <span class="pm-project-monogram"><?=htmlspecialcharsbx(mb_strtoupper(mb_substr($p['NAME'], 0, 1)))?></span>
            </div>
          <?php endif; ?>
          <div class="pm-project-edit">Редактировать ✎</div>
          <div class="pm-project-title"><?=htmlspecialcharsbx($p['NAME'])?></div>
        </a>
      <?php endforeach; ?>
    </div>
    <button id="create-project" class="pm-btn pm-btn--ghost">Создать проект</button>
  </div>

  <!-- ===== Форма создания проекта (скрыта) ===== -->
  <!-- ДОБАВЛЕН position+z-index, чтобы форма всегда была поверх футера -->
  <div id="pm-new-project" class="pm-card"
       style="display:none; margin-top:16px; position:relative; z-index:2147483647">
    <div class="pm-actions pm-actions--right">
      <button id="new-create-project" class="pm-btn pm-btn--primary">Создать проект</button>
      <button id="new-cancel-project" class="pm-btn pm-btn--ghost">Отмена</button>
    </div>

    <div class="pm-row">
      <div class="pm-col pm-col--full">
        <label class="pm-label">Название</label>
        <input class="pm-input" id="new-pr-name" placeholder="Название проекта">
      </div>
    </div>

    <div class="pm-row">
      <div class="pm-col pm-col--full">
        <label class="pm-label">Описание (до 300 символов)</label>
        <textarea class="pm-textarea" id="new-pr-desc" maxlength="300" placeholder="Короткое описание"></textarea>
      </div>
    </div>

    <div class="pm-row">
      <div class="pm-col">
        <label class="pm-label">Имя клиента</label>
        <input class="pm-input" id="new-pr-client" placeholder="Имя клиента">
      </div>
      <div class="pm-col">
        <label class="pm-label">Отзыв клиента</label>
        <textarea class="pm-textarea" id="new-pr-client-desc" maxlength="300" placeholder="Отзыв клиента"></textarea>
      </div>
    </div>

    <div class="pm-row" style="margin-top:6px">
      <div class="pm-col pm-col--full">
        <label class="pm-label" style="margin-bottom:10px">Услуги по проекту</label>
        <div class="pm-row" style="gap:10px">
          <?php foreach ($arResult['SERVICES'] as $s): ?>
            <label class="pm-chip-check">
              <input type="checkbox" class="pr-service pr-service--new" value="<?=$s['ID']?>">
              <span><?=htmlspecialcharsbx($s['NAME'])?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="pm-sep"></div>
    <div class="pm-row" style="justify-content:space-between;align-items:center">
      <div class="pm-label" style="margin:0">Галерея проекта</div>
      <div class="pm-file">
        <label for="new-gal-input" class="pm-file-trigger">Загрузить фото</label>
        <input type="file" id="new-gal-input" multiple accept="image/*">
        <span class="pm-hint">До <?=$arParams['GALLERY_LIMIT']?> изображений</span>
      </div>
    </div>
    <div id="new-gal-list" class="pm-gallery" style="margin-top:14px"></div>
  </div>
  <?php endif; ?>

  <?php if ($isEdit): ?>
  <!-- ===== Редактирование проекта ===== -->
  <div class="pm-section"><div class="pm-section__title">Проект «<?=htmlspecialcharsbx($arResult['CURRENT_PROJECT']['NAME'])?>»</div></div>

  <div class="pm-card" style="margin-bottom:18px">
    <div class="pm-actions pm-actions--right">
      <button id="save-project" data-id="<?=$arResult['CURRENT_PROJECT']['ID']?>" class="pm-btn pm-btn--primary">Сохранить</button>
      <button id="delete-project" data-id="<?=$arResult['CURRENT_PROJECT']['ID']?>" class="pm-btn pm-btn--danger">Удалить</button>
      <a href="?" class="pm-btn pm-btn--ghost">Вернуться</a>
    </div>

    <div class="pm-row">
      <div class="pm-col pm-col--full">
        <label class="pm-label">Название</label>
        <input class="pm-input" id="pr-name" value="<?=htmlspecialcharsbx($arResult['CURRENT_PROJECT']['NAME'])?>">
      </div>
    </div>

    <div class="pm-row">
      <div class="pm-col pm-col--full">
        <label class="pm-label">Описание (до 300 символов)</label>
        <textarea class="pm-textarea" id="pr-desc" maxlength="300"><?=htmlspecialcharsbx($arResult['CURRENT_PROJECT']['DETAIL_TEXT'])?></textarea>
      </div>
    </div>

    <div class="pm-row">
      <div class="pm-col">
        <label class="pm-label">Имя клиента</label>
        <input class="pm-input" id="pr-client" value="<?=htmlspecialcharsbx($arResult['CURRENT_PROJECT']['PROPERTY_CLIENT_NAME_VALUE'])?>">
      </div>
      <div class="pm-col">
        <label class="pm-label">Отзыв клиента</label>
        <textarea class="pm-textarea" id="pr-client-desc" maxlength="300"><?=htmlspecialcharsbx($arResult['CURRENT_PROJECT']['PROPERTY_CLIENT_DESC_VALUE'])?></textarea>
      </div>
    </div>

    <div class="pm-row" style="margin-top:6px">
      <div class="pm-col pm-col--full">
        <label class="pm-label" style="margin-bottom:10px">Услуги по проекту</label>
        <div class="pm-row" style="gap:10px">
          <?php foreach ($arResult['SERVICES'] as $s): ?>
            <label class="pm-chip-check">
              <input type="checkbox" class="pr-service" value="<?=$s['ID']?>"
              <?= in_array((int)$s['ID'], (array)$arResult['CURRENT_SERVICES'], true) ? 'checked' : '' ?>>
              <span><?=htmlspecialcharsbx($s['NAME'])?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="pm-card pm-services-card">

    <div class="pm-row" style="justify-content:space-between;align-items:center">
      <div class="pm-label" style="margin:0">Галерея проекта</div>
      <div class="pm-file">
        <label for="gal-input" class="pm-file-trigger">Загрузить фото</label>
        <input type="file" id="gal-input" multiple accept="image/*">
      </div>
    </div>

    <div id="gal-list" class="pm-gallery" style="margin-top:14px">
      <?php foreach ($arResult['CURRENT_GALLERY'] as $g):
        $r = CFile::ResizeImageGet($g['ID'], ['width'=>600,'height'=>400], BX_RESIZE_IMAGE_PROPORTIONAL, true); ?>
        <div class="pm-thumb" data-imgid="<?=$g['ID']?>">
          <button class="pm-thumb-del" data-del="<?=$g['ID']?>" title="Удалить">×</button>
          <img src="<?=$r['src']?>" alt="">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!$isEdit): ?>
  <!-- ===== Мои услуги ===== -->
  <div class="pm-section"><div class="pm-section__title">МОИ УСЛУГИ</div></div>
  <div class="pm-card pm-services-card">

    <div id="services">
      <?php
      if (!empty($arResult['USER_SERVICES'])):
        $n = 0;
        foreach ($arResult['USER_SERVICES'] as $row):
          $n++;
          $servId   = (int)$row['SERVICE_ID'];
          $desc     = htmlspecialcharsbx($row['DESC'] ?? '');
          $dmin     = (int)($row['DAYSMIN'] ?? 10);
          $dmax     = (int)($row['DAYSMAX'] ?? 15);
          $pmin     = (int)str_replace(' ', '', $row['PRICEMIN'] ?? 50000);
          $pmax     = (int)str_replace(' ', '', $row['PRICEMAX'] ?? 100000);
      ?>
      <div class="pm-service-row">
        <div class="pm-srv-num"><?=str_pad($n, 2, '0', STR_PAD_LEFT)?></div>

        <div>
          <label class="pm-label">Выберите услугу</label>
          <select class="pm-select srv-service">
            <option value="0">—</option>
            <?php foreach ($arResult['SERVICES'] as $opt): ?>
              <option value="<?=$opt['ID']?>" <?=($opt['ID']==$servId?'selected':'')?>>
                <?=htmlspecialcharsbx($opt['NAME'])?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="pm-label">Детализация (до 100 символов)</label>
          <textarea class="pm-textarea srv-desc" maxlength="100"><?=$desc?></textarea>
        </div>

        <!-- Количество дней -->
        <div class="pm-range">
          <label class="pm-label">Количество дней</label>
          <div class="pm-dual">
            <div class="pm-dual-track"></div>
            <input type="range" class="srv-daymin-range pm-range-input" min="1" max="180" step="1" value="<?=$dmin?>">
            <input type="range" class="srv-daymax-range pm-range-input" min="1" max="180" step="1" value="<?=$dmax?>">
          </div>
          <div class="pm-range-values">
            <span>от</span>
            <input type="number" class="pm-num srv-daymin-inp" min="1" max="180" step="1" value="<?=$dmin?>">
            <span>до</span>
            <input type="number" class="pm-num srv-daymax-inp" min="1" max="180" step="1" value="<?=$dmax?>">
          </div>
        </div>

        <!-- Стоимость -->
        <div class="pm-range">
          <label class="pm-label">Стоимость, руб</label>
          <div class="pm-dual">
            <div class="pm-dual-track"></div>
            <input type="range" class="srv-pricemin-range pm-range-input" min="0" max="1000000" step="500" value="<?=$pmin?>">
            <input type="range" class="srv-pricemax-range pm-range-input" min="0" max="1000000" step="500" value="<?=$pmax?>">
          </div>
          <div class="pm-range-values">
            <span>от</span>
            <input type="number" class="pm-num srv-pricemin-inp" min="0" max="1000000" step="500" value="<?=$pmin?>">
            <span>до</span>
            <input type="number" class="pm-num srv-pricemax-inp" min="0" max="1000000" step="500" value="<?=$pmax?>">
          </div>
        </div>

        <button type="button" class="pm-row-del" title="Удалить строку">×</button>
      </div>
      <?php endforeach; else: ?>
        <!-- Пустая первая строка -->
        <div class="pm-service-row">
          <div class="pm-srv-num">01</div>

          <div>
            <label class="pm-label">Выберите услугу</label>
            <select class="pm-select srv-service">
              <option value="0">—</option>
              <?php foreach ($arResult['SERVICES'] as $opt): ?>
                <option value="<?=$opt['ID']?>"><?=htmlspecialcharsbx($opt['NAME'])?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="pm-label">Детализация (до 100 символов)</label>
            <textarea class="pm-textarea srv-desc" maxlength="100"></textarea>
          </div>

          <div class="pm-range">
            <label class="pm-label">Количество дней</label>
            <div class="pm-dual">
              <div class="pm-dual-track"></div>
              <input type="range" class="srv-daymin-range pm-range-input" min="1" max="180" step="1" value="10">
              <input type="range" class="srv-daymax-range pm-range-input" min="1" max="180" step="1" value="15">
            </div>
            <div class="pm-range-values">
              <span>от</span>
              <input type="number" class="pm-num srv-daymin-inp" min="1" max="180" step="1" value="10">
              <span>до</span>
              <input type="number" class="pm-num srv-daymax-inp" min="1" max="180" step="1" value="15">
            </div>
          </div>

          <div class="pm-range">
            <label class="pm-label">Стоимость, руб</label>
            <div class="pm-dual">
              <div class="pm-dual-track"></div>
              <input type="range" class="srv-pricemin-range pm-range-input" min="0" max="1000000" step="500" value="50000">
              <input type="range" class="srv-pricemax-range pm-range-input" min="0" max="1000000" step="500" value="100000">
            </div>
            <div class="pm-range-values">
              <span>от</span>
              <input type="number" class="pm-num srv-pricemin-inp" min="0" max="1000000" step="500" value="50000">
              <span>до</span>
              <input type="number" class="pm-num srv-pricemax-inp" min="0" max="1000000" step="500" value="100000">
            </div>
          </div>

          <button type="button" class="pm-row-del" title="Удалить строку">×</button>
        </div>
      <?php endif; ?>
    </div>

    <div class="pm-actions" style="margin-top:12px">
      <button type="button" id="add-service" class="pm-btn pm-btn--ghost">+ Добавить услугу</button>
      <button type="button" id="save-services" class="pm-btn pm-btn--primary">Сохранить услуги</button>
    </div>
  </div>

  <!-- Шаблон новой строки -->
  <template id="srv-row-tpl">
    <div class="pm-service-row">
      <div class="pm-srv-num">01</div>

      <div>
        <label class="pm-label">Выберите услугу</label>
        <select class="pm-select srv-service">
          <option value="0">—</option>
          <?php foreach ($arResult['SERVICES'] as $opt): ?>
            <option value="<?=$opt['ID']?>"><?=htmlspecialcharsbx($opt['NAME'])?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="pm-label">Детализация (до 100 символов)</label>
        <textarea class="pm-textarea srv-desc" maxlength="100"></textarea>
      </div>

      <div class="pm-range">
        <label class="pm-label">Количество дней</label>
        <div class="pm-dual">
          <div class="pm-dual-track"></div>
          <input type="range" class="srv-daymin-range pm-range-input" min="1" max="180" step="1" value="10">
          <input type="range" class="srv-daymax-range pm-range-input" min="1" max="180" step="1" value="15">
        </div>
        <div class="pm-range-values">
          <span>от</span>
          <input type="number" class="pm-num srv-daymin-inp" min="1" max="180" step="1" value="10">
          <span>до</span>
          <input type="number" class="pm-num srv-daymax-inp" min="1" max="180" step="1" value="15">
        </div>
      </div>

      <div class="pm-range">
        <label class="pm-label">Стоимость, руб</label>
        <div class="pm-dual">
          <div class="pm-dual-track"></div>
          <input type="range" class="srv-pricemin-range pm-range-input" min="0" max="1000000" step="500" value="50000">
          <input type="range" class="srv-pricemax-range pm-range-input" min="0" max="1000000" step="500" value="100000">
        </div>
        <div class="pm-range-values">
          <span>от</span>
          <input type="number" class="pm-num srv-pricemin-inp" min="0" max="1000000" step="500" value="50000">
          <span>до</span>
          <input type="number" class="pm-num srv-pricemax-inp" min="0" max="1000000" step="500" value="100000">
        </div>
      </div>

      <button type="button" class="pm-row-del" title="Удалить строку">×</button>
    </div>
  </template>
  <?php endif; ?>

  <!-- НЕВИДИМАЯ ПОДПОРКА: запас снизу, чтобы контент не упирался в футер -->
  <div aria-hidden="true" class="pm-footer-guard" style="height:160px"></div>

</div><!-- /.pm-container -->

<!-- Тосты -->
<div id="pm-toast"></div>

<script>
(function(){
  'use strict';

  /* ===== helpers ===== */
  function notify(text,type){
    var box=document.getElementById('pm-toast');
    if(!box){ box=document.createElement('div'); box.id='pm-toast'; document.body.appendChild(box); }
    var n=document.createElement('div');
    n.className='pm-toast pm-toast--'+(type||'info');
    n.textContent=text;
    box.appendChild(n);
    setTimeout(function(){ n.classList.add('show'); }, 10);
    setTimeout(function(){
      n.classList.remove('show');
      n.addEventListener('transitionend', function(){ n.remove(); }, {once:true});
    }, 2600);
  }
  function bxRun(action, data){
    var payload = data;
    if(!(data instanceof FormData)){
      payload = new FormData();
      if (data && typeof data === 'object'){
        for (var k in data){
          if(!data.hasOwnProperty(k)) continue;
          var v=data[k];
          if(Array.isArray(v)) v.forEach(function(val){ payload.append(k+'[]', val); });
          else payload.append(k, v);
        }
      }
    }
    payload.append('sessid', BX.bitrix_sessid());
    return BX.ajax.runComponentAction('custom:profile.manager', action, { mode:'class', data: payload });
  }
  function percent(val, min, max){ return ((val - min) * 100) / (max - min); }

  /* ===== ДВОЙНЫЕ ПОЛЗУНКИ ДЛЯ УСЛУГ ===== */
  function wireRangeRow(row){
    var rMin=row.querySelector('.srv-daymin-range'),
        rMax=row.querySelector('.srv-daymax-range'),
        pMin=row.querySelector('.srv-pricemin-range'),
        pMax=row.querySelector('.srv-pricemax-range');

    var iMin=row.querySelector('.srv-daymin-inp'),
        iMax=row.querySelector('.srv-daymax-inp'),
        ipMin=row.querySelector('.srv-pricemin-inp'),
        ipMax=row.querySelector('.srv-pricemax-inp');

    var tracks=row.querySelectorAll('.pm-range .pm-dual-track');
    var trackDay=tracks[0]||null, trackPrice=tracks[1]||null;

    function clampRange(a,b){ var av=+a.value, bv=+b.value; if (av>bv){ var t=av; av=bv; bv=t; } a.value=av; b.value=bv; }
    function clampInput(i, min, max){ var v=+i.value; if(isNaN(v)) v=min; if(v<min) v=min; if(v>max) v=max; i.value=v; return v; }

    function updateDayFromRanges(){
      clampRange(rMin,rMax);
      if(iMin) iMin.value=rMin.value;
      if(iMax) iMax.value=rMax.value;
      if(trackDay){
        var from=percent(+rMin.value,+rMin.min,+rMin.max);
        var to  =percent(+rMax.value,+rMax.min,+rMax.max);
        trackDay.style.setProperty('--from',from+'%');
        trackDay.style.setProperty('--to',  to  +'%');
      }
    }
    function updatePriceFromRanges(){
      clampRange(pMin,pMax);
      if(ipMin) ipMin.value=pMin.value;
      if(ipMax) ipMax.value=pMax.value;
      if(trackPrice){
        var from=percent(+pMin.value,+pMin.min,+pMin.max);
        var to  =percent(+pMax.value,+pMax.min,+pMax.max);
        trackPrice.style.setProperty('--from',from+'%');
        trackPrice.style.setProperty('--to',  to  +'%');
      }
    }
    function updateDayFromInputs(){
      var v1=clampInput(iMin,+rMin.min,+rMin.max);
      var v2=clampInput(iMax,+rMax.min,+rMax.max);
      if(v1>v2){ var t=v1; v1=v2; v2=t; iMin.value=v1; iMax.value=v2; }
      rMin.value=v1; rMax.value=v2; updateDayFromRanges();
    }
    function updatePriceFromInputs(){
      var v1=clampInput(ipMin,+pMin.min,+pMin.max);
      var v2=clampInput(ipMax,+pMax.min,+pMax.max);
      if(v1>v2){ var t=v1; v1=v2; v2=t; ipMin.value=v1; ipMax.value=v2; }
      pMin.value=v1; pMax.value=v2; updatePriceFromRanges();
    }

    [rMin,rMax].forEach(function(el){ if(el) el.addEventListener('input',updateDayFromRanges); });
    [pMin,pMax].forEach(function(el){ if(el) el.addEventListener('input',updatePriceFromRanges); });
    [iMin,iMax].forEach(function(el){ if(el) el.addEventListener('change',updateDayFromInputs); });
    [ipMin,ipMax].forEach(function(el){ if(el) el.addEventListener('change',updatePriceFromInputs); });

    updateDayFromRanges(); updatePriceFromRanges();
  }
  function wireRowDelete(row){
    var del=row.querySelector('.pm-row-del');
    if(del){ del.onclick=function(){ row.remove(); renumberServices(); }; }
  }
  function renumberServices(){
    var rows=document.querySelectorAll('#services .pm-service-row');
    for(var i=0;i<rows.length;i++){
      var n=rows[i].querySelector('.pm-srv-num');
      if(n) n.textContent=String(i+1).padStart(2,'0');
    }
  }
  function collectServiceRowsFD(){
    var rows=document.querySelectorAll('#services .pm-service-row');
    var fd=new FormData();
    if(rows.length===0) fd.append('services[]','0');
    for(var i=0;i<rows.length;i++){
      var r=rows[i];
      fd.append('services[]', (r.querySelector('.srv-service').value||'0'));
      fd.append('desc[]',     r.querySelector('.srv-desc').value);
      fd.append('daymin[]',   r.querySelector('.srv-daymin-range').value);
      fd.append('daymax[]',   r.querySelector('.srv-daymax-range').value);
      fd.append('pricemin[]', r.querySelector('.srv-pricemin-range').value);
      fd.append('pricemax[]', r.querySelector('.srv-pricemax-range').value);
    }
    fd.append('sessid', BX.bitrix_sessid());
    return fd;
  }

  /* ===== ГАЛЕРЕЯ ПРОЕКТА ===== */
  function initDelete(){
    var list=document.getElementById('gal-list'); if(!list) return;
    var btns=list.querySelectorAll('.pm-thumb-del');
    for(var i=0;i<btns.length;i++){
      btns[i].onclick=function(){
        var sp=document.getElementById('save-project');
        var pid=sp ? (sp.dataset.id|0) : 0;
        var id=this.getAttribute('data-del')|0, self=this;
        bxRun('deleteImage',{projectId:pid,fileId:id})
          .then(function(){
            var t=self.closest('.pm-thumb'); if(t) t.remove();
            notify('Фото удалено','success');
          },function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка удаления','danger'); });
      };
    }
  }

  document.addEventListener('DOMContentLoaded', function(){

    /* ===== ПРОФИЛЬ ===== */
    var btnSaveProfile=document.getElementById('save-profile');
    if(btnSaveProfile){
      btnSaveProfile.addEventListener('click',function(){
        bxRun('saveProfile',{
          name:document.getElementById('fn').value,
          surname:document.getElementById('ln').value,
          description:document.getElementById('desc').value,
          contacts:document.getElementById('contacts').value
        }).then(function(){ notify('Профиль сохранён','success'); },
                function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка сохранения','danger'); });
      });
    }
    var avatarInput=document.getElementById('avatar-input');
    if(avatarInput){
      avatarInput.addEventListener('change',function(){
        if(!this.files||!this.files[0]) return;
        var fd=new FormData(); fd.append('file',this.files[0]); fd.append('sessid',BX.bitrix_sessid());
        BX.ajax.runComponentAction('custom:profile.manager','uploadAvatar',{mode:'class',data:fd})
          .then(function(res){
            if(res.data&&res.data.src){
              var holder=document.getElementById('avatar-preview');
              if(holder){
                var img=holder.querySelector('img'); if(!img){ img=document.createElement('img'); holder.appendChild(img); }
                img.src=res.data.src;
              }
            }
            notify('Фото обновлено','success');
          },function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка','danger'); });
      });
    }

    /* ===== ПРОЕКТЫ ===== */
    function openNewProjectForm(){
      var card=document.getElementById('pm-new-project');
      var list=document.getElementById('new-gal-list');
      if(list) list.innerHTML='';
      if(card){
        card.style.display='block';
        card.scrollIntoView({behavior:'smooth', block:'start'});
      }
    }
    var btnCreate=document.getElementById('create-project');
    if(btnCreate){
      btnCreate.addEventListener('click',function(){
        bxRun('resetNewGallery',{})
          .then(function(){ openNewProjectForm(); }, function(){ openNewProjectForm(); });
      });
    }
    var newGal=document.getElementById('new-gal-input');
    if(newGal){
      newGal.addEventListener('change',function(){
        var fd=new FormData();
        for(var i=0;i<this.files.length;i++) fd.append('files[]',this.files[i]);
        fd.append('projectId',0); fd.append('sessid',BX.bitrix_sessid());
        BX.ajax.runComponentAction('custom:profile.manager','uploadProjectImages',{mode:'class',data:fd})
          .then(function(r){ document.getElementById('new-gal-list').innerHTML=r.data.html; notify('Фото добавлены','success'); },
                function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка загрузки','danger'); });
      });
    }
    var newCreate=document.getElementById('new-create-project');
    if(newCreate){
      newCreate.addEventListener('click',function(){
        var services=Array.prototype.map.call(
          document.querySelectorAll('#pm-new-project .pr-service--new:checked'),
          function(i){ return i.value|0; }
        );
        bxRun('saveProject',{
          projectId:0,
          name:(document.getElementById('new-pr-name').value||'Без названия').trim(),
          desc:document.getElementById('new-pr-desc').value,
          clientName:document.getElementById('new-pr-client').value,
          clientDesc:document.getElementById('new-pr-client-desc').value,
          services:services
        }).then(function(r){
          notify('Проект создан','success');
          if(r&&r.data&&r.data.projectId) location.search='?projectid='+r.data.projectId;
        },function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка создания проекта','danger'); });
      });
    }
    var newCancel=document.getElementById('new-cancel-project');
    if(newCancel){
      newCancel.addEventListener('click',function(){
        bxRun('resetNewGallery',{})
          .then(hideNew, hideNew);
        function hideNew(){
          var card=document.getElementById('pm-new-project');
          var list=document.getElementById('new-gal-list');
          if(card) card.style.display='none';
          if(list) list.innerHTML='';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    }
    var savePr=document.getElementById('save-project');
    if(savePr){
      savePr.addEventListener('click',function(){
        var id=this.dataset.id|0;
        var sv=Array.prototype.map.call(document.querySelectorAll('.pr-service:checked'),function(i){ return i.value|0; });
        bxRun('saveProject',{
          projectId:id,
          name:document.getElementById('pr-name').value,
          desc:document.getElementById('pr-desc').value,
          clientName:document.getElementById('pr-client').value,
          clientDesc:document.getElementById('pr-client-desc').value,
          services:sv
        }).then(function(){ notify('Проект сохранён','success'); },
                function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка сохранения проекта','danger'); });
      });
    }
    var delPr=document.getElementById('delete-project');
    if(delPr){
      delPr.addEventListener('click',function(){
        if(!confirm('Удалить проект?')) return;
        var id=this.dataset.id|0;
        bxRun('deleteProject',{projectId:id})
          .then(function(r){
            if(r.data&&r.data.status==='soft'){ notify(r.data.message||'Проект деактивирован','info'); }
            else notify('Проект удалён','success');
            setTimeout(function(){ location.href='?'; },600);
          },function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка удаления','danger'); });
      });
    }
    var galInput=document.getElementById('gal-input');
    if(galInput){
      galInput.addEventListener('change',function(){
        var fd=new FormData();
        for(var i=0;i<this.files.length;i++) fd.append('files[]',this.files[i]);
        var sp=document.getElementById('save-project');
        var pid=sp ? (sp.dataset.id|0) : 0;
        fd.append('projectId',pid); fd.append('sessid',BX.bitrix_sessid());
        BX.ajax.runComponentAction('custom:profile.manager','uploadProjectImages',{mode:'class',data:fd})
          .then(function(r){ document.getElementById('gal-list').innerHTML=r.data.html; initDelete(); notify('Фото добавлены','success'); },
                function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка','danger'); });
      });
    }
    initDelete();

    /* ===== УСЛУГИ ===== */
    var initRows=document.querySelectorAll('#services .pm-service-row');
    for(var i=0;i<initRows.length;i++){ wireRangeRow(initRows[i]); wireRowDelete(initRows[i]); }
    renumberServices();

    var addSrv=document.getElementById('add-service');
    if(addSrv){
      addSrv.addEventListener('click',function(){
        var tpl=document.getElementById('srv-row-tpl');
        var node=tpl.content.firstElementChild.cloneNode(true);
        document.getElementById('services').appendChild(node);
        wireRangeRow(node); wireRowDelete(node); renumberServices();
      });
    }
    var saveSrv=document.getElementById('save-services');
    if(saveSrv){
      saveSrv.addEventListener('click',function(){
        var fd=collectServiceRowsFD();
        BX.ajax.runComponentAction('custom:profile.manager','saveServices',{mode:'class',data:fd})
          .then(function(r){
            var cnt=r.data&&typeof r.data.created!=='undefined'?r.data.created:0;
            notify(cnt?'Услуги сохранены':'Список услуг пуст — всё удалено','success');
          },function(e){ notify((e.errors&&e.errors[0]&&e.errors[0].message)||'Ошибка сохранения услуг','danger'); });
      });
    }

  }); // DOMContentLoaded
})();
</script>

<?php
/*
 * DLE-ModerNotes — Модуль заметок о пользователях
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9Tgv
 */

if (!defined('DATALIFEENGINE')) {
	die('Hacking attempt');
}

/**
 * Информация из DLE, доступная в модуле
 *
 * @global boolean $is_logged           Является ли посетитель авторизованным пользователем или гостем.
 * @global array   $member_id           Массив с информацией о авторизованном пользователе, включая всю его информацию
 *                                      из профиля.
 * @global object  $db                  Класс DLE для работы с базой данных.
 * @global object  $tpl                 Класс DLE для работы с шаблонами.
 * @global array   $cat_info            Информация обо всех категориях на сайте.
 * @global array   $config              Информация обо всех настройках скрипта.
 * @global array   $user_group          Информация о всех группах пользователей и их настройках.
 * @global integer $category_id         ID категории которую просматривает посетитель.
 * @global integer $_TIME               Содержит текущее время в UNIX формате с учетом настроек смещения в настройках
 *                                      скрипта.
 * @global array   $lang                Массив содержащий текст из языкового пакета.
 * @global boolean $smartphone_detected Если пользователь со смартфона - true.
 * @global string  $dle_module          Информация о просматриваемомразделе сайта, либо информацию переменной do из URL
 *                                      браузера.
 */

/**
 * Привет строки подключения:
 * {include file="engine/modules/moder_notes/module.php?user=this&nocache=y"}
 */

// Определям конфиг модуля по умолчанию
$cfg = [
	'user'           => !empty($user) ? $user : '',
	'template'       => !empty($template) ? $template : 'moder_notes/default',
	'nocache'        => !empty($nocache) ? $nocache : false,
	'cachePrefix'    => !empty($cachePrefix) ? $cachePrefix : 'moder_notes',
	'cacheSuffixOff' => !empty($cacheSuffixOff) ? $cacheSuffixOff : false,
	'cacheNameAddon' => ''
];

// Определяемся с шаблоном сайта
// Проверим куку пользователя и наличие параметра skin в реквесте.
$currentSiteSkin = (isset($_COOKIE['dle_skin'])) ? trim(totranslit($_COOKIE['dle_skin'], false, false)) : (isset($_REQUEST['skin'])) ? trim(totranslit($_REQUEST['skin'], false, false)) : $config['skin'];

// Если в итоге пусто — назначим опять шаблон из конфига.
if ($currentSiteSkin == '') {
	$currentSiteSkin = $config['skin'];
}

// Если папки с шаблоном нет — дальше не работаем.
if (!is_dir(ROOT_DIR . '/templates/' . $currentSiteSkin)) {
	die('no_skin');
}

if ($cfg['user'] == 'this') {
	$cfg['cacheNameAddon'] .= $_REQUEST['user'];

	$cfg['user'] = $db->safesql($_REQUEST['user']);
}

// Формируем имя кеша
$cacheName = implode('_', $cfg) . $currentSiteSkin;

// Определяем необходимость создания кеша для разных групп
$cacheSuffix = ($cfg['cacheSuffixOff']) ? false : true;

// Формируем имя кеша
$cacheName = md5(implode('_', $cfg));

// Дефолтное значение модуля
$moderNotes = false;

// Пытаемся получить данные из кеша
if (!$cfg['nocache']) {
	$moderNotes = dle_cache($cfg['cachePrefix'], $cacheName, $cacheSuffix);
}
// Если ничего не пришло из кеша — раблотаем
if (!$moderNotes) {

	$isModerator = $user_group[$member_id['user_group']]['allow_all_edit'];

	// Получаем информацию о пользоателе, про которого оставлена заметка
	$targetUser = ($cfg['user'] !== '') ? $db->super_query('SELECT user_id, name, logged_ip FROM ' . USERPREFIX . '_users WHERE name=\'' . $cfg['user'] . '\'') : null;

	// Если пользователь не залогинен или не обнаружен пользователь,
	// для которого написана заметка - значит ничего не нужно делать
	if (!$is_logged || !$targetUser['user_id']) {
		$moderNotes = '';
	} else {
		$tpl->result['moderNotes'] = '';
		$tpl->load_template($cfg['template'] . '.tpl');

		// Проверяем мультиаккаунты
		$targetUserNames  = [];
		$_targetUserNames = $db->super_query('SELECT name FROM ' . USERPREFIX . '_users WHERE logged_ip=\'' . $targetUser['logged_ip'] . '\'', true);

		if (count($_targetUserNames) > 1) {
			foreach ($_targetUserNames as $targetUserName) {
				$targetUserNames[] = $targetUserName['name'];
			}
			$tpl->set('[user_names]', '');
			$tpl->set('[/user_names]', '');

		} else {
			$tpl->set_block("'\\[user_names\\](.*?)\\[/user_names\\]'si", '');
		}

		$tpl->set('{user_names}', implode(', ', $targetUserNames));


		// TODO Добавить вывод автора публичной заметки
		$publicNotes = $db->super_query('SELECT * FROM ' . PREFIX . '_moder_notes WHERE user_name = \'' . $targetUser['name'] . '\' AND is_private = 0', true);

		if (count($publicNotes)) {
			$tpl->set('[public_notes]', '');
			$tpl->set('[/public_notes]', '');
		} else {
			$tpl->set_block("'\\[public_notes\\](.*?)\\[/public_notes\\]'si", '');
		}

		// Строим цикл из публичных заметок
		preg_match("'\\[pub_notes\\](.*?)\\[/pub_notes\\]'si", $tpl->copy_template, $matchContent);
		foreach ($publicNotes as $publicNote) {
			$publicNote['date'] = strtotime($publicNote['date']);
			if (date('Ymd', $publicNote['date']) == date('Ymd', $_TIME)) {
				$noteDate = $lang['time_heute'] . langdate(", H:i", $publicNote['date']);
			} elseif (date('Ymd', $publicNote['date']) == date('Ymd', ($_TIME - 86400))) {
				$noteDate = $lang['time_gestern'] . langdate(", H:i", $publicNote['date']);
			} else {
				$noteDate = langdate($config['timestamp_active'], $publicNote['date'], $short_news_cache);
			}

			$arReplace = [
				'{pub_note_text}' => $publicNote['text'],
				'{pub_note_date}' => $noteDate
			];

			$tpl->copy_template = strtr($tpl->copy_template, $arReplace);
			$tpl->copy_template = preg_replace("'\\[pub_notes\\](.*?)\\[/pub_notes\\]'si", "\\1\n" . $matchContent[0], $tpl->copy_template);
		}
		$tpl->set_block("'\\[pub_notes\\](.*?)\\[/pub_notes\\]'si", '');


		// Получаем приватную заметку
		$privateNote = $db->super_query('SELECT * FROM ' . PREFIX . '_moder_notes WHERE user_id=' . $member_id['user_id'] . ' AND user_name = \'' . $targetUser['name'] . '\' AND is_private = 1');

		if($privateNote['user_id']) {
			$tpl->set('[private_note]', '');
			$tpl->set('[/private_note]', '');
			$tpl->set('{private_note}', $privateNote['text']);

			$tpl->set_block("'\\[not_private_note\\](.*?)\\[/not_private_note\\]'si", '');
		} else {
			$tpl->set_block("'\\[private_note\\](.*?)\\[/private_note\\]'si", '');
			$tpl->set('[not_private_note]', '');
			$tpl->set('[/not_private_note]', '');
			$tpl->set('{private_note}', '');
		}


		$tpl->compile('moderNotes');
		$moderNotes = $tpl->result['moderNotes'];


		// Сохраняем данные в кеш
		if (!$cfg['nocache']) {
			create_cache($cfg['cachePrefix'], $moderNotes, $cacheName, $cacheSuffix);
		}

		$tpl->clear();
	}

}

// выводим результат работы модуля
echo $moderNotes;

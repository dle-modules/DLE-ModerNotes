<?php
/*
 * DLE-ModerNotes — Модуль заметок о пользователях
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9Tgv
 */

if (!defined('DATALIFEENGINE') || !defined('LOGGED_IN')) {
	die('Hacking attempt!');
}


echoheader('DLE-Starter', '"Hello Word" модуль для DLE');

echo '<div class="well relative">
		<span class="triangle-button green"><i class="icon-bell"></i></span>
		<p>Данный модуль предназначен исключительно для демонстрации работы админки.</p>
		<p>
			Все предложения по улучшению модуля можно направлять <a class="btn btn-blue" href="https://github.com/dle-modules/DLE-ModerNotes/issues/new" target="_blank">через систему тиккетов</a>
		</p>
</div>';


echofooter();


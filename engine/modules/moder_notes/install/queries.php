<?php
/*
 * DLE-ModerNotes — Модуль заметок о пользователях
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9Tgv
 */


/**
 * Этот файл отвеает за выполнение sql запросов во время установки модуля.
 */
return [
'CREATE TABLE `' . PREFIX . '_moder_notes` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL COMMENT \'ID пользователя, добавившего заметку\',
	`user_name` VARCHAR(40) NOT NULL COMMENT \'Логин пользователя, о котором написана заметка\',
	`text` TEXT NOT NULL COMMENT \'Текст заметки\',
	`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`is_private` TINYINT(1) NOT NULL DEFAULT \'1\' COMMENT \'Личная/публичная заметка\',
	PRIMARY KEY (`id`),
	INDEX `user_id` (`user_id`)
);
'
];

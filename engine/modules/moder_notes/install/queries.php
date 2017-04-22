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
'CREATE TABLE `' . PREFIX . '_user_notes` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`user_name` varchar(40) NOT NULL,
	`text` TEXT NOT NULL,
	`date` DATETIME NOT NULL,
	`is_private` tinyint(1) NOT NULL DEFAULT \'1\',
	PRIMARY KEY (`id`),
	KEY `user_id` (`user_id`)
);'
];

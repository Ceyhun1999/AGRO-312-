<?php
/*
=====================================================
 Multi-Language 4.9
-----------------------------------------------------
 Автор: Japing
-----------------------------------------------------
 https://japing.pw/
-----------------------------------------------------
 Copyright (c) 2014-2024  Japing
=====================================================
 Данный код защищен авторскими правами
=====================================================
*/

if(version_compare(phpversion(), '8.2.0', '>=')) {
	require (ENGINE_DIR . '/modules/multilanguage/tags.8.2.php');
} elseif(version_compare(phpversion(), '8.1.0', '>=')) {
	require (ENGINE_DIR . '/modules/multilanguage/tags.8.1.php');
} elseif(version_compare(phpversion(), '7.4.0', '>=')) {
	require (ENGINE_DIR . '/modules/multilanguage/tags.7.4.php');
}

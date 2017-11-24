<?php
/**
 * Autoload the autoload files of the individual standards.
 *
 * @category PHP
 * @package  QA-WP-Projects
 * @author   Juliette Reinders Folmer <qa_wp_projects_nospam@adviesenzo.nl>
 */

if ( file_exists( __DIR__ . '/vendor/wimg/php-compatibility/PHPCSAliases.php' ) ) {
	require_once __DIR__ . '/vendor/wimg/php-compatibility/PHPCSAliases.php';
}

if ( file_exists( __DIR__ . '/vendor/wp-coding-standards/wpcs/WordPress/PHPCSAliases.php' ) ) {
	require_once __DIR__ . '/vendor/wp-coding-standards/wpcs/WordPress/PHPCSAliases.php';
}

#!/usr/bin/php -d display_errors=1
<?php
	require_once("load.php");
	
	(new ArturasKaukenas\SimpleTest\Tests(__DIR__.\DIRECTORY_SEPARATOR."HTML"))->runAll(true);
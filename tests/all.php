#!/usr/bin/php -d display_errors=1
<?php
	require_once("load.php");

	if (
		((new ArturasKaukenas\SimpleTest\Tests(__DIR__.\DIRECTORY_SEPARATOR."XML"))->runAll())
			&&
		((new ArturasKaukenas\SimpleTest\Tests(__DIR__.\DIRECTORY_SEPARATOR."HTML"))->runAll())
	) {
		exit(0);
	}

	exit(1);
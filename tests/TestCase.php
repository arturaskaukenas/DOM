<?php
/*

Copyright 2024 Artūras Kaukėnas

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/

namespace ArturasKaukenas\tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase {
	/**
     * Reads the contents of a fixture file located under tests/Fixtures.
     *
     * @param 	string $relativePath	Path relative to the Fixtures directory, e.g. "XML/books.xml".
     * @return 	string
     */
	protected function fixture(string $relativePath) : string {
		return (string) \file_get_contents(__DIR__.\DIRECTORY_SEPARATOR."Fixtures".\DIRECTORY_SEPARATOR.$relativePath);
	}
}

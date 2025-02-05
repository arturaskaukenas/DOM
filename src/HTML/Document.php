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

namespace ArturasKaukenas\DOM\HTML;

use \ArturasKaukenas\DOM;

final class Document extends Node {
	public $HTML = null;
	public $HEAD = null;
	public $BODY = null;
	public $TITLE = null;
	public function __construct() {
		$this->
			setExpectedObject("html", new \ReflectionClass("\ArturasKaukenas\DOM\HTML\Document"), false)->//fix <html> tag
			setExpectedObject("head", new \ReflectionClass("\ArturasKaukenas\DOM\HTML\HTMLHeadElement"), false)->
			setExpectedObject("body", new \ReflectionClass("\ArturasKaukenas\DOM\HTML\HTMLElement"), false);
	}
}
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
	use \ArturasKaukenas\DOM;

	class BOOK extends DOM\XML\Node {
		public function __construct() {
			$this->
				setExpectedValue("id", DOM\NodeDataTypes::T_INT)->
				expects(
					(new DOM\Expected("author", DOM\NodeDataTypes::T_STRING))->
						process(
							function ($value) : string {
								return \strtoupper((string) $value);
							}
						)
				)->
				setExpectedValue("title", DOM\NodeDataTypes::T_STRING)->
				setExpectedValue("genre", DOM\NodeDataTypes::T_STRING)->
				expects(
					(new DOM\Expected("price", DOM\NodeDataTypes::T_FLOAT))->
						preValidate(
							function($value) : bool {
								if ($value === null) {
									return false;
								}
								
								return true;
							}
						)->
						validate(
							function (float $value) {
								if ($value > 10) {
									return "Should be cheaper than 10";
								}

								return true;
							}
						)
				)->
				setExpectedValue(
					"publish_date",
					DOM\NodeDataTypes::T_MIXED,
					function($value) {
						$timestamp = \strtotime((string) $value);
						if ($timestamp === false) {
							return null;
						}

						return (new \DateTime())->setTimestamp($timestamp);
					}
				)->
				setExpectedValue("description", DOM\NodeDataTypes::T_STRING);
		}
	}
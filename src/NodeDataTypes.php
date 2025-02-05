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

namespace ArturasKaukenas\DOM;

class NodeDataTypes {
	const T_UNDEFINED = -10;
	const T_PROTO = -1;
	const T_MIXED = 0;
	const T_ARRAY = 1;
	const T_STRING = 2;
	const T_INT = 3;
	const T_FLOAT = 4;
	const T_BOOL = 5;

	static function getTypeName(int $type) {
		$types = (new \ReflectionClass(__CLASS__))->getConstants();
		foreach ($types as $key => $value) {
			if ($value === $type) {
				return $key;
			}
		}

		return "T_UNDEFINED";
	}
}
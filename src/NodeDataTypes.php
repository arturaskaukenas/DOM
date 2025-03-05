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
	public const T_UNDEFINED = -100;
	public const T_PROTO = -2;
	public const T_ARRAY = -1;
	public const T_MIXED = 0;
	public const T_STRING = 1;
	public const T_INT = 2;
	public const T_FLOAT = 3;
	public const T_BOOL = 4;
	public const T_ARRAY_OF_PROTO = -12;
	public const T_ARRAY_OF_MIXED = 10;
	public const T_ARRAY_OF_STRING = 11;
	public const T_ARRAY_OF_INT = 12;
	public const T_ARRAY_OF_FLOAT = 13;
	public const T_ARRAY_OF_BOOL = 14;

	public static function getTypeName(int $type): string {
		$types = (new \ReflectionClass(__CLASS__))->getConstants();
		foreach ($types as $key => $value) {
			if ($value === $type) {
				return $key;
			}
		}

		return "T_UNDEFINED";
	}
}
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

enum NodeDataTypes: int {
	case T_UNDEFINED = -100;
	case T_PROTO = -2;
	case T_ARRAY = -1;
	case T_MIXED = 0;
	case T_STRING = 1;
	case T_INT = 2;
	case T_FLOAT = 3;
	case T_BOOL = 4;
	case T_ARRAY_OF_PROTO = -12;
	case T_ARRAY_OF_MIXED = 10;
	case T_ARRAY_OF_STRING = 11;
	case T_ARRAY_OF_INT = 12;
	case T_ARRAY_OF_FLOAT = 13;
	case T_ARRAY_OF_BOOL = 14;
}
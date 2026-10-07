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

namespace ArturasKaukenas\DOM\Expected;

use ArturasKaukenas\DOM\NodeDataTypes;

/**
 * Represents the expected properties of a node.
 *
 * @property		?callable				$processFunction		The function for processing.
 */

abstract class Expected implements IExpected {
	protected string $name = "";
	protected $type = NodeDataTypes::T_MIXED;

	protected bool $preValidate = false;
	protected $preValidateFunction = null;

	protected bool $preProcess = false;
	protected $preProcessFunction = null;

	protected bool $validate = false;
	protected $validateFunction = null;

	private ?bool $_isArray = null;

	public function __construct(string $name, int $type = NodeDataTypes::T_MIXED) {
		$this->name = $name;
		$this->type = $type;
	}

	public function typeOf(int $type): static {
		$this->type = $type;
		$this->_isArray = null;

		return $this;
	}

	public function preValidate(callable $function) : static {
		$this->preValidate = true;
		$this->preValidateFunction = $function;
		return $this;
	}

	public function process(callable $function) : static {
		if ($this->isPrototype()) {
			throw new \Exception("process not allowed for prototypes");
		}

		$this->preProcess = true;
		$this->preProcessFunction = $function;
		return $this;
	}

	public function validate(callable $function) : static {
		$this->validate = true;
		$this->validateFunction = $function;
		return $this;
	}

	public function isArray(): bool {
		if ($this->_isArray !== null) {
			return $this->_isArray;
		}
		$this->_isArray = (
			($this->type === NodeDataTypes::T_ARRAY)
				||
			($this->type === NodeDataTypes::T_ARRAY_OF_PROTO)
				||
			($this->type === NodeDataTypes::T_ARRAY_OF_MIXED)
				||
			($this->type === NodeDataTypes::T_ARRAY_OF_STRING)
				||
			($this->type === NodeDataTypes::T_ARRAY_OF_INT)
				||
			($this->type === NodeDataTypes::T_ARRAY_OF_FLOAT)
				||
			($this->type === NodeDataTypes::T_ARRAY_OF_BOOL)
		);

		return $this->_isArray;
	}

	public function isPrototype(): bool {
		return (($this->type === NodeDataTypes::T_PROTO) || ($this->type === NodeDataTypes::T_ARRAY_OF_PROTO));
	}

	public function getType() : int {
		return $this->type;
	}

	public function getName() : string {
		return $this->name;
	}

	public function isPreValidationEnabled() : bool {
		return $this->preValidate;
	}

	public function getPreValidateFunction() : ?callable {
		return $this->preValidateFunction;
	}

	public function isValidationEnabled() : bool {
		return $this->validate;
	}

	public function getValidateFunction() : ?callable {
		return $this->validateFunction;
	}

	public function isPreProcessEnabled() : bool {
		return $this->preProcess;
	}

	public function getPreProcessFunction() : ?callable {
		return $this->preProcessFunction;
	}
}
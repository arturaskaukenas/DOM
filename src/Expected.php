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

/**
 * Represents the expected properties of a node.
 *
 * @property		string 					$name					The name of the expected property.
 * @property		value-of<NodeDataTypes> $type					The data type of the expected property.
 * @property-read	bool 					$usePreValidate			Indicates if pre-validation is enabled.
 * @property		?callable 				$preValidateFunction 	The function for pre-validation.
 * @property-read	bool					$useProcess				Indicates if processing is enabled.
 * @property		?callable				$processFunction		The function for processing.
 * @property-read	bool					$usePrototype			Indicates if a prototype is used.
 * @property		?string					$prototypeName			The name of the prototype.
 * @property		?\ReflectionClass		$prototypeClass			The class of the prototype.
 */
class Expected {
	public $name = "";
	public $type = NodeDataTypes::T_MIXED;

	public $usePreValidate = false;
	public $preValidateFunction = null;

	public $useProcess = false;
	public $processFunction = null;

	public $useValidate = false;
	public $validateFunction = null;

	public $usePrototype = false;
	public $prototypeName = null;
	public $prototypeClass = null;

	/**
     * Constructs a new instance of the Expected class.
     *
     * @param string $name	The name of the expected property.
     * @param int $type		The data type of the expected property (use constants from NodeDataTypes).
     */
	public function __construct(string $name, int $type = NodeDataTypes::T_MIXED) {
		$name = \strtoupper($name);
		$this->name = $name;
		$this->type = $type;
	}

	/**
     * Specifies the data type of the expected property.
     *
     * @param int $type		The data type to set (use constants from NodeDataTypes class).
     * @return $this
     */
	public function typeOf(int $type) {
		if ($this->usePrototype) {
			if (($this->type !== NodeDataTypes::T_PROTO) || ($this->type !== NodeDataTypes::T_ARRAY)) {
				throw new \Exception("Expected object type should be 'NodeDataTypes::T_PROTO' or 'NodeDataTypes::T_ARRAY'");
			}
		}

		$this->type = $type;

		return $this;
	}

	 /**
     * Specifies a function for pre-validation.
     *
	 * @param callable $function	The function for pre-validation.
     *								The function should accept the value of the property as its only parameter and
     *								return either a boolean indicating whether the value is valid or a string
     *								describing the reason for validation failure.
     * @return $this
     */
	public function preValidate(callable $function) : Expected {
		$this->usePreValidate = true;
		$this->preValidateFunction = $function;
		return $this;
	}

	/**
     * Specifies a function for processing.
     *
     * @param callable $function	The function for processing.
     *								The function should accept the value of the property as its only parameter and
     *								return the processed value.
     * @return $this
     */
	public function process(callable $function) : Expected {
		if ($this->usePrototype) {
			throw new \Exception("process not allowed for prototypes");
		}

		$this->useProcess = true;
		$this->processFunction = $function;
		return $this;
	}

	/**
     * Specifies a function for validation.
     *
     * @param callable $function	The function for validation.
     *								The function should accept the value of the property as its only parameter and
     *								return either a boolean indicating whether the value is valid or a string
     *								describing the reason for validation failure.
     * @return $this
     */
	public function validate(callable $function) : Expected {
		$this->useValidate = true;
		$this->validateFunction = $function;
		return $this;
	}

	/**
     * Specifies the prototype of the expected child by class name.
     *
     * @param string $name The name of the class.
     * @return $this
     */
	public function prototypeOfClassName(string $name) : Expected {
		$this->checkForPrototype();

		$this->usePrototype = true;
		$this->prototypeClass = null;
		$this->prototypeName = $name;
		
		return $this;
	}

	/**
     * Specifies the prototype of the expected child by class.
     *
     * @param \ReflectionClass $class The reflection class of the prototype.
     * @return $this
     */
	public function prototypeOfObject(\ReflectionClass $class) : Expected {
		$this->checkForPrototype();

		$this->usePrototype = true;
		$this->prototypeName = null;
		$this->prototypeClass = $class;

		return $this;
	}

	private function checkForPrototype() {
		if ($this->useProcess) {
			throw new \Exception($this->name.": prototype usage not allowed when processing is used");
		}

		if (($this->type !== NodeDataTypes::T_PROTO) && ($this->type !== NodeDataTypes::T_ARRAY)) {
			throw new \Exception($this->name.": expected type should be 'NodeDataTypes::T_PROTO' or 'NodeDataTypes::T_ARRAY', '".NodeDataTypes::getTypeName($this->type)."' given");
		}
	}
}
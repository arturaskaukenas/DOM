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
 * Represents the expected element of a node.
 *
 */
class Element extends Expected {
	
	protected ?string $prototypeName = null;
	protected ?\ReflectionClass $prototypeClass = null;

	public function __construct(string $name, int $type = NodeDataTypes::T_MIXED) {
		$name = \strtoupper($name);
		parent::__construct($name, $type);
	}

	/**
     * Specifies the prototype of the expected child by class name.
     *
     * @param string $name The name of the class.
     * @return $this
     */
	public function prototypeOfClassName(string $name) : self {
		$this->checkForPrototype();

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
	public function prototypeOfObject(\ReflectionClass $class) : self {
		$this->checkForPrototype();

		$this->prototypeName = null;
		$this->prototypeClass = $class;

		return $this;
	}

	/**
	 * Retrieves the prototype name.
	 *
	 * @return string|null The prototype name, or null if not set.
	 */
	public function getPrototypeName(): ?string {
		return $this->prototypeName;
	}

	/**
	 * Retrieves the prototype class as a ReflectionClass instance.
	 *
	 * @return \ReflectionClass|null The ReflectionClass instance representing the prototype class, or null if not available.
	 */
	public function getPrototypeClass(): ?\ReflectionClass {
		return $this->prototypeClass;
	}

	private function checkForPrototype(): void {
		if ($this->isPreProcessEnabled()) {
			throw new \Exception($this->name.": prototype usage not allowed when processing is used");
		}

		if (($this->type !== NodeDataTypes::T_PROTO) && ($this->type !== NodeDataTypes::T_ARRAY_OF_PROTO)) {
			throw new \Exception($this->name.": expected type should be 'NodeDataTypes::T_PROTO' or 'NodeDataTypes::T_ARRAY_OF_PROTO', '".NodeDataTypes::getTypeName($this->type)."' given");
		}
	}
}
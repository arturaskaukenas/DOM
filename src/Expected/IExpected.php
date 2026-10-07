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

interface IExpected {
	/**
     * Constructs a new instance of the Expected class.
     *
     * @param string $name	The name of the expected property.
     * @param int $type		The data type of the expected property (use constants from NodeDataTypes).
     */
	public function __construct(string $name, int $type = NodeDataTypes::T_MIXED);

	/**
     * Specifies the data type of the expected property.
     *
     * @param int $type		The data type to set (use constants from NodeDataTypes class).
     * @return $this
     */
	public function typeOf(int $type): static;

	 /**
     * Specifies a function for pre-validation.
     *
	 * @param callable $function	The function for pre-validation.
     *								The function should accept the value of the property as its only parameter and
     *								return either a boolean indicating whether the value is valid or a string
     *								describing the reason for validation failure.
     * @return $this
     */
	public function preValidate(callable $function) : static;

	/**
     * Specifies a function for processing.
     *
     * @param callable $function	The function for processing.
     *								The function should accept the value of the property as its only parameter and
     *								return the processed value.
     * @return $this
     */
	public function process(callable $function) : static;

	/**
     * Specifies a function for validation.
     *
     * @param callable $function	The function for validation.
     *								The function should accept the value of the property as its only parameter and
     *								return either a boolean indicating whether the value is valid or a string
     *								describing the reason for validation failure.
     * @return $this
     */
	public function validate(callable $function) : static;

	/**
	 * Check if element is array.
	 *
	 * @return bool.
	 */
	public function isArray() : bool;

	/**
	 * Check if element is prototype.
	 *
	 * @return bool.
	 */
	public function isPrototype() : bool;

	/**
	 * Get the data type of expected property.
	 *
	 * @return value-of<NodeDataTypes>.
	 */
	public function getType() : int;

	/**
	 * Get the name of expected property.
	 *
	 * @return string.
	 */
	public function getName() : string;

	/**
	 * Indicates if pre-validation is enabled.
	 *
	 * @return bool.
	 */
	public function isPreValidationEnabled() : bool;

	/**
	 * Gets the pre-validation function.
	 *
	 * @return ?callable.
	 */
	public function getPreValidateFunction() : ?callable;

	/**
	 * Indicates if pre-validation is enabled.
	 *
	 * @return bool.
	 */
	public function isValidationEnabled() : bool;

	/**
	 * Gets the pre-validation function.
	 *
	 * @return ?callable.
	 */
	public function getValidateFunction() : ?callable;

	/**
	 * Indicates if pre-process enambled.
	 *
	 * @return bool.
	 */
	public function isPreProcessEnabled() : bool;

	/**
	 * Gets the pre-process function.
	 *
	 * @return ?callable.
	 */
	public function getPreProcessFunction() : ?callable;
}
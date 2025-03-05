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

interface IParser {

	/**
     * Initializes the parser.
     *
     * @param ?INode $currentScope The current parsing scope.
     * @param bool $skipParser Whether to skip initializing the XML parser.
     * @return void
     */
	public function init(?INode $currentScope = null, bool $skipParser = false) : void;

	/**
     * Parses data.
     *
     * @param string $data The data to parse.
     * @return bool True if parsing was successful, false otherwise.
     */
	public function parse(string $data) : bool;

	/**
     * Finalizes parsing and cleans up resources.
     *
     * @return void
     */
	public function finalize() : void;

	/**
     * Checks if any parsing errors occurred.
     *
     * @return bool
     */
	public function errorsExists() : bool;

	/**
     * Retrieves parsing errors.
     *
     * @return array<string>
     */
	public function getErrors() : array;

	/**
     * Cleans up resources and resets the parser state.
     *
     * @return void
     */
	public function clean() : void;

	/**
     * Registers a custom node template for parsing.
     *
     * @param \ReflectionClass $class The class to register.
     * @return IParser The parser instance.
     */
	public function registerNode(\ReflectionClass $class) : IParser;

	/**
     * Checks if a node template is registered.
     *
     * @param string 	$elementName The name of the node template.
     * @return bool True if the node class is registered, false otherwise.
     */
	public function isNodeRegistered(string $elementName) : bool;

	/**
     * Registers a callback to be executed when finalizing a specific node type.
     *
     * @param string 	$name The name of the node type.
     * @param callable 	$callback The callback function.
     * @return IParser 	The parser instance for method chaining.
     */
	public function onFinalizeNode(string $name, callable $callback) : IParser;

	public function finalizeNode() : void;

	public function setData(string $value) : void;
}
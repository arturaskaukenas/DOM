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

interface INode extends INodeBasic, INodeActions {
	/**
     * Sets an expected values, templates, processing rules for child element.
     *
     * @param 	Expected $expected    				Rules class.
     */
	public function expects(Expected $expected) : INode;
	
	/**
     * Sets an expected node template for child element.
     *
     * @param 	string $name    					The name of the expected element.
     * @param 	string|\ReflectionClass $object		The class name or \ReflectionClass instance representing the expected object.
     * @param 	bool $isArray						Indicates whether the expected element is an array or single element.
     * @return 	INode 								The node instance.
     */
	public function setExpectedObject(string $name, $object, bool $isArray) : INode;

	/**
     * Sets an expected value based on a child element.
     *
     * @param 	string		$name            	The name of the expected element.
     * @param 	int			$type            	The data type of the expected value (use constants from NodeDataTypes class).
     * @param 	?callable	[$processFunction] 	The function to process the value before setting it.
     * @return 	INode							The node instance.
     */
	public function setExpectedValue(string $name, int $type, ?callable $processFunction = null) : INode;

	/**
     * Sets a callback to parse data.
     *
     * @param 	callable	$callback 			The parser callback function.
     * @return 	void
     */
	public function useDataParser(callable $callback) : void;

	/**
     * Sets whether child elements should be ignored or not.
     *
     * @param 	bool		$value
     * @return 	void
     */
	public function setIgnoreChildren(bool $value) : void;

	/**
     * Sets whether child elements should be ignored or not only when initial value not set.
     *
     * @param 	bool		$value
     * @return 	void
     */
	public function setIgnoreChildrenIfNotExists(bool $value) : void;

	/**
     * Configures whether to treat inner content as child elements.
     *
     * @param 	bool		$value
     * @return 	void
     */
	public function setDataAsChildren(bool $value) : void;
}
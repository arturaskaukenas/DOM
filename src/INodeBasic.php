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

interface INodeBasic {
	/**
     * Removes the current node from its parent node, if it exists.
     *
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element/remove
	 *
     * @return void
     */
	public function remove() : void;

	/**
     * Sets the parser instance for the node.
     *
     * @param IParser $parser The parser instance.
     * @return void
     */
	public function setParser(IParser $parser) : void;

	public function setData(?string $data) : void;

     /**
      * Retrieves the node's data.
      *
      * @return ?string.
      */
     public function getData() : ?string;

     /**
     * Appends an expected value to the node.
     *
     * @param string $name		The name of the value.
     * @param string $value		The value to append.
     * @return void
     */
	public function appendExpectedValue(string $name, string $value) : void;

	public function setParentNode(INode $parentNode) : void;

	public function setParserReference(INode $node, ?IParser $parser);

	public function updateChildrenParser() : void;

	public function finalize() : bool;
}
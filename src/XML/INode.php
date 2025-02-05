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

namespace ArturasKaukenas\DOM\XML;

interface INode extends \ArturasKaukenas\DOM\INode {

	/**
     * Retrieves the inner XML content of the node, including child nodes and data.
     *
     * @return string The inner XML content of the node.
     */
	public function getInner() : string;

	/**
     * Retrieves the inner XML content of the node, including child nodes and data.
     *
     * @return string The inner XML content of the node.
     */
	public function getInnerXML() : string;

	/**
     * Sets the inner content of the node with the provided data.
     *
     * @param ?string $data The data to set as inner content.
     * @return void
     */
	public function setInner(?string $data) : void;

	/**
     * Sets the inner content of the node with the provided data.
     *
     * @param ?string $data The data to set as inner content.
     * @return void
     */
	public function setInnerXML(?string $data) : void;
}
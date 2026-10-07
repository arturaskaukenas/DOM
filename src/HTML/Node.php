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

namespace ArturasKaukenas\DOM\HTML;

use ArturasKaukenas\DOM;

abstract class Node extends DOM\Node implements INode {
	public bool $dataAsChildren = true;

	public function getInner() : string {
		return $this->getInnerHTML();
	}

	public function getInnerHTML() : string {
		$HTML = [];
		if ($this->nodeName !== null) {
			$HTML[] = "<".$this->nodeName.$this->genAttributesHTML().">";
		}

		for ($i = 0; $i < $this->childElementsQty; $i++) {
			$HTML[] = $this->children[$i]->getInner();
		}

		if (!$this->dataAsChildren) {
			$HTML[] = $this->getValidFormatData();
		}

		if ($this->nodeName !== null) {
			$HTML[] = "</".$this->nodeName.">";
		}

		return \implode("", $HTML);
	}
	
	private function genAttributesHTML() : string {
		if (\count($this->attributes) === 0) {
			return "";
		}
		
		$attributes = [];
		foreach ($this->attributes as $key => $value) {
			$key = (string) $key;
			$value = (string) $value;
			$attributes[] = $this->escapeAttributeKey($key).'="'.$this->escapeAttributeValue($value).'"';
		}

		return " ".\implode(" ", $attributes);
	}

	private function escapeAttributeKey(string $text) : string {
		return \htmlspecialchars($text, \ENT_XML1 | \ENT_QUOTES, "UTF-8");
	}

	private function escapeAttributeValue(string $text) : string {
		$text = \str_replace('"', "&quot;", $text);
		$text = \str_replace("'", "&apos;", $text);

		return $text;
	}

	public function setInner(?string $data) : void {
		$this->setInnerHTML($data);
	}

	public function setInnerHTML(?string $data) : void {
		$this->cleanChildren();
		$this->setData(null);

		$this->parser->clean();
		$this->parser->init($this);
		$this->parser->parse($data);
		$this->parser->finalize();
		$this->parser->clean();		
	}

	public function getValidFormatData() : string {
		return \htmlspecialchars((string) $this->data);
	}
}
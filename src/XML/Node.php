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

use ArturasKaukenas\DOM;

abstract class Node extends DOM\Node implements INode {
	public $dataAsChildren = true;

	public function getInner() : string {
		return $this->getInnerXML();
	}

	public function getInnerXML() : string {
		$XML = array();
		if ($this->nodeName !== null) {
			$XML[] = "<".$this->nodeName.$this->genAttributesXML().">";
		}

		for ($i = 0; $i < $this->childElementCount; $i++) {
			$XML[] = $this->children[$i]->getInnerXML();
		}

		if (!$this->dataAsChildren) {
			$XML[] = $this->getValidXMLData();
		}

		if ($this->nodeName !== null) {
			$XML[] = "</".$this->nodeName.">";
		}

		return \implode("", $XML);
	}

	private function genAttributesXML() : string {
		if (\count($this->attributes) === 0) {
			return "";
		}
		
		$attributes = array();
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
		$this->setInnerXML($data);
	}

	public function setInnerXML(?string $data) : void {
		$this->cleanChildren();
		$this->setData(null);

		$this->parser->clean();
		$this->parser->init($this);
		$this->parser->parse($data);
		$this->parser->finalize();
		$this->parser->clean();		
	}

	public function getValidXMLData() : string {
		$data = (string) $this->data;
		if (\strpos($data, "<") !== false) {
			return "<![CDATA[".$data."]]>";
		}

		return $data;
	}

	public function __debugInfo() {
		$data = \get_object_vars($this);
		unset($data["parentNode"]);
		unset($data["parser"]);

		return $data;
	}
}
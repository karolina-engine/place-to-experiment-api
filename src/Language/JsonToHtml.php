<?php


namespace Karolina\Language;


Class JsonToHtml {
	
	private $converter;
	private $json;

	public function __construct ($json) {

		$this->json = $json;

		$jsonToHtml = new \Sioen\JsonToHtml();
		$jsonToHtml->addConverter(new \Sioen\JsonToHtml\BlockquoteConverter());
		$jsonToHtml->addConverter(new \Sioen\JsonToHtml\HeadingConverter());
		$jsonToHtml->addConverter(new \Sioen\JsonToHtml\IframeConverter());
		$jsonToHtml->addConverter(new \Sioen\JsonToHtml\ImageConverter());
		$jsonToHtml->addConverter(new \Sioen\JsonToHtml\BaseConverter());
		$this->converter = $jsonToHtml;

	}

	public function getHTML () {

		$html = $this->converter->toHtml($this->json);
		return $html;


	}

}

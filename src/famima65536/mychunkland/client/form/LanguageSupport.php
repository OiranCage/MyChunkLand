<?php

namespace famima65536\mychunkland\client\form;



use pocketmine\lang\Language;

trait LanguageSupport {
	private Language $language;

	public function setLanguage(Language $language){
		$this->language = $language;
	}

	public function getLanguage(): Language{
		return $this->language;
	}
}
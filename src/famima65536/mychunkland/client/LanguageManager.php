<?php

namespace famima65536\mychunkland\client;

use pocketmine\lang\Language;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

/**
 * @method static LanguageManager getInstance()
 */
class LanguageManager {
	use SingletonTrait;


	/** @var Language[] */
	private array $languages = [];

	public function __construct(string $path){
		$list = Language::getLanguageList($path);
		foreach($list as $langName){
			$this->languages[$langName] = new Language($langName, $path, "en_US");

		}

	}

	public function getLanguageFor(Player $player){
		return $this->languages[$player->getLocale()] ?? $this->languages["en_US"];
	}

}
<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\Section;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\Player;

class MyLandListForm implements Form {

	/**
	 * @param Section[] $sections
	 */
	public function __construct(private array $sections){
	}

	/**
	 * @inheritDoc
	 */
	public function handleResponse(Player $player, $data): void{
		if(!is_int($data) or $data >= count($this->sections)){
			Loader::getInstance()->getFormSession($player)->previous();
			return;
		}

		$section = $this->sections[$data];
		Loader::getInstance()->getFormSession($player)->open(new LandMenuForm($section));
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(){
		$json = [
			"type" => "form",
			"title" => "My Land List",
			"content" => "show list"
		];

		$buttons = [];
		foreach($this->sections as $section){
			$buttons[] = [
				"text" => "{$section->getCoordinate()->getX()}.{$section->getCoordinate()->getZ()}@{$section->getCoordinate()->getWorldName()}"
			];
		}

		$json["buttons"] = $buttons;

		return $json;
	}
}
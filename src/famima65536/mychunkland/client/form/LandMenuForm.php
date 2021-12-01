<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\Section;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

class LandMenuForm implements Form {

	public function __construct(private Section $section){
	}

	/**
	 * @inheritDoc
	 */
	public function handleResponse(Player $player, $data): void{
		if(!is_int($data)){
			Loader::getInstance()->getFormSession($player)->previous();
			return;
		}

		switch($data){
			case 0:
				$coordinate = $this->section->getCoordinate();
				$position = new Vector3(($coordinate->getX() << 4) + 8, 64, ($coordinate->getZ() << 4) + 8);
				$player->teleport($player->getServer()->getLevelByName($coordinate->getWorldName())->getSafeSpawn($position));
				break;

			case 2:
				Loader::getInstance()->getFormSession($player)->open(new EditLandForm($this->section));
				break;

			default:
				Loader::getInstance()->getFormSession($player)->previous();
				break;
		}


	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(){
		$section = $this->section;
		return [
			"type" => "form",
			"title" => "Land Menu",
			"content" => join("\n",[
				"Land: {$section->getCoordinate()->getX()}.{$section->getCoordinate()->getZ()}@{$section->getCoordinate()->getWorldName()}",
				"Group Access Permission: {$section->getGroupPermission()->toString()}",
				"Other Access Permission: {$section->getOtherPermission()->toString()}",
				"Choose action below"
			]),
			"buttons" => [
				["text" => "Teleport"],
				["text" => "Add Group Member"],
				["text" => "Edit Access Permission"],
				["text" => "Abandon This Land"]
			]
		];
	}
}
<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\client\task\AsyncSectionOwnTask;
use famima65536\mychunkland\system\model\PlayerUserId;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\Player;

class CentralForm implements Form {

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
				Loader::getInstance()->asyncCacheSectionByOwner(new PlayerUserId($player->getName()), function(array $sections)use($player){
					Loader::getInstance()->getFormSession($player)->open(new MyLandListForm($sections));
				});
				break;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(){
		return [
			"type" => "form",
			"title" => "MyChunkLand Central Form",
			"content" => "Choose action",
			"buttons" => [
				["text" => "My Land List"],
				["text" => "Own Land"]
			]
		];
	}
}
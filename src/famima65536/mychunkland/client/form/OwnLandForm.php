<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\AccessPermission;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use pocketmine\form\Form;
use pocketmine\player\Player;

class OwnLandForm implements Form {


	public function __construct(private ChunkCoordinate $coordinate){
	}
	/**
	 * @inheritDoc
	 */
	public function handleResponse(Player $player, $data): void{
		if(!is_bool($data)){
			return;
		}

		if($data){
			Loader::getInstance()->loadAndActionOnSection($this->coordinate, function($section) use ($player){
				if($section !== null){
					$player->sendMessage("Land has been owned.");
					return;
				}

				Loader::getInstance()->asyncSaveSection(new Section($this->coordinate, new PlayerUserId($player->getName()), new ShareGroup([]), new AccessPermission(true, true, false),new AccessPermission(false, false, false)));
				$player->sendMessage("Successful!");
			});
		}
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(){
		return [
			"type" => "modal",
			"title" => "Buy this chunk land",
			"content" => "Do you own this land? cost: xxx",
			"button1" => "Yes",
			"button2" => "No"
		];
	}
}
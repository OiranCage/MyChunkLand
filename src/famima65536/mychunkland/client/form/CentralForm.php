<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\client\task\AsyncSectionSaveTask;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
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

			case 1:
				$position = $player->getPosition();
				$chunkX = $position->getFloorX() >> 4;
				$chunkZ = $position->getFloorZ() >> 4;
				$worldName = $position->getLevel()->getFolderName();
				Loader::getInstance()->getFormSession($player)->open(new OwnLandForm(new ChunkCoordinate($chunkX, $chunkZ, $worldName)));
				break;

			case 2:
				$position = $player->getPosition();
				$chunkX = $position->getFloorX() >> 4;
				$chunkZ = $position->getFloorZ() >> 4;
				$worldName = $position->getLevel()->getFolderName();
				$coordinate = new ChunkCoordinate($chunkX, $chunkZ, $worldName);

				Loader::getInstance()->loadAndActionOnSection($coordinate, function(?Section $section) use ($player){
					if($section !== null and $section->getOwnerId()->equals(new PlayerUserId($player->getName()))){
						Loader::getInstance()->getFormSession($player)->open(new EditLandForm($section));
						return;
					}

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
				["text" => "Own Land"],
				["text" => "Edit/View Here"]
			]
		];
	}
}
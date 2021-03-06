<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use pocketmine\player\Player;

class CentralForm extends LanguageSupportForm {

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
				$worldName = $position->getWorld()->getFolderName();
				Loader::getInstance()->getFormSession($player)->open(new OwnLandForm(new ChunkCoordinate($chunkX, $chunkZ, $worldName)));
				break;

			case 2:
				$position = $player->getPosition();
				$chunkX = $position->getFloorX() >> 4;
				$chunkZ = $position->getFloorZ() >> 4;
				$worldName = $position->getWorld()->getFolderName();
				$coordinate = new ChunkCoordinate($chunkX, $chunkZ, $worldName);

				Loader::getInstance()->loadAndActionOnSection($coordinate, function(?Section $section) use ($player){
					if($section !== null and $section->getOwnerId()->equals(new PlayerUserId($player->getName()))){
						Loader::getInstance()->getFormSession($player)->open(new EditPermissionForm($section));
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
			"title" => $this->getLanguage()->get('form.central.title'),
			"content" => $this->getLanguage()->get('terms.choose-action'),
			"buttons" => [
				["text" => $this->getLanguage()->get("form.central.my-land-list")],
				["text" => $this->getLanguage()->get("form.central.buy-land")],
				["text" => $this->getLanguage()->get("form.central.edit-view-here")]
			]
		];
	}
}
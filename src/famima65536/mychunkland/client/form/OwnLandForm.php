<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\client\SettingManager;
use famima65536\mychunkland\client\usecase\BuyLand;
use famima65536\mychunkland\system\model\AccessPermission;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use pocketmine\form\Form;
use pocketmine\player\Player;

class OwnLandForm extends LanguageSupportForm {


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
			(new BuyLand($player, $this->coordinate, Loader::getInstance()))->invokes();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(){
		$price = SettingManager::getInstance()->getSettingForWorldByName($this->coordinate->getWorldName())->land_price;
		return [
			"type" => "modal",
			"title" => $this->getLanguage()->get('form.buy-land.title'),
			"content" => $this->getLanguage()->translateString('form.buy-land.content', [$price]),
			"button1" => $this->getLanguage()->get('terms.yes'),
			"button2" => $this->getLanguage()->get('terms.no')
		];
	}
}
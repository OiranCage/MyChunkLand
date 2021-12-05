<?php

namespace famima65536\mychunkland\client\usecase;

use famima65536\mychunkland\client\LanguageManager;
use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\client\SettingManager;
use famima65536\mychunkland\system\model\AccessPermission;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use oiran\miwallet\api\WalletLib;
use pocketmine\player\Player;

class AbandonLand {
	public function __construct(private Player $player, private Section $section, private Loader $loader){
	}

	public function invokes(){
		$this->loader->loadAndActionOnSection($this->section->getCoordinate(), function(?Section $section){
			$player = $this->player;
			$language = LanguageManager::getInstance()->getLanguageFor($player);
			if($section === null or !$section->getOwnerId()->equals(new PlayerUserId($player->getName()))){
				$player->sendMessage("Something goes wrong...");
				return;
			}

			$this->loader->asyncDeleteSection($section);
		});
	}
}
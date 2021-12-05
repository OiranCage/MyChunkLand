<?php

namespace famima65536\mychunkland\client\usecase;

use famima65536\mychunkland\client\LanguageManager;
use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\client\SettingManager;
use famima65536\mychunkland\system\model\AccessPermission;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use oiran\miwallet\api\WalletLib;
use oiran\miwallet\store\WalletStore;
use pocketmine\player\Player;

class BuyLand {

	public function __construct(private Player $player, private ChunkCoordinate $coordinate, private Loader $loader){
	}

	public function invokes(){
		$this->loader->loadAndActionOnSection($this->coordinate, function($section){
			$player = $this->player;
			$language = LanguageManager::getInstance()->getLanguageFor($player);
			if($section !== null){
				$player->sendMessage($language->get('message.error.section-already-owned'));
				return;
			}

			$wallet = WalletLib::findWallet($player->getXuid());
			$landPrice =SettingManager::getInstance()->getSettingForWorldByName($this->coordinate->getWorldName())->land_price;
			if($wallet->getMoney()->getAmount() < $landPrice){
				$player->sendMessage($language->translateString('message.error.not-enough-money', [$landPrice]));
				return;
			}

			$wallet->spend($landPrice);
			Loader::getInstance()->asyncSaveSection(new Section($this->coordinate, new PlayerUserId($player->getName()), new ShareGroup([]), new AccessPermission(true, true, false),new AccessPermission(false, false, false)));
			$player->sendMessage($language->get('message.successful.buy'));
		});
	}
}
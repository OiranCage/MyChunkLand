<?php

namespace famima65536\mychunkland\client;

use famima65536\mychunkland\client\task\AsyncSectionLoadTask;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;

class EventListener implements Listener {
	public function __construct(private Loader $loader){
	}

	public function onBreakBlock(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$userId = new PlayerUserId($player->getName());
		$block = $event->getBlock();
		$coordinate = new ChunkCoordinate($block->getFloorX() << 4, $block->getFloorX() << 4, $player->getLevel()->getFolderName());
		if(!$this->loader->hasCachedSection($coordinate)){
			$this->loader->tryAsyncCacheSection([$coordinate]);
			$player->sendMessage("土地情報が読み込まれていないため編集できません。");
		}

	}
}
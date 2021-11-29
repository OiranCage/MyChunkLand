<?php

namespace famima65536\mychunkland\client;

use famima65536\mychunkland\client\task\AsyncSectionLoadTask;
use famima65536\mychunkland\client\task\ShowChunkBoxTask;
use famima65536\mychunkland\system\model\AccessPermission;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use famima65536\mychunkland\system\model\UserId;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\scheduler\ClosureTask;

class EventListener implements Listener {
	public function __construct(private Loader $loader){
	}

	public function onInteract(PlayerInteractEvent $event){
		switch($event->getAction()){
			case PlayerInteractEvent::RIGHT_CLICK_BLOCK:
				$player = $event->getPlayer();
				$position = $player->getPosition();
				$chunkX = $position->getFloorX() >> 4;
				$chunkZ = $position->getFloorZ() >> 4;
				$section = new Section(new ChunkCoordinate($chunkX, $chunkZ, $position->getLevel()->getFolderName()), new PlayerUserId($player->getName()), new ShareGroup([]), new AccessPermission(true, true, false), new AccessPermission(false, false, false));
				$this->loader->asyncOwnSection($section);
		}

	}


	public function onBreakBlock(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$userId = new PlayerUserId($player->getName());
		$block = $event->getBlock();
		$coordinate = new ChunkCoordinate($block->getFloorX() >> 4, $block->getFloorZ() >> 4, $player->getLevel()->getFolderName());
		if(!$this->loader->hasCachedSection($coordinate)){
			$this->loader->tryAsyncCacheSection([$coordinate]);
			$player->sendMessage("土地情報が読み込まれていないため編集できません。");
			$event->setCancelled();
			return;
		}

		$section = $this->loader->getCachedSection($coordinate);
		if($section === null){
			$player->sendMessage("所有していない土地は編集できません。");
			$event->setCancelled();
			return;
		}

		if(!$section->getPermissionFor($userId)->isWritable()){
			$player->sendMessage("権限がないため編集できません");
			$event->setCancelled();
			return;
		}

	}

	public function onPlayerMove(PlayerMoveEvent $event){
		$position = $event->getPlayer()->getPosition();
		$chunkX = $position->getFloorX() >> 4;
		$chunkZ = $position->getFloorZ() >> 4;
		$worldName = $event->getPlayer()->getLevel()->getFolderName();
		$coordinates = [];
		for($dx = -2; $dx <= 2; $dx++){
			for($dz = -2; $dz <= 2; $dz++){
				$coordinate = new ChunkCoordinate($chunkX+$dx, $chunkZ+$dz, $worldName);
				if(!$this->loader->hasCachedSection($coordinate)){
					$coordinates[] = $coordinate;
				}
			}
		}
		$this->loader->tryAsyncCacheSection($coordinates);

		$coordinate = new ChunkCoordinate($chunkX, $chunkZ, $worldName);
		if(!$this->loader->hasCachedSection($coordinate)){
			$event->getPlayer()->sendTip("uncached chunk");
			return;
		}

		$section = $this->loader->getCachedSection($coordinate);
		if($section === null){
			$event->getPlayer()->sendTip("cached chunk: none");
			return;
		}

		$event->getPlayer()->sendTip("cached chunk\nowner {$section->getOwnerId()->getPrefix()}:{$section->getOwnerId()->getName()}\ngperm {$section->getGroupPermission()->toString()}\noperm {$section->getOtherPermission()->toString()}");
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->loader->getScheduler()->scheduleDelayedRepeatingTask(new ShowChunkBoxTask($player), 20, 20);
	}
}
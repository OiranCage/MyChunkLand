<?php

namespace famima65536\mychunkland\client;

use famima65536\mychunkland\client\task\ShowChunkBoxTask;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\Furnace;
use pocketmine\block\ItemFrame;
use pocketmine\block\Trapdoor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;

class EventListener implements Listener {
	public function __construct(private Loader $loader){
	}

	public function onInteract(PlayerInteractEvent $event){
		switch($event->getAction()){
			case PlayerInteractEvent::RIGHT_CLICK_BLOCK:
				$block = $event->getBlock();

				if($this->matchUpdateBlockList($block)){
					$this->onBlockUpdate($event, $block->getPosition());
				}

				if($this->matchContainerBlockList($block)){
					$this->onOpenOrBreakContainer($event, $block->getPosition());
				}
				break;
		}
	}

	public function matchUpdateBlockList(Block $block): bool{
		$blocks = [
			Door::class,
			Trapdoor::class,
			FenceGate::class,
			ItemFrame::class
		];

		foreach($blocks as $blockClass){
			if($block instanceof $blockClass){
				return true;
			}
		}
		return false;
	}


	private function matchContainerBlockList(Block $block): bool{
		$blocks = [
			Chest::class,
			Furnace::class
		];

		foreach($blocks as $blockClass){
			if($block instanceof $blockClass){
				return true;
			}
		}
		return false;
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$this->onBlockUpdate($event, $event->getBlock()->getPosition());
	}

	/**
	 * @notHandler
	 */
	private function onBlockUpdate(BlockBreakEvent|PlayerInteractEvent $event, Vector3 $position){
		$player = $event->getPlayer();
		$userId = new PlayerUserId($player->getName());
		$coordinate = new ChunkCoordinate($position->getFloorX() >> 4, $position->getFloorZ() >> 4, $player->getWorld()->getFolderName());
		if(!$this->loader->getSectionCache()->hasCache($coordinate)){
			$this->loader->loadAndActionOnSection($coordinate, function(?Section $section) use ($event){
				$event->uncancel();
				$event->call();
			});
			$event->cancel();
			return;
		}

		$section = $this->loader->getSectionCache()->readCache($coordinate);
		if($section === null){
			$player->sendMessage("所有していない土地は編集できません。");
			$event->cancel();
			return;
		}

		if(!$section->getPermissionFor($userId)->isWritable()){
			$player->sendMessage("権限がないため編集できません");
			$event->cancel();
			return;
		}

	}

	/**
	 * @param BlockBreakEvent|PlayerInteractEvent $event
	 * @param Vector3 $position
	 */
	private function onOpenOrBreakContainer(BlockBreakEvent|PlayerInteractEvent $event, Vector3 $position){
		$player = $event->getPlayer();
		$userId = new PlayerUserId($player->getName());
		$coordinate = new ChunkCoordinate($position->getFloorX() >> 4, $position->getFloorZ() >> 4, $player->getWorld()->getFolderName());
		if(!$this->loader->getSectionCache()->hasCache($coordinate)){
			$this->loader->loadAndActionOnSection($coordinate, function(?Section $section) use ($event){
				$event->uncancel();
				$event->call();
			});
			$event->cancel();
			return;
		}

		$section = $this->loader->getSectionCache()->readCache($coordinate);
		if($section === null){
			$player->sendMessage("所有していない土地は編集できません。");
			$event->cancel();
			return;
		}

		if(!$section->getPermissionFor($userId)->isReadable()){
			$player->sendMessage("権限がないため閲覧/破壊できません");
			$event->cancel();
			return;
		}

	}

	public function onPlayerMove(PlayerMoveEvent $event){
		$position = $event->getPlayer()->getPosition();
		$chunkX = $position->getFloorX() >> 4;
		$chunkZ = $position->getFloorZ() >> 4;
		$worldName = $event->getPlayer()->getWorld()->getFolderName();

		$coordinate = new ChunkCoordinate($chunkX, $chunkZ, $worldName);
		if(!$this->loader->getSectionCache()->hasCache($coordinate)){
			$event->getPlayer()->sendTip("uncached chunk");
			return;
		}

		$section = $this->loader->getSectionCache()->readCache($coordinate);
		if($section === null){
			$event->getPlayer()->sendTip("cached chunk: none");
			return;
		}

		$event->getPlayer()->sendTip("cached chunk\nowner {$section->getOwnerId()->getPrefix()}:{$section->getOwnerId()->getName()}\ngperm {$section->getGroupPermission()->toString()}\noperm {$section->getOtherPermission()->toString()}\ntotal-load ?");
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$pk = new GameRulesChangedPacket();
		$pk->gameRules = [
			"showCoordinates" => new BoolGameRule(true, true)
		];
		$player->getNetworkSession()->sendDataPacket($pk);
		$this->loader->getScheduler()->scheduleDelayedRepeatingTask(new ShowChunkBoxTask($player), 20, 20);
	}

	public function onChunkLoad(ChunkLoadEvent $event){
		$coordinate = new ChunkCoordinate($event->getChunkX(), $event->getChunkZ(), $event->getWorld()->getFolderName());
		if(!$this->loader->getSectionCache()->hasCache($coordinate)){
			$this->loader->tryAsyncCacheSection([$coordinate]);
		}
	}

}
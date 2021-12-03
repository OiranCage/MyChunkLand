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
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\ItemFrame;
use pocketmine\block\Trapdoor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\ContainerInventory;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\GameRuleType;
use pocketmine\scheduler\ClosureTask;

class EventListener implements Listener {
	public function __construct(private Loader $loader){
	}

	public function onInventoryOpen(InventoryOpenEvent $event){
		$inventory = $event->getInventory();
		if(!$inventory instanceof ContainerInventory){
			return;
		}

		$this->onInventoryRead($event, $inventory->getHolder());

	}

	public function onInteract(PlayerInteractEvent $event){
		switch($event->getAction()){
			case PlayerInteractEvent::RIGHT_CLICK_BLOCK:
			case PlayerInteractEvent::PHYSICAL:
				$block = $event->getBlock();

				if($this->matchUpdateBlockList($block)){
					$this->onBlockUpdate($event, $block->asPosition());
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

	public function onBlockBreak(BlockBreakEvent $event){
		$this->onBlockUpdate($event, $event->getBlock()->asPosition());
	}

	/**
	 * @notHandler
	 */
	private function onBlockUpdate(BlockBreakEvent|PlayerInteractEvent $event, Vector3 $position){
		$player = $event->getPlayer();
		$userId = new PlayerUserId($player->getName());
		$coordinate = new ChunkCoordinate($position->getFloorX() >> 4, $position->getFloorZ() >> 4, $player->getLevel()->getFolderName());
		if(!$this->loader->hasCachedSection($coordinate)){
			$this->loader->loadAndActionOnSection($coordinate, function(?Section $section) use ($event){
				$event->setCancelled(false);
				$event->call();
			});
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

		$event->getPlayer()->sendTip("cached chunk\nowner {$section->getOwnerId()->getPrefix()}:{$section->getOwnerId()->getName()}\ngperm {$section->getGroupPermission()->toString()}\noperm {$section->getOtherPermission()->toString()}\ntotal-load {$this->loader->countCachedSections()}");
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$pk = new GameRulesChangedPacket();
		$pk->gameRules = [
			"showCoordinates" => [
				GameRuleType::BOOL,
				true,
				true
			]
		];
		$player->sendDataPacket($pk);
		$this->loader->getScheduler()->scheduleDelayedRepeatingTask(new ShowChunkBoxTask($player), 20, 20);
	}

	public function onChunkLoad(ChunkLoadEvent $event){
		$coordinate = new ChunkCoordinate($event->getChunk()->getX(), $event->getChunk()->getZ(), $event->getLevel()->getFolderName());
		if(!$this->loader->hasCachedSection($coordinate)){
			$this->loader->tryAsyncCacheSection([$coordinate]);
		}
	}

	private function onInventoryRead(InventoryOpenEvent $event, Vector3 $position){
		$player = $event->getPlayer();
		$userId = new PlayerUserId($player->getName());
		$coordinate = new ChunkCoordinate($position->getFloorX() >> 4, $position->getFloorZ() >> 4, $player->getLevel()->getFolderName());
		if(!$this->loader->hasCachedSection($coordinate)){
			$this->loader->loadAndActionOnSection($coordinate, function(?Section $section) use ($event){
				$event->setCancelled(false);
				$event->call();
			});
			$event->setCancelled();
			return;
		}

		$section = $this->loader->getCachedSection($coordinate);
		if($section === null){
			$player->sendMessage("所有してされていない土地は編集できません。");
			$event->setCancelled();
			return;
		}

		if(!$section->getPermissionFor($userId)->isReadable()){
			$player->sendMessage("権限がないため編集できません");
			$event->setCancelled();
			return;
		}

	}
}
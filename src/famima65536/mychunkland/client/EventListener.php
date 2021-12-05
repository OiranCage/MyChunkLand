<?php

namespace famima65536\mychunkland\client;

use famima65536\mychunkland\client\task\ShowChunkBoxTask;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use Hoa\Event\Event;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\Furnace;
use pocketmine\block\ItemFrame;
use pocketmine\block\Trapdoor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Cancellable;
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
		$blockPosition = $event->getBlock()->getPosition();
		$player = $event->getPlayer();
		$world = $player->getWorld();
		if($blockPosition->y < SettingManager::getInstance()->getSettingForWorld($world)->protect_min_y_range){
			return;
		}
		$userId = new PlayerUserId($player->getName());
		$coordinate = new ChunkCoordinate($position->getFloorX() >> 4, $position->getFloorZ() >> 4, $world->getFolderName());
		$this->waitForLoading($coordinate, $event);

		$section = $this->loader->getSectionCache()->readCache($coordinate);
		if($section === null and !SettingManager::getInstance()->getSettingForWorld($world)->update_block_without_owning){
			$player->sendPopup(LanguageManager::getInstance()->getLanguageFor($player)->get('event.on-block-update.not-owning'));
			$event->cancel();
			return;
		}

		if(!$section->getPermissionFor($userId)->isWritable()){
			$player->sendPopup(LanguageManager::getInstance()->getLanguageFor($player)->get('event.on-block-update.not-enough-permission'));
			$event->cancel();
			return;
		}

	}

	/**
	 * @notHandler
	 * @param BlockBreakEvent|PlayerInteractEvent $event
	 * @param Vector3 $position
	 */
	private function onOpenOrBreakContainer(BlockBreakEvent|PlayerInteractEvent $event, Vector3 $position){
		$player = $event->getPlayer();
		$userId = new PlayerUserId($player->getName());
		$world = $player->getWorld();
		$blockPosition = $event->getBlock()->getPosition();
		if($blockPosition->y < SettingManager::getInstance()->getSettingForWorld($world)->protect_min_y_range){
			return;
		}
		$coordinate = new ChunkCoordinate($position->getFloorX() >> 4, $position->getFloorZ() >> 4, $world->getFolderName());
		$this->waitForLoading($coordinate, $event);

		$section = $this->loader->getSectionCache()->readCache($coordinate);
		if($section === null and !SettingManager::getInstance()->getSettingForWorld($world)->update_block_without_owning){
			$player->sendPopup(LanguageManager::getInstance()->getLanguageFor($player)->get('event.on-view-block-inventory.not-owning'));
			$event->cancel();
			return;
		}

		if(!$section->getPermissionFor($userId)->isReadable()){
			$player->sendPopup(LanguageManager::getInstance()->getLanguageFor($player)->get('event.on-view-block-inventory.not-enough-permission'));
			$event->cancel();
		}

	}


	public function onChunkLoad(ChunkLoadEvent $event){
		$coordinate = new ChunkCoordinate($event->getChunkX(), $event->getChunkZ(), $event->getWorld()->getFolderName());
		if(!$this->loader->getSectionCache()->hasCache($coordinate)){
			$this->loader->tryAsyncCacheSection([$coordinate]);
		}
	}

	private function waitForLoading(ChunkCoordinate $coordinate, BlockBreakEvent|PlayerInteractEvent $event){
		if(!$this->loader->getSectionCache()->hasCache($coordinate)){
			$this->loader->loadAndActionOnSection($coordinate, function(?Section $section) use ($event){
				$event->uncancel();
				$event->call();
			});
			$event->cancel();
		}
	}

}
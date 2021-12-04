<?php

namespace famima65536\mychunkland\client;

use Closure;
use Exception;
use famima65536\mychunkland\client\command\MyChunkLandCommand;
use famima65536\mychunkland\client\form\FormSession;
use famima65536\mychunkland\client\task\AsyncSectionLoadByOwnerTask;
use famima65536\mychunkland\client\task\AsyncSectionLoadTask;
use famima65536\mychunkland\client\task\AsyncSectionSaveTask;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\UserId;
use mysqli;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase {

	use SingletonTrait;

	/** @var ChunkCoordinate[] $loadingCoordinates */
	private array $loadingCoordinates = [];
	private array $sectionCache = [];
	private array $userCache = [];

	/** @var FormSession[] */
	private array $sessions = [];

	public function onLoad(): void{
		self::setInstance($this);

		$this->saveDefaultConfig();
		$connectionConfig = $this->getConfig()->get("database");
		$sql = file_get_contents($this->getFile()."/resources/initialize.sql");
		$this->getLogger()->info($sql);
		try{
			$connection = new mysqli($connectionConfig["host"], $connectionConfig["username"], $connectionConfig["password"], $connectionConfig["schema"]);
			$connection->query($sql);
		}catch(Exception $ex){
			$this->getLogger()->critical("Error happen when connecting MySQL server: {$ex->getMessage()}");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}

		$this->getServer()->getCommandMap()->register("mychunkland", new MyChunkLandCommand("mychunkland", "MyChunkLand central command","", ["mcl"], $this));
	}

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function tryAsyncCacheSection(array $chunkCoordinates, ?Closure $callback=null){
		$config = $this->getConfig()->get("database");
		$chunkCoordinates = array_filter($chunkCoordinates, function($chunkCoordinate){
			foreach($this->loadingCoordinates as $loading){
				if($loading->equals($chunkCoordinate))
					return false;
			}
			return true;
		});
		$this->getServer()->getAsyncPool()->submitTask(new AsyncSectionLoadTask($chunkCoordinates, $config, $callback));
		foreach($chunkCoordinates as $coordinate){
			$this->getLogger()->notice("Loading Section #{x: {$coordinate->getX()}, z: {$coordinate->getZ()}, world: {$coordinate->getWorldName()}}");
		}
		$this->loadingCoordinates = array_merge($this->loadingCoordinates, $chunkCoordinates);
	}

	public function asyncCacheSectionByOwner(UserId $userId, ?Closure $closure = null){
		$this->getServer()->getAsyncPool()->submitTask(new AsyncSectionLoadByOwnerTask($userId, $this->getConfig()->get("database"), $closure));
	}

	public function asyncSaveSection(Section $section){
		$config = $this->getConfig()->get("database");
		$this->clearCachedSection($section->getCoordinate());
		$this->loadingCoordinates[] = $section->getCoordinate();
		$this->getServer()->getAsyncPool()->submitTask(new AsyncSectionSaveTask($section, $config));
	}

	public function loadAndActionOnSection(ChunkCoordinate $coordinate, Closure $closure): void{
		$isCached = $this->hasCachedSection($coordinate);
		if(!$isCached){
			$dummy_callback = function($sections)use($closure){
				$closure($sections[0][1]);
			};
			$this->tryAsyncCacheSection([$coordinate], $dummy_callback);
			return;
		}
		$section = $this->getCachedSection($coordinate);
		$closure($section);
	}

	/**
	 * @param ChunkCoordinate $coordinate
	 * @param Section|null $section
	 */
	public function cacheSection(ChunkCoordinate $coordinate, ?Section $section){
		if(!isset($this->sectionCache[$coordinate->getWorldName()])){
			$this->sectionCache[$coordinate->getWorldName()] = [];
		}
		$this->sectionCache[$coordinate->getWorldName()][$coordinate->hash()] = $section;
		$this->getLogger()->notice("Cached Section #{x: {$coordinate->getX()}, z: {$coordinate->getZ()}, world: {$coordinate->getWorldName()}}");

		$this->loadingCoordinates = array_filter($this->loadingCoordinates, function($chunkCoordinate)use($coordinate){
			return !$coordinate->equals($chunkCoordinate);
		});
	}

	public function getCachedSection(ChunkCoordinate $coordinate): ?Section{
		if(!$this->hasCachedSection($coordinate)){
			return null;
		}

		return $this->sectionCache[$coordinate->getWorldName()][$coordinate->hash()];
	}

	public function hasCachedSection(ChunkCoordinate $coordinate): bool{
		return (
			isset($this->sectionCache[$coordinate->getWorldName()]) and
			array_key_exists($coordinate->hash(), $this->sectionCache[$coordinate->getWorldName()])
		);
	}

	public function clearCachedSection(ChunkCoordinate $coordinate){
		if($this->hasCachedSection($coordinate)){
			unset($this->sectionCache[$coordinate->getWorldName()][$coordinate->hash()]);
			$this->getLogger()->notice("Unload Cached Section #{x: {$coordinate->getX()}, z: {$coordinate->getZ()}, world: {$coordinate->getWorldName()}}");
		}
	}

	public function countCachedSections(): int{
		$count = 0;
		foreach($this->sectionCache as $sectionCachePerWorld){
			$count += count($sectionCachePerWorld);
		}
		return $count;
	}

	public function startFormSession(Player $player): void{
		$this->sessions[$player->getName()] = new FormSession($player);
	}

	public function getFormSession(Player $player): ?FormSession{
		return $this->sessions[$player->getName()] ?? null;
	}

}
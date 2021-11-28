<?php

namespace famima65536\mychunkland\client;

use Exception;
use famima65536\mychunkland\client\task\AsyncSectionLoadTask;
use famima65536\mychunkland\client\task\AsyncSectionOwnTask;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
use mysqli;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase {

	use SingletonTrait;

	/** @var ChunkCoordinate[] $loadingCoordinates */
	private array $loadingCoordinates = [];
	private array $sectionCache = [];
	private array $userCache = [];

	public function onLoad(){
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
	}

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function tryAsyncCacheSection(array $chunkCoordinates){
		$config = $this->getConfig()->get("database");
		$chunkCoordinates = array_filter($chunkCoordinates, function($chunkCoordinate){
			foreach($this->loadingCoordinates as $loading){
				if($loading->equals($chunkCoordinate))
					return false;
			}
			return true;
		});
		$this->getServer()->getAsyncPool()->submitTask(new AsyncSectionLoadTask($chunkCoordinates, $config));
		foreach($chunkCoordinates as $coordinate){
			$this->getLogger()->notice("Loading Section #{x: {$coordinate->getX()}, z: {$coordinate->getZ()}, world: {$coordinate->getWorldName()}}");
		}
		$this->loadingCoordinates = array_merge($this->loadingCoordinates, $chunkCoordinates);
	}

	public function asyncOwnSection(Section $section){
		$config = $this->getConfig()->get("database");
		$this->clearCachedSection($section->getCoordinate());
		$this->loadingCoordinates[] = $section->getCoordinate();
		$this->getServer()->getAsyncPool()->submitTask(new AsyncSectionOwnTask($section, $config));
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
}
<?php

namespace famima65536\mychunkland\client;

use famima65536\mychunkland\client\task\AsyncSectionLoadTask;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
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
	}

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->saveDefaultConfig();
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
		$this->loadingCoordinates = array_merge($this->loadingCoordinates, $chunkCoordinates);
	}

	/**
	 * @param Section[] $sections
	 */
	public function cacheSections(array $sections){
		foreach($sections as $section){
			if(!isset($this->sectionCache[$section->getCoordinate()->getWorldName()])){
				$this->sectionCache[$section->getCoordinate()->getWorldName()] = [];
			}
			$this->sectionCache[$section->getCoordinate()->getWorldName()][$section->getCoordinate()->hash()] = $section;
		}

		$this->loadingCoordinates = array_filter($this->loadingCoordinates, function($chunkCoordinate)use($sections){
			foreach($sections as $section){
				if($section->getCoordinate()->equals($chunkCoordinate))
					return false;
			}
			return true;
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
		}
	}
}
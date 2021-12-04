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
use Webmozart\PathUtil\Path;

class Loader extends PluginBase {

	use SingletonTrait;

	private SectionCache $sectionCache;

	/** @var FormSession[] */
	private array $sessions = [];

	public function onLoad(): void{
		self::setInstance($this);
		$this->saveDefaultConfig();
		$connectionConfig = $this->getConfig()->get("database");
		$sql = file_get_contents(Path::join($this->getFile(), "resources", "initialize.sql"));
		$this->getLogger()->info($sql);
		try{
			$connection = new mysqli($connectionConfig["host"], $connectionConfig["username"], $connectionConfig["password"], $connectionConfig["schema"]);
			$connection->query($sql);
		}catch(Exception $ex){
			$this->getLogger()->critical("Error happen when connecting MySQL server: {$ex->getMessage()}");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}

		LanguageManager::setInstance(new LanguageManager(Path::join($this->getFile(), "resources", "lang")));
		$this->sectionCache = new SectionCache();
		$this->getServer()->getCommandMap()->register("mychunkland", new MyChunkLandCommand("mychunkland", "MyChunkLand central command","", ["mcl"], $this));
	}

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function tryAsyncCacheSection(array $chunkCoordinates, ?Closure $callback=null){
		$config = $this->getConfig()->get("database");
		$this->getServer()->getAsyncPool()->submitTask(new AsyncSectionLoadTask($chunkCoordinates, $config, $callback));
		foreach($chunkCoordinates as $coordinate){
			$this->getLogger()->debug("Loading Section #{x: {$coordinate->getX()}, z: {$coordinate->getZ()}, world: {$coordinate->getWorldName()}}");
		}
	}

	public function asyncCacheSectionByOwner(UserId $userId, ?Closure $closure = null){
		$this->getServer()->getAsyncPool()->submitTask(new AsyncSectionLoadByOwnerTask($userId, $this->getConfig()->get("database"), $closure));
	}

	public function asyncSaveSection(Section $section){
		$config = $this->getConfig()->get("database");
		$this->sectionCache->writeCache($section, true);
		$this->getServer()->getAsyncPool()->submitTask(new AsyncSectionSaveTask($section, $config));
	}

	public function loadAndActionOnSection(ChunkCoordinate $coordinate, Closure $closure): void{
		$isCached = $this->sectionCache->hasCache($coordinate);
		if(!$isCached){
			$dummy_callback = function($sections)use($closure){
				$closure($sections[0][1]);
			};
			$this->tryAsyncCacheSection([$coordinate], $dummy_callback);
			return;
		}
		$section = $this->sectionCache->readCache($coordinate);
		$closure($section);
	}

	public function startFormSession(Player $player): void{
		$this->sessions[$player->getName()] = new FormSession($player, LanguageManager::getInstance()->getLanguageFor($player));
	}

	public function getFormSession(Player $player): ?FormSession{
		return $this->sessions[$player->getName()] ?? null;
	}

	public function getSectionCache(): SectionCache{
		return $this->sectionCache;
	}

}
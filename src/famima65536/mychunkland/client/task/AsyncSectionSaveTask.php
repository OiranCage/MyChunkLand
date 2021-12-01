<?php

namespace famima65536\mychunkland\client\task;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\repository\MySQLSectionRepository;
use mysqli;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncSectionSaveTask extends AsyncTask {

	private string $serializedSection;

	public function __construct(Section $section, private array $connectionConfig){
		$this->serializedSection = serialize($section);
	}

	public function onRun(){
		$connectionConfig = $this->connectionConfig;
		$section = unserialize($this->serializedSection);
		$sectionRepository = new MySQLSectionRepository(new mysqli($connectionConfig["host"], $connectionConfig["username"], $connectionConfig["password"], $connectionConfig["schema"]));
		$sectionRepository->save($section);
	}

	public function onCompletion(Server $server){
		$section = unserialize($this->serializedSection);
		$loader = Loader::getInstance();
		/** @var Section $section */
		$loader->cacheSection($section->getCoordinate(), $section);
	}
}
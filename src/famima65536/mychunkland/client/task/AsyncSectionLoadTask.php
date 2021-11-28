<?php

namespace famima65536\mychunkland\client\task;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\repository\MySQLSectionRepository;
use mysqli;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncSectionLoadTask extends AsyncTask {

	private string $serializedChunkCoordinates;
	/**
	 * @param ChunkCoordinate[] $chunkCoordinates
	 * @param array $connectionConfig
	 */
	public function __construct(array $chunkCoordinates, private array $connectionConfig){
		$this->serializedChunkCoordinates = serialize($chunkCoordinates);
	}

	public function onRun(){
		$connectionConfig = $this->connectionConfig;
		$chunkCoordinates = unserialize($this->serializedChunkCoordinates);
		$sectionRepository = new MySQLSectionRepository(new mysqli($connectionConfig["host"], $connectionConfig["username"], $connectionConfig["password"], $connectionConfig["database"]));
		$sections = [];
		foreach($chunkCoordinates as $chunkCoordinate){
			/** @var ChunkCoordinate $chunkCoordinate */
			$sections[] = $sectionRepository->findByCoordinate($chunkCoordinate);
		}

		$this->setResult($sections);
	}

	public function onCompletion(Server $server){
		$sections = $this->getResult();

		/** @var Section[] $sections */
		Loader::getInstance()->cacheSections($sections);
	}
}
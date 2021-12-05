<?php

namespace famima65536\mychunkland\client\task;

use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\repository\MySQLSectionRepository;
use mysqli;
use pocketmine\scheduler\AsyncTask;

class AsyncSectionDeleteTask extends AsyncTask {
	private string $serializedCoordinate;

	public function __construct(ChunkCoordinate $coordinate, private array $connectionConfig){
		$this->serializedCoordinate = serialize($coordinate);
	}

	public function onRun(): void{
		$connectionConfig = $this->connectionConfig;
		$coordinate = unserialize($this->serializedCoordinate);
		$sectionRepository = new MySQLSectionRepository(new mysqli($connectionConfig["host"], $connectionConfig["username"], $connectionConfig["password"], $connectionConfig["schema"]));
		$sectionRepository->delete($coordinate);
	}
}
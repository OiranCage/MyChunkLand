<?php

namespace famima65536\mychunkland\client\task;

use Closure;
use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\repository\MySQLSectionRepository;
use mysqli;
use pocketmine\scheduler\AsyncTask;

class AsyncSectionLoadTask extends AsyncTask {

	private string $serializedChunkCoordinates;
	/**
	 * @param ChunkCoordinate[] $chunkCoordinates
	 * @param array $connectionConfig
	 */
	public function __construct(array $chunkCoordinates, private array $connectionConfig, private ?Closure $callback=null){
		$this->serializedChunkCoordinates = serialize($chunkCoordinates);
	}

	public function onRun(): void{
		$connectionConfig = $this->connectionConfig;
		$chunkCoordinates = unserialize($this->serializedChunkCoordinates);
		$sectionRepository = new MySQLSectionRepository(new mysqli($connectionConfig["host"], $connectionConfig["username"], $connectionConfig["password"], $connectionConfig["schema"]));
		$sections = [];
		foreach($chunkCoordinates as $chunkCoordinate){
			/** @var ChunkCoordinate $chunkCoordinate */
			$sections[] = [$chunkCoordinate, $sectionRepository->findByCoordinate($chunkCoordinate)];
		}

		$this->setResult($sections);
	}

	public function onCompletion(): void{
		$sections = $this->getResult();
		$sectionCache = Loader::getInstance()->getSectionCache();
		foreach($sections as $sectionData){
			if($sectionData[1] !== null){
				$sectionCache->writeCache($sectionData[1], false);
			}else{
				$sectionCache->writeNullCache($sectionData[0], false);
			}
		}

		if($this->callback !== null){
			($this->callback)($sections);
		}
	}
}
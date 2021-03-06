<?php

namespace famima65536\mychunkland\client\task;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\repository\MySQLSectionRepository;
use mysqli;
use pocketmine\scheduler\AsyncTask;

class AsyncSectionSaveTask extends AsyncTask {

	private string $serializedSection;

	public function __construct(Section $section, private array $connectionConfig){
		$this->serializedSection = serialize($section);
	}

	public function onRun(): void{
		$connectionConfig = $this->connectionConfig;
		$section = unserialize($this->serializedSection);
		$sectionRepository = new MySQLSectionRepository(new mysqli($connectionConfig["host"], $connectionConfig["username"], $connectionConfig["password"], $connectionConfig["schema"]));
		$sectionRepository->save($section);
	}

	public function onCompletion(): void{
	}
}
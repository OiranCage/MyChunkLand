<?php

namespace famima65536\mychunkland\client\task;

use Closure;
use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\UserId;
use famima65536\mychunkland\system\repository\MySQLSectionRepository;
use mysqli;

class AsyncSectionLoadByOwnerTask extends \pocketmine\scheduler\AsyncTask {

	private string $serializedUserId;

	public function __construct(UserId $userId, private array $connectionConfig, private ?Closure $callback = null){
		$this->serializedUserId = serialize($userId);
	}

	public function onRun(): void{
		$userId = unserialize($this->serializedUserId);
		$connectionConfig = $this->connectionConfig;
		$sectionRepository = new MySQLSectionRepository(new mysqli($connectionConfig["host"], $connectionConfig["username"], $connectionConfig["password"], $connectionConfig["schema"]));
		$sections = $sectionRepository->findByOwner($userId);
		$this->setResult($sections);
	}

	public function onCompletion(): void{
		$sections = $this->getResult();
		$sectionCache = Loader::getInstance()->getSectionCache();
		/** @var Section[] $sections */
		foreach($sections as $section){
			$sectionCache->writeCache($section, false);
		}

		if($this->callback !== null){
			($this->callback)($sections);
		}
	}
}
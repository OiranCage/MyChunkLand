<?php

namespace famima65536\mychunkland\client\command;

use famima65536\mychunkland\client\form\CentralForm;
use famima65536\mychunkland\client\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class MyChunkLandCommand extends Command {

	public function __construct(string $name, string $description, string $usageMessage, array $aliases, private Loader $loader){
		parent::__construct($name, $description, $usageMessage, $aliases);
	}

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player and count($args) === 0){
			$this->loader->startFormSession($sender);
			$this->loader->getFormSession($sender)->open(new CentralForm());
		}
	}

}
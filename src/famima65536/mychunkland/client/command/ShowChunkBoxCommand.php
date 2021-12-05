<?php

namespace famima65536\mychunkland\client\command;

use famima65536\mychunkland\client\feature\ChunkBox;
use famima65536\mychunkland\client\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\player\Player;

class ShowChunkBoxCommand extends Command {

	/**
	 * @var ChunkBox[]
	 */
	private array $chunkBoxesForPlayer = [];

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player){
			$sender->sendMessage("Command is only available in server.");
			return;
		}
		if(isset($this->chunkBoxesForPlayer[$sender->getName()])){
			$this->chunkBoxesForPlayer[$sender->getName()]->hide($sender);
		}
		$chunkBox = new ChunkBox($sender->getPosition());
		$chunkBox->show($sender);
		$this->chunkBoxesForPlayer[$sender->getName()] = $chunkBox;
	}
}
<?php

namespace famima65536\mychunkland\client\task;

use famima65536\mychunkland\client\feature\ChunkBox;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class ShowChunkBoxTask extends Task {

	private ChunkBox $chunkBox;

	/**
	 * @param Player $player
	 */
	public function __construct(private Player $player){
		$this->chunkBox = new ChunkBox($player->getPosition());
		$this->chunkBox->show($this->player);
	}

	public function onRun(int $currentTick){
		if(!$this->player->isConnected()){
			$this->getHandler()->cancel();
			return;
		}

		$position = $this->player->getPosition();

		if(!$this->chunkBox->isInRange($position)){
			$this->chunkBox->hide($this->player);
			$this->chunkBox = new ChunkBox($position);
			$this->chunkBox->show($this->player);
		}
	}
}
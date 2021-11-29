<?php

namespace famima65536\mychunkland\client\feature;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\level\Position;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\StructureEditorData;
use pocketmine\Player;

class ChunkBox {

	private int $chunkX;
	private int $chunkY;
	private int $chunkZ;

	private Position $structureBlockPosition;

	public function __construct(Position $position){
		$this->chunkX = ($position->getFloorX() >> 4);
		$this->chunkY = ($position->getFloorY() >> 4);
		$this->chunkZ = ($position->getFloorZ() >> 4);
		$this->structureBlockPosition = new Position($this->chunkX << 4, $this->chunkY << 4, $this->chunkZ << 4);
	}

	public function show(Player $player){
		$world = $player->getPosition()->getLevelNonNull();
		$block = BlockFactory::get(BlockIds::STRUCTURE_BLOCK, 0, $this->structureBlockPosition);
		$world->sendBlocks([$player], [$block]);

		$pk = new BlockActorDataPacket();

		$pk->x = $this->structureBlockPosition->x;
		$pk->y = $this->structureBlockPosition->y;
		$pk->z = $this->structureBlockPosition->z;

		$tag = new CompoundTag();
		$tag->setInt("data", 5);
		$tag->setString("dataField", "");
		$tag->setByte("ignoreEntities", 1);
		$tag->setByte("includePlayers", 0);
		$tag->setFloat("integrity", 1.0);
		$tag->setByte("isMovable", 1);
		$tag->setByte("isPowered", 0);
		$tag->setByte("mirror", 0);
		$tag->setByte("removeBlocks", 1);
		$tag->setByte("rotation", 0);
		$tag->setLong("seed", 0);
		$tag->setByte("showBoundingBox", 1);
		$tag->setString("structureName", "ChunkBox");
		$tag->setInt("x", $block->x);
		$tag->setInt("xStructureOffset", 0);
		$tag->setInt("xStructureSize", 16);
		$tag->setInt("y", $block->y);
		$tag->setInt("yStructureOffset", 0);
		$tag->setInt("yStructureSize", 16);
		$tag->setInt("z", $block->z);
		$tag->setInt("zStructureOffset", 0);
		$tag->setInt("zStructureSize", 16);

		$pk->namedtag = (new NetworkLittleEndianNBTStream())->write($tag);

		$player->sendDataPacket($pk);

	}


	public function hide(Player $player){
		$world = $player->getPosition()->getLevelNonNull();
		$world->sendBlocks([$player], [$this->structureBlockPosition]);
	}

	public function isInRange(Position $position): bool{
		return (
			$position->getFloorX() >> 4 === $this->chunkX and
			$position->getFloorY() >> 4 === $this->chunkY and
			$position->getFloorZ() >> 4 === $this->chunkZ
		);
	}
}
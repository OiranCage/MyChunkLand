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
		$block = BlockFactory::get(BlockIds::STRUCTURE_BLOCK, 0);

		$tag = new CompoundTag();
		$tag->setInt("data", 1);
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
		$tag->setInt("y", 0);
		$tag->setInt("yStructureOffset", $this->structureBlockPosition->y);
		$tag->setInt("yStructureSize", 16);


		$position = new Position(0,0,0);
		$position->y = 0;
		$pk = new BlockActorDataPacket();
		$pk->y = 0;

		for($i = 0; $i < 5; $i++){
			$position->x = $this->structureBlockPosition->x + 2 + 2*$i;
			$position->z = $this->structureBlockPosition->z;
			$block->position($position);
			$world->sendBlocks([$player], [$block]);

			$pk->x = $block->x;
			$pk->z = $block->z;
			$tag->setInt("xStructureSize", 16 - 4*$i);
			$tag->setInt("zStructureSize", 16);
			$tag->setInt("xStructureOffset", -2);
			$tag->setInt("zStructureOffset", 0);
			$tag->setInt("x", $block->x);
			$tag->setInt("z", $block->z);

			$pk->namedtag = (new NetworkLittleEndianNBTStream())->write($tag);
			$player->sendDataPacket(clone $pk);

			$position->x = $this->structureBlockPosition->x;
			$position->z = $this->structureBlockPosition->z + 2 + 2*$i;
			$block->position($position);
			$world->sendBlocks([$player], [$block]);

			$pk->x = $block->x;
			$pk->z = $block->z;
			$tag->setInt("xStructureSize", 16);
			$tag->setInt("zStructureSize", 16 - 4*$i);
			$tag->setInt("xStructureOffset", 0);
			$tag->setInt("zStructureOffset", -2);
			$tag->setInt("x", $block->x);
			$tag->setInt("z", $block->z);

			$pk->namedtag = (new NetworkLittleEndianNBTStream())->write($tag);
			$player->sendDataPacket(clone $pk);
		}


	}


	public function hide(Player $player){
		$world = $player->getPosition()->getLevelNonNull();
		$position = new Position(0,0,0);
		for($i = 0; $i < 5; $i++){
			$position->x = $this->structureBlockPosition->x + 2 + 2*$i;
			$position->z = $this->structureBlockPosition->z;
			$world->sendBlocks([$player], [$position]);
			$position->x = $this->structureBlockPosition->x;
			$position->z = $this->structureBlockPosition->z + 2 + 2*$i;
			$world->sendBlocks([$player], [$position]);
		}
	}

	public function isInRange(Position $position): bool{
		return (
			$position->getFloorX() >> 4 === $this->chunkX and
			$position->getFloorY() >> 4 === $this->chunkY and
			$position->getFloorZ() >> 4 === $this->chunkZ
		);
	}
}
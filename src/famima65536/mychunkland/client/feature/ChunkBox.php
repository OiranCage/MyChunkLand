<?php

namespace famima65536\mychunkland\client\feature;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;

class ChunkBox {

	private int $chunkX;
	private int $chunkY;
	private int $chunkZ;

	private Position $structureBlockPosition;
	private int $runtimeId;

	public function __construct(Position $position){
		$this->chunkX = ($position->getFloorX() >> 4);
		$this->chunkY = ($position->getFloorY() >> 4);
		$this->chunkZ = ($position->getFloorZ() >> 4);
		$this->runtimeId = RuntimeBlockMapping::getInstance()->toRuntimeId(BlockFactory::getInstance()->get(BlockLegacyIds::STRUCTURE_BLOCK, 0)->getFullId());

		$this->structureBlockPosition = new Position($this->chunkX << 4, $this->chunkY << 4, $this->chunkZ << 4, null);
	}

	public function show(Player $player){
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


		$position = new Position(0,0,0, null);
		$position->y = 0;
		$pk = new BlockActorDataPacket();
		$packets = [];
		$updateBlockPackets = [];
		for($i = 0; $i < 5; $i++){
			$position->x = $this->structureBlockPosition->x + 2 + 2*$i;
			$position->z = $this->structureBlockPosition->z;
			$pk->blockPosition = BlockPosition::fromVector3($position);
			$updateBlockPackets[] = UpdateBlockPacket::create($pk->blockPosition, $this->runtimeId, 2, 0);

			$tag->setInt("xStructureSize", 16 - 4*$i);
			$tag->setInt("zStructureSize", 16);
			$tag->setInt("xStructureOffset", -2);
			$tag->setInt("zStructureOffset", 0);
			$tag->setInt("x", $position->x);
			$tag->setInt("z", $position->z);
			$pk->nbt = new CacheableNbt($tag->safeClone());
			$packets[] = clone $pk;

			$position->x = $this->structureBlockPosition->x;
			$position->z = $this->structureBlockPosition->z + 2 + 2*$i;
			$pk->blockPosition = BlockPosition::fromVector3($position);
			$updateBlockPackets[] = UpdateBlockPacket::create($pk->blockPosition, $this->runtimeId, 2, 0);

			$tag->setInt("xStructureSize", 16);
			$tag->setInt("zStructureSize", 16 - 4*$i);
			$tag->setInt("xStructureOffset", 0);
			$tag->setInt("zStructureOffset", -2);
			$tag->setInt("x", $position->x);
			$tag->setInt("z", $position->z);
			$pk->nbt = new CacheableNbt($tag->safeClone());
			$packets[] = clone $pk;
		}

		foreach($updateBlockPackets as $pk){
			$player->getNetworkSession()->sendDataPacket($pk);
		}

		foreach($packets as $pk){
			$player->getNetworkSession()->sendDataPacket($pk);
		}



	}


	public function hide(Player $player){
		$world = $player->getWorld();
		$position = new Position(0,0,0, null);
		$updateBlocks = [];
		for($i = 0; $i < 5; $i++){
			$position->x = $this->structureBlockPosition->x + 2 + 2*$i;
			$position->z = $this->structureBlockPosition->z;
			$updateBlocks[] = clone $position;
			$position->x = $this->structureBlockPosition->x;
			$position->z = $this->structureBlockPosition->z + 2 + 2*$i;
			$updateBlocks[] = clone $position;
		}

		foreach($world->createBlockUpdatePackets($updateBlocks) as $pk){
			$player->getNetworkSession()->sendDataPacket($pk);
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
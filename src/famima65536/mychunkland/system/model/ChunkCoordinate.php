<?php declare(strict_types=1);

namespace famima65536\mychunkland\system\model;

/** This is value-type class */
class ChunkCoordinate {
	public function __construct(
		private int $x,
		private int $z,
		private string $worldName
	){
	}

	/**
	 * @return int
	 */
	public function getX(): int{
		return $this->x;
	}

	/**
	 * @return int
	 */
	public function getZ(): int{
		return $this->z;
	}

	/**
	 * @return string
	 */
	public function getWorldName(): string{
		return $this->worldName;
	}

	public function equals(ChunkCoordinate $other): bool{
		return (
			$this->x === $other->x &&
			$this->z === $other->z &&
			$this->worldName === $other->worldName
		);
	}
}
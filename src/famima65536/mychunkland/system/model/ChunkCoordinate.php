<?php

namespace famima65536\mychunkland\system\model;

/** This is value-type class */
class ChunkCoordinate {
	public function __construct(
		private int $x,
		private int $z
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
}
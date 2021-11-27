<?php

namespace famima65536\mychunkland\system\model;

class User {
	/**
	 * @param UserId $id
	 * @param ChunkCoordinate[] $ownedSectionIds
	 * @param ChunkCoordinate[] $sharedSectionIds
	 */
	public function __construct(
		private UserId $id,
		private array $ownedSectionIds,
		private array $sharedSectionIds
	){
	}

	/**
	 * @return UserId
	 */
	public function getId(): UserId{
		return $this->id;
	}

	/**
	 * @return ChunkCoordinate[]
	 */
	public function getOwnedSectionIds(): array{
		return $this->ownedSectionIds;
	}

	/**
	 * @param ChunkCoordinate[] $ownedSectionIds
	 */
	public function setOwnedSectionIds(array $ownedSectionIds): void{
		$this->ownedSectionIds = $ownedSectionIds;
	}

	/**
	 * @return ChunkCoordinate[]
	 */
	public function getSharedSectionIds(): array{
		return $this->sharedSectionIds;
	}

	/**
	 * @param ChunkCoordinate[] $sharedSectionIds
	 */
	public function setSharedSectionIds(array $sharedSectionIds): void{
		$this->sharedSectionIds = $sharedSectionIds;
	}
}
<?php

namespace famima65536\mychunkland\system\model;

/**
 * Immutable Entity-type class
 */
class User {
	/**
	 * @param UserId $id
	 * @param ChunkCoordinate[] $owningSectionIds
	 * @param ChunkCoordinate[] $sharedSectionIds
	 */
	public function __construct(
		private UserId $id,
		private array $owningSectionIds,
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
	public function getOwningSectionIds(): array{
		return $this->owningSectionIds;
	}


	/**
	 * @return ChunkCoordinate[]
	 */
	public function getSharedSectionIds(): array{
		return $this->sharedSectionIds;
	}

	public function equals(User $user): bool{
		return $user->id->equals($this->id);
	}

}
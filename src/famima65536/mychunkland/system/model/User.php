<?php

namespace famima65536\mychunkland\system\model;

class User {
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
	 * @return array
	 */
	public function getOwnedSectionIds(): array{
		return $this->ownedSectionIds;
	}

	/**
	 * @param array $ownedSectionIds
	 */
	public function setOwnedSectionIds(array $ownedSectionIds): void{
		$this->ownedSectionIds = $ownedSectionIds;
	}

	/**
	 * @return array
	 */
	public function getSharedSectionIds(): array{
		return $this->sharedSectionIds;
	}

	/**
	 * @param array $sharedSectionIds
	 */
	public function setSharedSectionIds(array $sharedSectionIds): void{
		$this->sharedSectionIds = $sharedSectionIds;
	}
}
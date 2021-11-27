<?php

namespace famima65536\mychunkland\system\model;

/**
 * Mutable Entity-type class
 */
class Section {
	public function __construct(
		private ChunkCoordinate $coordinate,
		private UserId $ownerId,
		private ShareGroup $shareGroup,
		private AccessPermission $groupPermission,
		private AccessPermission $otherPermission
	){
	}

	/**
	 * @return ChunkCoordinate
	 */
	public function getCoordinate(): ChunkCoordinate{
		return $this->coordinate;
	}

	/**
	 * @return UserId
	 */
	public function getOwnerId(): UserId{
		return $this->ownerId;
	}

	/**
	 * @return ShareGroup
	 */
	public function getShareGroup(): ShareGroup{
		return $this->shareGroup;
	}

	/**
	 * @param ShareGroup $shareGroup
	 */
	public function setShareGroup(ShareGroup $shareGroup): void{
		$this->shareGroup = $shareGroup;
	}

	public function getGroupPermission(): AccessPermission{
		return $this->groupPermission;
	}

	public function getOtherPermission(): AccessPermission{
		return $this->otherPermission;
	}

	public function getPermissionFor(UserId $userId): AccessPermission{
		if($userId === $this->ownerId){
			return new AccessPermission(true, true, true); // Dummy Permission
		}

		if($this->shareGroup->contains($userId)){
			return $this->groupPermission;
		}

		return $this->otherPermission;
	}
}
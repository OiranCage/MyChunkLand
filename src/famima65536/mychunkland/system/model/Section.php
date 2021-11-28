<?php

namespace famima65536\mychunkland\system\model;

/**
 * Immutable Entity-type class
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
	public function shareGroupUpdated(ShareGroup $shareGroup): self{
		return new self($this->coordinate, $this->ownerId, $shareGroup, $this->groupPermission, $this->otherPermission);
	}

	public function getGroupPermission(): AccessPermission{
		return $this->groupPermission;
	}

	public function groupPermissionUpdated(AccessPermission $permission): self{
		return new self($this->coordinate, $this->ownerId, $this->shareGroup, $permission, $this->otherPermission);

	}

	public function getOtherPermission(): AccessPermission{
		return $this->otherPermission;
	}

	public function otherPermissionUpdated(AccessPermission $permission): self{
		return new self($this->coordinate, $this->ownerId, $this->shareGroup, $this->groupPermission, $permission);
	}

	public function getPermissionFor(UserId $userId): AccessPermission{
		if($userId->equals($this->ownerId)){
			return new AccessPermission(true, true, true); // Dummy Permission
		}

		if($this->shareGroup->contains($userId)){
			return $this->groupPermission;
		}

		return $this->otherPermission;
	}
}
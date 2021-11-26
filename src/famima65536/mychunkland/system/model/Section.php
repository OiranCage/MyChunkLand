<?php

namespace famima65536\mychunkland\system\model;

class Section {
	public function __construct(
		private ChunkCoordinate $coordinate,
		private UserId $ownerId,
		private ShareGroup $shareGroup,
		private AccessPermission $permission
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

	/**
	 * @return AccessPermission
	 */
	public function getPermission(): AccessPermission{
		return $this->permission;
	}

	public function isEditable(User $user): bool{
		// TODO: suitable logic to judge;
		return false;
	}
}
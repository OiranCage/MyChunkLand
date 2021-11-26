<?php

namespace famima65536\mychunkland\system\model;

use InvalidArgumentException;

/**
 * Immutable Value-type class
 */
class ShareGroup {
	/**
	 * @param UserId[] $userIds
	 */
	public function __construct(private array $userIds){
	}

	public function contains(UserId $userId): bool{
		foreach($this->userIds as $sharedUserId){
			if($userId->equals($sharedUserId)){
				return true;
			}
		}
		return false;
	}

	/**
	 * @param UserId $userId
	 * @throws InvalidArgumentException when user id has already been in group
	 */
	public function add(UserId $userId): self{
		if($this->contains($userId)){
			throw new InvalidArgumentException("given user id has already been in group.");
		}
		return new ShareGroup(array_merge($this->userIds, [$userId]));
	}

	/**
	 * @param UserId $userId
	 * @throws InvalidArgumentException when user id has already been in group
	 */
	public function delete(UserId $userId): self{
		if(!$this->contains($userId)){
			throw new InvalidArgumentException("given user id is not in group.");
		}

		$userIds = array_filter($this->userIds, fn(UserId $id) => $userId->equals($id));
		return new ShareGroup($userIds);
	}
}
<?php

namespace famima65536\mychunkland\system\model;

use InvalidArgumentException;

/**
 * Immutable Value-type class
 */
class ShareGroup implements \JsonSerializable {
	/**
	 * @param UserId[] $userIds
	 */
	public function __construct(private array $userIds){
	}

	/**
	 * @return UserId[]
	 */
	public function getUserIds(): array{
		return $this->userIds;
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

	public function jsonSerialize(): array{
		return array_map(fn(UserId $userId) => ["prefix" => $userId->getPrefix(), "name" => $userId->getName()], $this->userIds);
	}

	public static function deserializeFromJson(array $json): self{
		$userIds = array_map(fn(array $userIdData) => new UserId($userIdData["prefix"], $userIdData["name"]), $json);
		return new self($userIds);
	}
}
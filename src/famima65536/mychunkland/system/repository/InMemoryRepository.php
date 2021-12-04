<?php

namespace famima65536\mychunkland\system\repository;

use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\User;
use famima65536\mychunkland\system\model\UserId;
use pocketmine\level\Level;

/**
 * for test, In memory Section Repository
 */
class InMemoryRepository implements ISectionRepository, IUserRepository {

	/**
	 * @var array<string, array<int, Section>>
	 */
	private array $sections = [];

	public function __construct(){
	}

	/**
	 * @inheritDoc
	 */
	public function findByCoordinate(ChunkCoordinate $coordinate): ?Section{
		if(!isset($this->sections[$coordinate->getWorldName()])){
			return null;
		}

		return clone $this->sections[$coordinate->getWorldName()][$coordinate->hash()] ?? null;
	}

	/**
	 * @inheritDoc
	 */
	public function save(Section $section): void{
		$coordinate = $section->getCoordinate();
		$this->sections[$coordinate->getWorldName()][$coordinate->hash()] = $section;
	}

	/**
	 * @inheritDoc
	 */
	public function findByOwner(UserId $userId): array{
		$result = [];
		foreach($this->sections as $sectionsByWorld){
			foreach($sectionsByWorld as $section){
				if($section->getOwnerId()->equals($userId)){
					$result[] = clone $section;
				}
			}
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function findByShared(UserId $userId): array{
		$result = [];
		foreach($this->sections as $sectionsByWorld){
			foreach($sectionsByWorld as $section){
				if($section->getShareGroup()->contains($userId)){
					$result[] = clone $section;
				}
			}
		}
		return $result;
	}

	public function find(UserId $userId): User{
		$owning = [];
		$shared = [];
		foreach($this->sections as $sectionsByWorld){
			foreach($sectionsByWorld as $section){
				if($section->getOwnerId()->equals($userId)){
					$owning[] = $section->getCoordinate();
				}
				if($section->getShareGroup()->contains($userId)){
					$shared[] = $section->getCoordinate();
				}
			}
		}

		return new User($userId, $owning, $shared);
	}
}
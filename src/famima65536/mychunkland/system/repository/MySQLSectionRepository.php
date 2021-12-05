<?php

namespace famima65536\mychunkland\system\repository;

use famima65536\mychunkland\system\model\AccessPermission;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use famima65536\mychunkland\system\model\UserId;
use mysqli;

class MySQLSectionRepository implements ISectionRepository {

	public function __construct(private mysqli $connection){
	}

	/**
	 * @inheritDoc
	 */
	public function findByCoordinate(ChunkCoordinate $coordinate): ?Section{
		$stmt = $this->connection->prepare("SELECT owner_name, owner_prefix, group_permission, other_permission, share_group FROM section WHERE x=? AND z=? AND world_name=?");
		$stmt->bind_param('iis', $x, $z, $world_name);
		$x = $coordinate->getX();
		$z = $coordinate->getZ();
		$world_name = $coordinate->getWorldName();
		$stmt->execute();

		$stmt->bind_result($owner_name, $owner_prefix, $group_permission, $other_permission, $share_group);
		if(!$stmt->fetch()){
			return null;
		}

		return new Section($coordinate, new UserId($owner_prefix, $owner_name), ShareGroup::deserializeFromJson(json_decode($share_group, true)), AccessPermission::fromBinary($group_permission), AccessPermission::fromBinary($other_permission));
	}

	/**
	 * @inheritDoc
	 */
	public function save(Section $section): void{
		$stmt = $this->connection->prepare("INSERT INTO section(x, z, world_name, owner_name, owner_prefix, share_group, group_permission, other_permission) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE owner_name = ?, owner_prefix = ?, share_group = ?, group_permission = ?, other_permission = ?");

		$stmt->bind_param('iissssiisssii', $x, $z, $world_name, $owner_name, $owner_prefix, $share_group, $group_permission, $other_permission, $owner_name, $owner_prefix, $share_group, $group_permission, $other_permission);
		$x = $section->getCoordinate()->getX();
		$z = $section->getCoordinate()->getZ();
		$world_name = $section->getCoordinate()->getWorldName();
		$owner_name = $section->getOwnerId()->getName();
		$owner_prefix = $section->getOwnerId()->getPrefix();
		$share_group = json_encode($section->getShareGroup(), JSON_PRETTY_PRINT);
		$group_permission = $section->getGroupPermission()->toBinary();
		$other_permission = $section->getOtherPermission()->toBinary();
		$stmt->execute();
	}

	/**
	 * @inheritDoc
	 */
	public function findByOwner(UserId $userId): array{
		$stmt = $this->connection->prepare("SELECT x, z, world_name, group_permission, other_permission, share_group FROM section WHERE owner_prefix=? AND owner_name=?");
		$stmt->bind_param('ss', $owner_prefix, $owner_name);
		$owner_prefix = $userId->getPrefix();
		$owner_name = $userId->getName();
		$stmt->execute();

		$stmt->bind_result($x, $y, $world_name, $group_permission, $other_permission, $share_group);

		$result = [];
		while($stmt->fetch()){
			$result[] = new Section(new ChunkCoordinate($x, $y, $world_name), new UserId($owner_prefix, $owner_name), ShareGroup::deserializeFromJson(json_decode($share_group, true)), AccessPermission::fromBinary($group_permission), AccessPermission::fromBinary($other_permission));
		}
		$stmt->close();
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function findByShared(UserId $userId): array{
		$stmt = $this->connection->prepare("SELECT x, z, world_name, owner_prefix, owner_name, group_permission, other_permission, share_group FROM mychunklandtest01.section WHERE JSON_CONTAINS(share_group, JSON_OBJECT('name', ?, 'prefix', ?), '$')");
		$stmt->bind_param('ss', $owner_prefix, $owner_name);
		$owner_prefix = $userId->getPrefix();
		$owner_name = $userId->getName();
		$stmt->execute();

		$stmt->bind_result($x, $z, $world_name, $owner_name, $owner_prefix, $group_permission, $other_permission, $share_group);

		$result = [];
		while($stmt->fetch()){
			$result[] = new Section(new ChunkCoordinate($x, $z, $world_name), new UserId($owner_prefix, $owner_name), ShareGroup::deserializeFromJson(json_decode($share_group, true)), AccessPermission::fromBinary($group_permission), AccessPermission::fromBinary($other_permission));
		}
		$stmt->close();
		return $result;
	}

	public function delete(ChunkCoordinate $coordinate): void{
		$stmt = $this->connection->prepare("DELETE FROM mychunklandtest01.section WHERE x = ? AND z = ? AND world_name = ?");
		$stmt->bind_param('iis', $x, $z, $world_name);
		$x = $coordinate->getX();
		$z = $coordinate->getZ();
		$world_name = $coordinate->getWorldName();
		$stmt->execute();
		$stmt->close();
	}
}
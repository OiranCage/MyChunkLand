<?php

use famima65536\mychunkland\system\model\AccessPermission;
use famima65536\mychunkland\system\model\ChunkCoordinate;
use famima65536\mychunkland\system\model\PlayerUserId;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use famima65536\mychunkland\system\model\SystemOwnedUserId;
use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase {

	public function testInitialization(): Section{
		$section = new Section(
			new ChunkCoordinate(0, 0, "world"),
			new PlayerUserId("David"),
			new ShareGroup([]),
			new AccessPermission(true, true, false),
			new AccessPermission(false, false, false)
		);
		// Ok Case
		$this->assertTrue($section->getOwnerId()->equals(new PlayerUserId("David")));
		$this->assertTrue($section->getCoordinate()->equals(new ChunkCoordinate(0, 0, "world")));

		return $section;
	}

	/**
	 * @depends testInitialization
	 */
	public function testPermission(Section $section){
		$goodUserNames = [
			"Adam",
			"Adrian",
			"Alan"
		];

		$badUserNames = [
			"Sonia",
			"Sarah",
			"adam",
			"Victoria"
		];

		$goodUserIds = array_map(fn($name) => new PlayerUserId($name), $goodUserNames);
		$badUserIds = array_map(fn($name) => new PlayerUserId($name), $badUserNames);
		$badUserIds[] = new SystemOwnedUserId("Adam");

		$shareGroup = new ShareGroup($goodUserIds);
		$section->setShareGroup($shareGroup);

		foreach($goodUserIds as $goodUserId){
			$permission = $section->getPermissionFor($goodUserId);
			$this->assertTrue($permission->isReadable());
			$this->assertTrue($permission->isWritable());
			$this->assertNotTrue($permission->isExecutable());
		}

		foreach($badUserIds as $badUserId){
			$permission = $section->getPermissionFor($badUserId);
			$this->assertNotTrue($permission->isReadable());
			$this->assertNotTrue($permission->isWritable());
			$this->assertNotTrue($permission->isExecutable());
		}

	}
}
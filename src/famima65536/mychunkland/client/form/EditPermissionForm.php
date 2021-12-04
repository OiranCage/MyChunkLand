<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\AccessPermission;
use famima65536\mychunkland\system\model\Section;
use pocketmine\form\Form;
use pocketmine\player\Player;

class EditPermissionForm implements Form {

	public function __construct(private Section $section){
	}

	/**
	 * @inheritDoc
	 */
	public function handleResponse(Player $player, $data): void{
		if(!$this->validate($data)){
			Loader::getInstance()->getFormSession($player)->previous();
			return;
		}
		$newGroupPermission = new AccessPermission($data[1], $data[2], $data[3]);
		$newOtherPermission = new AccessPermission($data[5], $data[6], $data[7]);
		$section = $this->section
			->groupPermissionUpdated($newGroupPermission)
			->otherPermissionUpdated($newOtherPermission);
		Loader::getInstance()->asyncSaveSection($section);
	}

	public function validate($data): bool{
		return (
			is_array($data) and
			count($data) === 8 and
			is_bool($data[1]) and
			is_bool($data[2]) and
			is_bool($data[3]) and
			is_bool($data[5]) and
			is_bool($data[6]) and
			is_bool($data[7])
		);
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(){
		$groupPermission = $this->section->getGroupPermission();
		$otherPermission = $this->section->getOtherPermission();
		return [
			"type" => "custom_form",
			"title" => "Edit Permission",
			"content" => [
				["type" => "label", "text" => "Group Permission"],
				["type" => "toggle", "text" => "Read", "default" => $groupPermission->isReadable()],
				["type" => "toggle", "text" => "Write", "default" => $groupPermission->isWritable()],
				["type" => "toggle", "text" => "Execute", "default" => $groupPermission->isExecutable()],
				["type" => "label", "text" => "Other Permission"],
				["type" => "toggle", "text" => "Read", "default" => $otherPermission->isReadable()],
				["type" => "toggle", "text" => "Write", "default" => $otherPermission->isWritable()],
				["type" => "toggle", "text" => "Execute", "default" => $otherPermission->isExecutable()],
			]
		];
	}
}
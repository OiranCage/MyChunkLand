<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\UserId;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class LandMenuForm extends LanguageSupportForm {

	public function __construct(private Section $section){
	}

	/**
	 * @inheritDoc
	 */
	public function handleResponse(Player $player, $data): void{
		if(!is_int($data)){
			Loader::getInstance()->getFormSession($player)->previous();
			return;
		}

		switch($data){
			case 0:
				$coordinate = $this->section->getCoordinate();
				$position = new Vector3(($coordinate->getX() << 4) + 8, 64, ($coordinate->getZ() << 4) + 8);
				$player->teleport($player->getServer()->getWorldManager()->getWorldByName($coordinate->getWorldName())->getSafeSpawn($position));
				break;

			case 1:
				Loader::getInstance()->getFormSession($player)->open(new AddGroupMemberForm($this->section, $player));
				break;

			case 3:
				Loader::getInstance()->getFormSession($player)->open(new EditPermissionForm($this->section));
				break;

			default:
				Loader::getInstance()->getFormSession($player)->previous();
				break;
		}


	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(){
		$section = $this->section;
		return [
			"type" => "form",
			"title" => $this->getLanguage()->get('form.land-menu.title'),
			"content" => join("\n",[
				"{$this->getLanguage()->get('terms.land')}: {$section->getCoordinate()->getX()}.{$section->getCoordinate()->getZ()}@{$section->getCoordinate()->getWorldName()}",
				"{$this->getLanguage()->get('terms.group-access-permission')}: {$section->getGroupPermission()->toString()}",
				"{$this->getLanguage()->get('terms.other-access-permission')}: {$section->getOtherPermission()->toString()}",
				"{$this->getLanguage()->get('terms.group-member')}: ".join(",", array_map(fn(UserId $id) => "{$id->getPrefix()}:{$id->getName()}", $section->getShareGroup()->getUserIds())),
				$this->getLanguage()->get('terms.choose-action')
			]),
			"buttons" => [
				["text" => $this->getLanguage()->get('terms.teleport')],
				["text" => $this->getLanguage()->get('form.add-group-member.title')],
				["text" => $this->getLanguage()->get('form.remove-group-member.title')],
				["text" => $this->getLanguage()->get('form.edit-permission.title')],
				["text" => $this->getLanguage()->get('form.land-menu.abandon')]
			]
		];
	}
}
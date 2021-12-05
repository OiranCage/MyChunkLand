<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use famima65536\mychunkland\system\model\UserId;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

class RemoveGroupMemberForm extends LanguageSupportForm {

	/**
	 * @param Section $section
	 */
	public function __construct(private Section $section, private ShareGroup $changedGroup){
	}

	public function handleResponse(Player $player, $data): void{
		if(!$this->validate($data)){
			if($this->section->getShareGroup() !== $this->changedGroup){
				Loader::getInstance()->getFormSession($player)->open(new RemoveGroupMemberConfirmForm($this->section, $this->changedGroup));
			}
			return;
		}

		$changedGroup = $this->changedGroup->delete($this->changedGroup->getUserIds()[$data]);
		Loader::getInstance()->getFormSession($player)->open(new self($this->section, $changedGroup));
	}

	public function validate($data): bool{
		return (
			is_int($data) and
			0 <= $data and $data < count($this->changedGroup->getUserIds())
		);
	}

	public function jsonSerialize(){
		$buttons = array_map(fn(UserId $userId) => ["text" => "{$userId->getPrefix()}:{$userId->getName()}"], $this->changedGroup->getUserIds());

		return [
			"type" => "form",
			"title" => $this->getLanguage()->get('form.remove-group-member.title'),
			"content" => $this->getLanguage()->get('form.remove-group-member.content'),
			"buttons" => $buttons
		];
	}
}
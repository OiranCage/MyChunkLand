<?php

namespace famima65536\mychunkland\client\form;

use famima65536\mychunkland\client\Loader;
use famima65536\mychunkland\system\model\Section;
use famima65536\mychunkland\system\model\ShareGroup;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

class RemoveGroupMemberConfirmForm extends LanguageSupportForm {

	/**
	 * @param Section $section
	 * @param ShareGroup $changedGroup
	 */
	public function __construct(private Section $section, private ShareGroup $changedGroup){
	}

	public function handleResponse(Player $player, $data): void{
		if(!is_bool($data)){
			return;
		}

		if($data){
			$section = $this->section->shareGroupUpdated($this->changedGroup);
			Loader::getInstance()->asyncSaveSection($section);
		}
	}

	public function jsonSerialize(){
		return [
			"type" => "modal",
			"title" => "Save this change",
			"content" => "Do you save this change?",
			"button1" => $this->getLanguage()->get('terms.yes'),
			"button2" => $this->getLanguage()->get('terms.no')
		];
	}
}
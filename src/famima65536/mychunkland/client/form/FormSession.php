<?php

namespace famima65536\mychunkland\client\form;

use pocketmine\form\Form;
use pocketmine\lang\Language;
use pocketmine\player\Player;
use SplStack;

class FormSession {

	/** @var SplStack<Form> $formStack*/
	private SplStack $formStack;

	public function __construct(private Player $player, private Language $language){
		$this->formStack = new SplStack();
	}

	public function open(LanguageSupportForm $form){
		$form->setLanguage($this->language);
		$this->formStack->push($form);
		$this->player->sendForm($form);
	}

	public function previous(){
		if($this->formStack->count() <= 1){
			return;
		}
		$this->formStack->pop();
		$form = $this->formStack->top();
		$this->player->sendForm($form);
	}
}
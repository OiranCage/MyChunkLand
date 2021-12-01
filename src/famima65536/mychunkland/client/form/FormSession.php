<?php

namespace famima65536\mychunkland\client\form;

use pocketmine\form\Form;
use pocketmine\Player;
use SplStack;

class FormSession {

	/** @var SplStack<Form> $formStack*/
	private SplStack $formStack;

	public function __construct(private Player $player){
		$this->formStack = new SplStack();
	}

	public function open(Form $form){
		$this->formStack->push($form);
		$this->player->sendForm($form);
	}

	public function previous(){
		if($this->formStack->count() === 0){
			return;
		}
		$form = $this->formStack->pop();
		$this->player->sendForm($form);
	}
}
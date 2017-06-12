<?php

/*
	This is a theme specific subcontroller. 
	

 notice the class naming here, needs to adhere to this standard and extend the main controller*/

class MobileexampleDefaultController extends MobileexampleView {

	public function getExampleString(){
		return $this->getText('Hello from the sub controller');
	}

}
<?php

namespace FW\Security;

class UserProfile {
	
	private $id;
	
	private $name;
	
	private $roles;
	
	public function __construct(string $id, string $name, array $roles) {
		$this->id = $id;
		$this->name = $name;
		$this->roles = $roles;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getRoles() {
		return $this->roles;
	}
	
}
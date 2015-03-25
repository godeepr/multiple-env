<?php
class EnvObj {
	public $userID; 
	public $is_new;
	
	
	public $create;// = sprintf("there are %u", '1000');
	public $delete;// '<a href="?page=%s&action=%s&user=%s">Delete environment</a>';
	public $update;// = '<a href="?page=%s&action=%s&user=%s">Update environment</a>';
	function __construct($ID){
	
		$userID = $ID;
		$this->create = sprintf('<a href="?page=%s&action=%s&user=%s">Create a new environment</a>', $_REQUEST['page'], 'create', $ID ); 
		$this->delete = sprintf('<a href="?page=%s&action=%s&user=%s">Delete environment </a>', $_REQUEST['page'], 'delete', $ID );
		$this->update = sprintf('<a href="?page=%s&action=%s&user=%s">Update environment </a>', $_REQUEST['page'], 'update', $ID );
		
	}
	
	function wasCreated(){
		$this->is_new = false;
	
	}
	
	
	function wasNotCreated(){
		$this->is_new = true;
	}
	
	
	
	function getMethods() {
		$user = get_user_by('id', $this->userID);
		if($this->is_new){
			return $this->create;
		
		}
		else{
			return $this->update . '<br>' . $this->delete;
		
		}
	
	
	}
	
	
}

?>
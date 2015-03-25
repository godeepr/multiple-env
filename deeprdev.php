<?php
/**
* Plugin Name: Deepr Development Plugin.
* Plugin URI: deepr.de
* Description: Creates and manage various development environments for each developer.
* Version: 0.0.1
* Author: Moe
* Author URI: deepr.de
* License: Not Public
*/

defined('ABSPATH') or die("No script kiddies please!");

// make sure list_table is loaded
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


//require_once('workspace.php');
//include('ajax_methods.php');


// create the table object which lists all user, in order to give them developer capabilities
class Developer_User_Table extends WP_List_Table {

  // set columns and items
  function configure_table(){
    $header = array(
        'user_login' => 'Login Name',
        'display_name' => 'Name',
        'status' => 'Status',
        'environment' => 'Development Environment',
      );
    $hidden = array();
    $sortable = array();

    $this->_column_headers = array($header, $hidden, $sortable);
    $this->items = get_users();
  }

  function column_default($user, $column_name){
    switch($column_name){
      case 'status':
        $link_text = 'Give Developer Status';
        if ($user->has_cap('developer')){
          $link_text = "Remove Developer Status";
        }

        // watch out for security risk... TODO
		return sprintf('<a href="?page=%s&action=%s&user=%s">%s</a><br><a href="?page=%s&action=%s&user=%s">Update environment</a><br><a href="?page=%s&action=%s&user=%s">Delete Env</a><br><a href="?page=%s&action=%s&user=%s">Create a new environment</a>',$_REQUEST['page'],'devON',$user->ID, $link_text,$_REQUEST['page'],'update',$user->ID,$_REQUEST['page'],'delete',$user->ID,$_REQUEST['page'],'create',$user->ID);
      case 'environment':
        return "Environment noch nicht erstellt";
      default:
        return $user->$column_name;
    }
  }
}

// show function...
function show_page(){
  if (check_cred()) return;

  ?>
  <h1>Deepr Development Plugin</h1>
  <h2>Give User Development Rights</h2>
  <div class="wrap">
  <?php
  echo admin_url('admin-ajax.php')."<br>";
  echo get_theme_root()."<br>";
  echo get_template_directory()."<br>";
  $usertable = new Developer_User_Table();
  $usertable->configure_table();
  $usertable->display();

  $text_status = "Give Developer Status";
  $user = wp_get_current_user();
  if($user->has_cap('developer')){
		$text_status = "Remove Developer Status";
	}
	//"http://www.digitalhost.de/yd/underscores/wp-admin/admin.php?page=deepr"
  ?>
  

  
 <!--<div><form action="http://www.digitalhost.de/yd/underscores/wp-admin/admin.php?page=deepr" method="post">
	<input name="update" type="submit" value="Update environment" /></div> -->

 <div><form action="http://www.digitalhost.de/yd/underscores/wp-admin/admin.php?page=deepr" method="post">
	<input name="allUsers" type="submit" value="Update environment All Users" /></div>	
 
  
  <?php 
  //$user = wp_get_current_user();
  
  if(isset($_GET['action']) && $_GET['action'] == 'devON'){
	if($text_status == "Give Developer Status")
	{
		$user->add_cap('developer');
		$text_status = "Remove Developer Status";
	}
	else{
		$user->remove_cap('developer');
		$text_status = "Give Developer Status";
		}
  }
  
 
  
}

function check_cred(){
	//if (empty($_POST)) return false;
	
	//check_admin_referer();
	
	$form_fields = array('allUsers');
	$method = '';
	
	if (isset($_POST['allUsers'])){
		$url = 'themes.php?page=otto';
		if (false === ($creds = request_filesystem_credentials($url, $method, false, false, $form_fields) ) ) {
		
			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in, 
			// so stop processing for now
			
			return true; // stop the normal page form from displaying
		}
			
		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem($creds) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials($url, $method, true, false, $form_fields);
			return true;
		}
		
		//global $wp_filesystem;
		update_all_users();
	}
	
	
	$form_fields = array('user');
	$method = '';
	
	if (isset($_GET['user']) && isset($_GET['action'])){
		$url = 'themes.php?page=otto';
		if (false === ($creds = request_filesystem_credentials($url, $method, false, false, $form_fields) ) ) {
		
			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in, 
			// so stop processing for now
			return true; // stop the normal page form from displaying
		}
			
		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem($creds) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials($url, $method, true, false, $form_fields);
			return true;
		}
		
		
		//global $wp_filesystem;
		if($_GET['action'] == "update"){
			update_env_by_ID($_GET['user']);
		}
		 
		else if($_GET['action']== "delete"){
			delete_by_ID($_GET['user']);
		}
		
		else if($_GET['action']== "create"){
			create_by_ID($_GET['user']);
		}
	
	}
	
}





// set up a page in the menu
function add_deepr_page(){
  add_utility_page("deepr_development", "deepr", "edit_users","deepr", "show_page", plugin_dir_url(__FILE__).'images/logo.png');
  //$u = get_user_by('login', 'Moritz Hamann');
  //$u->add_cap('developer');
}
add_action('admin_menu', 'add_deepr_page');



function change_current_theme_get($current){
  // query vars are not set when template directory is set, so we need to use get. but possible security implications-> sanitize input
  
  if(is_user_logged_in()){
	  $newtheme = isset($_GET['developer']) ?  "environment-".$_GET['developer']: "";
	  if (!empty($newtheme)){
		if ( validate_file($newtheme) == 0 && file_exists(get_theme_root()."/".$newtheme) ){
		  return $newtheme;
		}
	  }
	  
	  return $current;
	}
}
add_filter('template', 'change_current_theme_get');
add_filter('stylesheet', 'change_current_theme_get');





function copy_parent_theme(){
	echo "copy parent theme..";
	$user = wp_get_current_user();
	
	//var_dump($wp_filesystem);
	
	if(is_user_logged_in()){
		
		$dir_name = "/environment-".$user->user_login;
		//echo $user->user_login;
		if(!file_exists(get_theme_root() . $dir_name)){
			wp_mkdir_p(get_theme_root() . $dir_name);
			//wp_mkdir_p(get_template_directory_uri() . "/testt");
			copy_dir(get_template_directory(), get_theme_root() . $dir_name);			
		}
	}
}

function update_folder() {
	global $wp_filesystem;
	$user = wp_get_current_user();	
	$filename = get_theme_root() .  "/environment-".$user->user_login;
	
	if(is_user_logged_in()){
	
		// Delete last version of child theme 
		if(file_exists($filename))
		{
			$wp_filesystem->delete($filename, true);
			echo "deleting folder...";
		}

		//copy new version
		wp_mkdir_p($filename);
		copy_dir(get_template_directory(), $filename);			
		echo "making the new dir";
	}
}

function update_all_users(){
	global $wp_filesystem;
	$users = get_users();
	echo "updating all users theme...";
	foreach ($users as $user) {
		$filename = get_theme_root() . "/environment-" . $user->user_login;
		if(file_exists($filename)){
			$wp_filesystem->delete($filename,true);
			wp_mkdir_p($filename);
			copy_dir(get_template_directory(), $filename);			
		}
	}
}

/*
*Generic function to update environment by id
*/
function update_env_by_ID($ID){
	global $wp_filesystem;
	$user = get_user_by('id', $ID);
	$filename = get_theme_root() . "/environment-" . $user->user_login;
	if(file_exists($filename)){
		$wp_filesystem->delete($filename,true);
		echo "deleting child id theme...";
		wp_mkdir_p($filename);
		copy_dir(get_template_directory(), $filename);			
		echo "making the new dir...";
	}
}


/*
*Generic function to delete environment by id
*/
function delete_by_ID($ID){
	global $wp_filesystem;
	$user = get_user_by('id', $ID);
	$filename = get_theme_root() . "/environment-" . $user->user_login;
	if(file_exists($filename)){
		$wp_filesystem->delete($filename,true);
		echo "deleting child id theme...";	
	}
}


/*
*Generic function to create a new environment for a user
*/
function create_by_ID($ID){
	global $wp_filesystem;
	$user = get_user_by('id', $ID);
	$filename = get_theme_root() . "/environment-" . $user->user_login;
	if(!file_exists($filename)){
		wp_mkdir_p($filename);
		copy_dir(get_template_directory(), $filename);			
		echo "creating a new env...";
	}
}






?>

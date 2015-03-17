<?php

// this file manages the various ajax calls the chrome plugin can make

function include_jquery(){
  echo "<script src='https://code.jquery.com/jquery-2.1.3.min.js'></script>";
}
// add option to include jquery
add_action('wp_head', 'include_jquery');



function get_developers(){
  // if user is authenticated and has admin rights return all developers
  $user = wp_get_current_user();
  if ($user->has_cap('edit_user')){
    $users = get_users(array(
      //'fields' => array('user_login', 'display_name')
    ));
    $users = array_filter($users, function($u){return $u->has_cap('developer');});
    $users = array_map(function($u){return array($u->ID, $u->user_login, $u->display_name);}, $users);
    echo json_encode($users);
    wp_die();
  } else {
    echo json_encode("error");
    wp_die();
  }
}
add_action('wp_ajax_developers', 'get_developers');


function request_merge(){
  // user has updated his files and requests an merge into main branch
}

function merge_occured(){
  // a merge has occured, notify all users on next keep alive
}

function keep_alive(){
  // this function should be called periodically, to ensure to get notifications from the server
}

function get_file(){
  // send the content of a file back to the chrome plugin (used for php files)

  // test if path is set and check that it is not
  $path = $_POST['path'];
  $validfile = validate_file($path);
  if (isset($path) && ){

  }
}

function update_file(){
  // will updates files in the future directly from the chrome plugin
  // (not yet implemented, due to possibly security risk)
}

?>
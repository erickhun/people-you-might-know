<?php

  require_once 'pymk.php';

  $argv = $_SERVER['argv'];
  
  if (count($argv) >= 2 && is_numeric($argv[1])) {

    if (isset($argv[2]) && is_numeric($argv[2]))
      $limit = $argv[2];
    else
      $limit = 5;
        
    $p = new PeopleYouMightKnow($argv[1]);
    $p->loadUsers("database/user.csv");
    $p->loadFriends("database/friend.csv");
    $p->loadTags("database/tag.csv");
    $p->loadSeens("database/seen.csv");
    $p->find();
    
    $p->show($limit);
  }
 else {
    echo "Usage: php index.php [id_user] [nb_show]\n";
}
  
?>
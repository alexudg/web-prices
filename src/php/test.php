<?php
  if (!empty($_GET))
    echo '"GET":true';
  else if (!empty($_POST))
    echo '"POST":true';
  else if (!empty(file_get_contents('php://input'))) 
    echo '"POST_JSON":true'; 
?>
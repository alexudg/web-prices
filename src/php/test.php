<?php
  if (!empty($_GET))
    echo '"method":"GET"';
  // flutter envia como formulario  
  else if (!empty($_POST))
    echo '"method":"POST"';
  // client REST de VSCode envia como JSON  
  else if (!empty(file_get_contents('php://input'))) 
    echo '"method":"POST_JSON"'; 
?>
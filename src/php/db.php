<?php

if (!empty($_GET) and isset($_GET['fn'])) {
    //exit(json_encode($_GET));
    switch ($_GET['fn']) {
        case 'getUserData':
            getUserData($_GET['token']);
            break;
        case 'getArticles':
            getArticles($_GET['idUser'], $_GET['txt']);
            break;
        case 'isCodeExists':
            isCodeExists($_GET['idUser'], $_GET['code'], $_GET['id']);
            break;
        case 'isDescriptionExists':
            isDescriptionExists($_GET['idUser'], $_GET['description'], $_GET['id']);
            break;
        case 'getArticle':
            getArticle($_GET['idUser'], $_GET['id']);
            break;
        case 'getFamilies':
            getFamilies($_GET['idUser'], $_GET['idUser']);
            break;
        case 'isFamilyExists':
            isFamilyExists($_GET['idUser'], $_GET['description'], $_GET['id']);
            break;
    }
    exit();
}
else if (!empty($_POST) and isset($_POST['fn'])) {
    switch ($_POST['fn']) {
        case 'isUserExists':
            isUserExists($_POST['username'], $_POST['pass']);
            break;
        case 'addUpdateArticle':
            addUpdateArticle($_POST['idUser'], intVal($_POST['id']), $_POST['code'], $_POST['description'], floatval($_POST['price']), $_POST['family']); # idUser, id, code, description, price, family
            break;
        case 'delArticle':
            delArticle($_POST['idUser'], $_POST['id']);
            break;
        case 'addUpdateFamily':
            addUpdateFamily($_POST['idUser'], $_POST['id'], $_POST['description']);
            break;
        case 'delFamily':
            delFamily($_POST['idUser'], $_POST['id']);
            break;
        case 'getUsers':
            getUsers($_POST['idUser']);
            break;
    }
    exit();
}

function getFileUsers() {
    $usersPath = '../json/users.json';    
    # si no existe, crear usuario default admin:1
    if (!file_exists($usersPath)) { 
        $user = array('id'=>1, 'email'=>'puntoplanet@gmail.com', 'username'=>'admin', 'pass'=>'$2y$10$MDggBLqURRhbvdY4WM52me06PmsZe5AHY06rFknDLTrjD77RK3Vcq');
        file_put_contents($usersPath, json_encode(array($user)));
    }
    return json_decode(file_get_contents($usersPath));    
}

function saveFileUsers($users) {
    $usersPath = '../json/users.json';
    file_put_contents($usersPath, json_encode($users, JSON_PRETTY_PRINT));
}

function getFileArticles($idUser) {
    $articlesPath = '../json/articles'.$idUser.'.json';    
    # si no existe, crearlo vacio
    if (!file_exists($articlesPath)) 
        file_put_contents($articlesPath, json_encode(array()));
    return json_decode(file_get_contents($articlesPath));    
}

function saveFileArticles($idUser, $articles) {
    $articlesPath = '../json/articles'.$idUser.'.json';
    file_put_contents($articlesPath, json_encode($articles, JSON_PRETTY_PRINT));
}

function getFileFamilies($idUser) {
    $familiesPath = '../json/families'.$idUser.'.json';    
    # si no existe, crearlo vacio
    if (!file_exists($familiesPath)) 
        file_put_contents($familiesPath, json_encode(array()));
    return json_decode(file_get_contents($familiesPath));    
}

function saveFileFamiles($idUser, $families) {
    $familiesPath = '../json/families'.$idUser.'.json';
    file_put_contents($familiesPath, json_encode($families, JSON_PRETTY_PRINT));
}

function isUserExists($username, $pass) {
    $response = array('success' => false, 'user' => null);
    
    $users = getFileUsers();
    $usernames = array_column($users, 'username'); # array de todos los username
    $key = array_search($username, $usernames); # <int>|false
    
    # key=false puede pasar x cero, comparacion estricta
    if ($key === 0 or $key > 0) { 
        # obtener contraseña encriptada
        $hash = $users[$key]->pass;
        # verificar contraseña
        if (password_verify($pass, $hash)) {
            # generar token
            $token = '';
            for ($i=0; $i < 8; $i++)
                $token .= dechex(rand(0, 15));
            $users[$key]->token = $token; # save token in user register   
            $response['success'] = true;
            $response['user']['id'] = $users[$key]->id;
            $response['user']['username'] = $users[$key]->username;
            $response['user']['token'] = $token;            
        }
    }
    exit(json_encode($response));
}

function getUserData($token) {
    $response = array('success' => false, 'user' => null);    
    $users = getFileUsers();
    $tokens = array_column($users, 'token'); # array de todos los username
    $key = array_search($token, $tokens); # <int>|false
    
    # key=false puede pasar x cero, comparacion estricta
    if ($key === 0 or $key > 0) { 
        $response['success'] = true;
        $response['user']['id'] = $users[$key]->id;
        $response['user']['username'] = $users[$key]->username;
    }    
    exit(json_encode($response));    
}

function getUsers($idUser) {
    $response = array('success'=>false, 'users'=>null);
    $users = getFileUsers();

    # orden alfabetico ascendente
    usort($users, function ($a, $b) {
        return $a->username > $b->username;
    });

    $usersSend = array(); # usuarios a ser enviados
    foreach ($users as $key=>$user) {
        # super-adimin o mismo usuario que lo solicita
        if ($idUser == 1 or $user->id == $idUser) {
            $userSend = array('id'=>$user->id, 'username'=>$user->username); # 
            array_push($usersSend, $userSend); # add new user created 
            if ($idUser != 1)
                break;
        }
    }
    $response['success'] = true;
    $response['users'] = $usersSend;    

    exit(json_encode($response));
}

function isCodeExists($idUser, $code, $id) {
    $response = array('success' => false, 'description' => null);
    $articles = getFileArticles($idUser);
    $codes = array_column($articles, 'code'); # array of all codes
    $key = array_search($code, $codes); # id<int> | false
    
    # si encontro el objeto con el mismo codigo
    if ($key === 0 or $key > 0) { # estrictamente cero entero ya que false tambien es cero
        # verificar si el id es diferente, entonces el codigo es de otro articulo
        if ($articles[$key]->id != $id)  {
            $response['success'] = true;
            $response['description'] = $articles[$key]->description;        
        }        
    }
    exit(json_encode($response));
}

function isDescriptionExists($idUser, $description, $id) {
    $response = array('success' => false);
    $articles = getFileArticles($idUser);
    $description = strtoupper($description); # comparacion en mayusculas
    foreach($articles as $article) {
        if (strtoupper($article->description) == $description and $article->id != $id) {
            $response['success'] = true;
            break;                
        }
    }    
    exit(json_encode($response));
}

function _getFamilyDescription($idUser, $id) {
    $response = '';
    $families = getFileFamilies($idUser);
    $ids = array_column($families, 'id'); # array of id's
    $key = array_search($id, $ids);
    if ($key === 0 or $key > 0) 
        $response = $families[$key]->description;    
    return $response;
}

function addUpdateArticle($idUser, $id, $code, $description, $price, $family) {
    $response = array('success' => false);
    $articles = getFileArticles($idUser);        

    $isContinue = false;
    $family = _getFamilyDescription($idUser, $family); # family_id -> family_description
    # nuevo id para agregar articulo
    if ($id == 0) {
        if (count($articles) > 0)
            $id = end($articles)->id + 1; # ultimo elemento sera id mayor
        else
            $id = 1;        
        $article = array('id'=>$id, 'code'=>$code, 'description'=>$description, 'price'=>$price, 'family'=>$family);  
        array_push($articles, $article); # agregar nuevo articulo
        //print_r($articles);
        $isContinue = true;
    }
    # obtener objeto con el mismo id
    else {
        foreach($articles as $article) {
            # objeto encontrado
            if ($article->id == $id) {
                //$article = $article;
                $article->code = $code;
                $article->description = $description;
                $article->price = $price;
                $article->family = $family;
                break;
            }
        }
        $isContinue = true;
    }    
    if ($isContinue) {
        saveFileArticles($idUser, $articles);
        $response['success'] = true;
    }
    
    exit(json_encode($response));    
}

function getArticles($idUser, $txt) {
    $response = array('success' => false, 'articles' => null);        
    $articles = getFileArticles($idUser); # si no existia se creo uno vacio

    # orden alfabetico ascendente
    usort($articles, function ($a, $b) {
        return $a->description > $b->description;
    });

    if (strlen($txt) > 0) {        
        $response['articles'] = array();
        $txt = strtoupper($txt); # comparacion en mayusculas
        foreach($articles as $article) {
            # is objet, not array
            $posCode = strpos($article->code, $txt); # false|<int>
            $posDescription = strpos(strtoupper($article->description), $txt);
            $posFamily = strpos(strtoupper($article->family), $txt);

            # estrictamente igual a cero entero, ya que cero es false
            if ($posCode === 0 or $posCode > 0 or $posDescription === 0 or $posDescription > 0 or $posFamily === 0 or $posFamily > 0) 
                array_push($response['articles'], $article);                            
        }
        $response['success'] = true;
    }
    else {
        $response['success'] = true;
        $response['articles'] = $articles;
    }
        
    exit(json_encode($response));
}

function getArticle($idUser, $id) {
    $response = array('success' => false, 'article' => null);
    $articles = getFileArticles($idUser);
    $ids = array_column($articles, 'id'); # array of id's
    $key = array_search($id, $ids);
    
    # estrictamente igual a cero entero ya que tambien es false
    if ($key === 0 or $key > 0) {
        $response['success'] = true;
        $response['article'] = $articles[$key];
    }
    
    exit(json_encode($response));
}

function delArticle($idUser, $id) {
    $response = array('success' => false);
    $articles = getFileArticles($idUser);
    $ids = array_column($articles, 'id'); # array de id's 
    $key = array_search($id, $ids);
    # estrictamente igual a cero, ya que false es cero tambien
    if ($key === 0 or $key > 0) { 
        array_splice($articles, $key, 1); # eliminar desde key, 1 elemento
        saveFileArticles($idUser, $articles);
        $response['success'] = true;
    }
     
    exit(json_encode($response));
}

function getFamilies($idUser) {
    $response = array('success' => false, 'families' => null);
    $families = getFileFamilies($idUser); # si no existe se crea uno vacio
        
    # orden ascendente
    usort($families, function ($a, $b) {
        return $a->description > $b->description;
    });

    $response['success'] = true;
    $response['families'] = $families;
    
    exit(json_encode($response));
}

function isFamilyExists($idUser, $description, $id) {
    $response = array('success' => false, 'exists' => null);
    $families = getFileFamilies($idUser);
    $description = strtoupper($description); # comparacion en mayusculas
    $response['success'] = true;
    $response['exists'] = false;
    foreach ($families as $family) {
        if (strtoupper($family->description) == $description and $family->id != $id) {
            $response['exists'] = true;
            break;
        }
    }
    
    exit(json_encode($response)); 
}

function addUpdateFamily($idUser, $id, $description) {
    $response = array('success' => false);
    $families = getFileFamilies($idUser);
    $isContinue = false;
    # add
    if ($id == '0') {
        if (count($families) > 0) 
            # encontrar el max id + 1
            $id = end($families)->id + 1; # ultimo objeto del array
        else 
            $id = 1;
        $family = array('id' => $id, 'description' => $description);
        array_push($families, $family);
        $isContinue = true;
    }
    # update
    else {
        $id = intval($id);
        $ids = array_column($families, 'id'); # array de ids de families
        $key = array_search($id, $ids); # key del objeto con el mismo id
        # key estrictamente igual a cero, ya que cero es false
        if ($key === 0 or $key > 0) {
            $descriptionOld = $families[$key]->description;
            $families[$key]->description = $description;

            # update article.family with descriptionOld
            $articles = getFileArticles($idUser);
            foreach ($articles as $article) {
                if ($article->family == $descriptionOld)
                    $article->family = $description;
            }
            saveFileArticles($idUser, $articles);
            $isContinue = true;
        }
    }
    if ($isContinue) {
        saveFileFamiles($idUser, $families);
        $response['success'] = true;
    }
    
    exit(json_encode($response));
}

function delFamily($idUser, $id) {
    $response = array('success' => false);    
        $families = getFileFamilies($idUser);
        $ids = array_column($families, 'id'); # array de id's 
        $key = array_search($id, $ids);
        # estrictamente igual a cero, ya que false es cero tambien
        if ($key === 0 or $key > 0) { 
            array_splice($families, $key, 1); # eliminar desde key, 1 elemento
            saveFileFamiles($idUser, $families);
            $response['success'] = true;
        }     
    exit(json_encode($response));
}

?>
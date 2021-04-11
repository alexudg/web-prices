<?php

if (!empty($_GET) and isset($_GET['fn'])) {
    //exit(json_encode($_GET));
    switch ($_GET['fn']) {
        case 'getUserData':
            getUserData($_GET['token']);
            break;
        case 'getArticles':
            getArticles($_GET['txt']);
            break;
        case 'isCodeExists':
            isCodeExists($_GET['code'], $_GET['id']);
            break;
        case 'isDescriptionExists':
            isDescriptionExists($_GET['description'], $_GET['id']);
            break;
        case 'getArticle':
            getArticle($_GET['id']);
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
            unset($_POST['fn']);
            addUpdateArticle($_POST);
            break;
        case 'delArticle':
            delArticle($_POST['id']);
            break;
    }
    exit();
}

function isUserExists($username, $pass) {
    $result = array('success' => false, 'user' => null);
    $usersPath = '../json/users.json';
    if (file_exists($usersPath)) {
        $users = json_decode(file_get_contents($usersPath));
        $users_username = array_column($users, 'username'); # array de todos los username
        $key = array_search($username, $users_username); # <int>|false
        
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
                $users[$key]->token = $token;    
                $result['success'] = true;
                $result['user']['id'] = $users[$key]->id;
                $result['user']['username'] = $users[$key]->username;
                $result['user']['token'] = $token;
                file_put_contents($usersPath, json_encode($users, JSON_PRETTY_PRINT));
            }
        }    
    }
    exit(json_encode($result));
}

function getUserData($token) {
    $result = array('success' => false, 'user' => null);
    $usersPath = '../json/users.json';
    if (file_exists($usersPath)) {
        $users = json_decode(file_get_contents($usersPath));
        $users_token = array_column($users, 'token'); # array de todos los username
        $key = array_search($token, $users_token); # <int>|false
        
        # key=false puede pasar x cero, comparacion estricta
        if ($key === 0 or $key > 0) { 
            $result['success'] = true;
            $result['user']['id'] = $users[$key]->id;
            $result['user']['username'] = $users[$key]->username;
        }
    }
    exit(json_encode($result));    
}

function isCodeExists($code, $id) {
    $result = array('success' => false, 'description' => null);
    $articles = json_decode(file_get_contents('../json/articles.json')); # array of objects, inicia con key=0
    $key = array_search($code, array_column($articles, 'code')); # id<int> | false
    
    # si encontro el objeto con el mismo codigo
    if (gettype($key) == 'integer') { # 0=false usar gettype
        $article = $articles[$key]; # tomar el objeto del articulo que tiene el mismo codigo
        # verificar si el id es diferente, entonces el codigo es de otro articulo
        if ($article->id != $id)  {
            $result['success'] = true;
            $result['description'] = $article->description;        
        }        
    }
    exit(json_encode($result));
}

function isDescriptionExists($description, $id) {
    $result = array('success' => false);
    $articles = json_decode(file_get_contents('../json/articles.json')); # array of objects, inicia con key=0
    foreach($articles as $key=>$value) {
        if (strtoupper($description) == strtoupper($value->description)) {
            if ($id != $value->id) 
                $result['success'] = true;
            break;
        }
    }    
    exit(json_encode($result));
}

function addUpdateArticle($article) {
    $article['price'] = floatval($article['price']); # string to float    
    $articles = json_decode(file_get_contents('../json/articles.json'));

    # nuevo id para agregar articulo
    if ($article['id'] == 0) {
        if (count($articles) > 0)
            $article['id'] = end($articles)->id + 1; # ultimo elemento sera id mayor
        else
            $article['id'] = 1;
        array_push($articles, $article); # agregar nuevo articulo
        //print_r($articles);
    }
    # obtener objeto con el mismo id
    else {
        $article['id'] = intval($article['id']); # str to int
        foreach($articles as $value) {
            # objeto encontrado
            if ($article['id'] == $value->id) {
                //$value = $article;
                $value->code = $article['code'];
                $value->description = $article['description'];
                $value->price = $article['price'];
                break;
            }
        }
    }    
    file_put_contents('../json/articles.json', json_encode($articles, JSON_PRETTY_PRINT));
    exit(json_encode(array('success' => true)));    
}

function getArticles($txt) {
    $articles = json_decode(file_get_contents('../json/articles.json'));
    # orden ascendente
    usort($articles, function ($a, $b) {
        return $a->description > $b->description;
    });

    if (strlen($txt) > 0) {
        $result = array();
        foreach($articles as $key => $value) {
            if (gettype(strpos($value->code, $txt)) == 'integer' or gettype(strpos($value->description, $txt)) == 'integer') #objeto no array
                array_push($result, $value);            
        }
    }
    else
        $result = $articles;
    exit(json_encode($result));
}

function getArticle($id) {
    $articles = json_decode(file_get_contents('../json/articles.json'));
    $key = array_search($id, array_column($articles, 'id'));
    $result = array();
    if (gettype($key) == 'integer')
        $result = $articles[$key];
    exit(json_encode($result));
}

function delArticle($id) {
    $result = array('success' => false);
    $articlesPath = '../json/articles.json';
    if (file_exists($articlesPath)) {
        $articles = json_decode(file_get_contents($articlesPath));
        $ids = array_column($articles, 'id'); # array de id's 
        $key = array_search($id, $ids);
        # estrictamente igual a cero, ya que false es cero tambien
        if ($key === 0 or $key > 0) { 
            unset($articles[$key]);
            file_put_contents($articlesPath, json_encode($articles, JSON_PRETTY_PRINT));
            $result['success'] = true;
        }
    } 
    exit(json_encode($result));
}

?>
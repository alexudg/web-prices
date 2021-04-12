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
        case 'getFamilies':
            getFamilies();
            break;
        case 'isFamilyExists':
            isFamilyExists($_GET['description'], $_GET['id']);
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
        case 'addUpdateFamily':
            addUpdateFamily($_POST['id'], $_POST['description']);
            break;
        case 'delFamily':
            delFamily($_POST['id']);
            break;
    }
    exit();
}

function isUserExists($username, $pass) {
    $response = array('success' => false, 'user' => null);
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
                $users[$key]->token = $token; # save token in user register   
                $response['success'] = true;
                $response['user']['id'] = $users[$key]->id;
                $response['user']['username'] = $users[$key]->username;
                $response['user']['token'] = $token;
                file_put_contents($usersPath, json_encode($users, JSON_PRETTY_PRINT));
            }
        }    
    }
    exit(json_encode($response));
}

function getUserData($token) {
    $response = array('success' => false, 'user' => null);
    $usersPath = '../json/users.json';
    if (file_exists($usersPath)) {
        $users = json_decode(file_get_contents($usersPath));
        $users_token = array_column($users, 'token'); # array de todos los username
        $key = array_search($token, $users_token); # <int>|false
        
        # key=false puede pasar x cero, comparacion estricta
        if ($key === 0 or $key > 0) { 
            $response['success'] = true;
            $response['user']['id'] = $users[$key]->id;
            $response['user']['username'] = $users[$key]->username;
        }
    }
    exit(json_encode($response));    
}

function isCodeExists($code, $id) {
    $response = array('success' => false, 'description' => null);
    $articles = json_decode(file_get_contents('../json/articles.json')); # array of objects, inicia con key=0
    $codes = array_column($articles, 'code'); # array of all codes
    $key = array_search($code, $codes); # id<int> | false
    
    # si encontro el objeto con el mismo codigo
    if ($key === 0 or $key > 0) { # estrictamente cero entero ya que false tambien es cero
        $article = $articles[$key]; # tomar el objeto del articulo que tiene el mismo codigo
        # verificar si el id es diferente, entonces el codigo es de otro articulo
        if ($article->id != $id)  {
            $response['success'] = true;
            $response['description'] = $article->description;        
        }        
    }
    exit(json_encode($response));
}

function isDescriptionExists($description, $id) {
    $response = array('success' => false);
    $articles = json_decode(file_get_contents('../json/articles.json')); # array of objects, inicia con key=0
    $description = strtoupper($description); # comparacion en mayusculas
    foreach($articles as $article) {
        if ($description == strtoupper($article->description)) {
            if ($id != $article->id) 
                $response['success'] = true;
            break;
        }
    }    
    exit(json_encode($response));
}

function getFamilyDescription($id) {
    $response = '';
    $familiesPath = '../json/families.json';
    if (file_exists($familiesPath)) {
        $families = json_decode(file_get_contents($familiesPath));
        $ids = array_column($families, 'id'); # array of id's
        $key = array_search($id, $ids);
        if ($key === 0 or $key > 0) 
            $response = $families[$key]->description;
    }
    return $response;
}

function addUpdateArticle($article) {
    $response = array('success' => false);
    $articlesPath = '../json/articles.json';
    if (file_exists($articlesPath)) {
        $articles = json_decode(file_get_contents($articlesPath));
        $article['price'] = floatval($article['price']); # string to float    

        $isContinue = false;
        # nuevo id para agregar articulo
        if ($article['id'] == 0) {
            if (count($articles) > 0)
                $article['id'] = end($articles)->id + 1; # ultimo elemento sera id mayor
            else
                $article['id'] = 1;
            $article['family'] = getFamilyDescription($article['family']); # family contiene el id   
            array_push($articles, $article); # agregar nuevo articulo
            //print_r($articles);
            $isContinue = true;
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
                    $value->family = getFamilyDescription($article['family']); # family contiene el id   
                    break;
                }
            }
            $isContinue = true;
        }    
        if ($isContinue) {
            file_put_contents($articlesPath, json_encode($articles, JSON_PRETTY_PRINT));
            $response['success'] = true;
        }
    }
    exit(json_encode($response));    
}

function getArticles($txt) {
    $response = array('success' => false, 'articles' => null);
    $articlesPath = '../json/articles.json';
    if (file_exists($articlesPath)) {
        $articles = json_decode(file_get_contents($articlesPath));
    
        # orden ascendente
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

                # estrictamente igual a cero entero, ya que cero es false
                if ($posCode === 0 or $posCode > 0 or $posDescription === 0 or $posDescription > 0) 
                    array_push($response['articles'], $article);                            
            }
            $response['success'] = true;
        }
        else {
            $response['success'] = true;
            $response['articles'] = $articles;
        }
    }    
    exit(json_encode($response));
}

function getArticle($id) {
    $response = array('success' => false, 'article' => null);
    $articlesPath = '../json/articles.json';
    if (file_exists($articlesPath)) {
        $articles = json_decode(file_get_contents($articlesPath));
        $ids = array_column($articles, 'id'); # array of id's
        $key = array_search($id, $ids);
        
        # estrictamente igual a cero entero ya que tambien es false
        if ($key === 0 or $key > 0) {
            $response['success'] = true;
            $response['article'] = $articles[$key];
        }
    }
    exit(json_encode($response));
}

function delArticle($id) {
    $response = array('success' => false);
    $articlesPath = '../json/articles.json';
    if (file_exists($articlesPath)) {
        $articles = json_decode(file_get_contents($articlesPath));
        $ids = array_column($articles, 'id'); # array de id's 
        $key = array_search($id, $ids);
        # estrictamente igual a cero, ya que false es cero tambien
        if ($key === 0 or $key > 0) { 
            array_splice($articles, $key, 1); # eliminar desde key, 1 elemento
            file_put_contents($articlesPath, json_encode($articles, JSON_PRETTY_PRINT));
            $response['success'] = true;
        }
    } 
    exit(json_encode($response));
}

function getFamilies() {
    $response = array('success' => false, 'families' => null);
    $familiesPath = '../json/families.json';
    if (file_exists($familiesPath)) {
        $families = json_decode(file_get_contents($familiesPath));
        
        # orden ascendente
        usort($families, function ($a, $b) {
            return $a->description > $b->description;
        });

        $response['success'] = true;
        $response['families'] = $families;
    }
    exit(json_encode($response));
}

function isFamilyExists($description, $id) {
    $response = array('success' => false, 'exists' => null);
    $familiesPath = '../json/families.json';
    if (file_exists($familiesPath)) {
        $families = json_decode(file_get_contents($familiesPath));
        $description = strtoupper($description); # comparacion en mayusculas
        $response['success'] = true;
        $response['exists'] = false;
        foreach ($families as $family) {
            if (strtoupper($family->description) == $description and $family->id != $id) {
                $response['exists'] = true;
                break;
            }
        }
    }
    exit(json_encode($response)); 
}

function addUpdateFamily($id, $description) {
    $response = array('success' => false);
    $familiesPath = '../json/families.json';
    if (file_exists($familiesPath)) {
        $families = json_decode(file_get_contents($familiesPath));
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
                $family = $families[$key];
                $descriptionOld = $family->description;
                $family->description = $description;

                # update article.family with descriptionOld
                $articlesPath = '../src/json/articles.json';
                if (file_exists($articlesPath)) {
                    $articles = json_decode(file_get_contents($articlesPath));
                    foreach ($articles as $article) {
                        if ($article->family == $descriptionOld)
                            $article->family = $description;
                    }
                }

                $isContinue = true;
            }
        }
        if ($isContinue) {
            file_put_contents($familiesPath, json_encode($families, JSON_PRETTY_PRINT));
            $response['success'] = true;
        }
    }
    exit(json_encode($response));
}

function delFamily($id) {
    $response = array('success' => false);
    $familiesPath = '../json/families.json';
    if (file_exists($familiesPath)) {
        $families = json_decode(file_get_contents($familiesPath));
        $ids = array_column($families, 'id'); # array de id's 
        $key = array_search($id, $ids);
        # estrictamente igual a cero, ya que false es cero tambien
        if ($key === 0 or $key > 0) { 
            array_splice($families, $key, 1); # eliminar desde key, 1 elemento
            file_put_contents($familiesPath, json_encode($families, JSON_PRETTY_PRINT));
            $response['success'] = true;
        }
    } 
    exit(json_encode($response));
}

?>
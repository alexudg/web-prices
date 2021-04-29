<?php

//exit(phpinfo()); # extensiones habilitadas
//exit(ini_get('disable_functions')); # funciones deshabilitadas

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
        case 'getUsersData':
            getUsersData();
            break;
        case 'isUsernameExists':
            isUsernameExists($_GET['username'], $_GET['id']);
            break;
        case 'isEmailExists':
            isEmailExists($_GET['email'], $_GET['id']);
            break;
        case 'getNewTokenUser':
            getNewTokenUser($_GET['email']);
            break;
        case 'isEmailTokenExists':
            isEmailTokenExists($_GET['email'], $_GET['token']);
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
        case 'getUsersMinimal':
            getUsersMinimal();
            break;
        case 'isUserPass':
            isUserPass($_POST['id'], $_POST['pass']);
            break;
        case 'addUpdateUserData':
            addUpdateUserData(); # se utiliza $_POST
            break;
        case 'delUser':
            delUser($_POST['id']);
            break;
        case 'updatePass':
            updatePass($_POST['id'], $_POST['pass']);
            break;
    }
    exit();
}

########################## sqlite ############################

class Database {
    static $db = null;
    
    static function loadDatabase() {
        self::$db = new PDO(
            'sqlite:../db/prices.db', # dsn path from here
            null, # username
            null, # password
            #options
            array(
                PDO::ATTR_STRINGIFY_FETCHES => false, # evitar todo valor string
                PDO::ATTR_EMULATE_PREPARES => false, # evita que todo se trate como string (no funciona en sqlite) 
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // only column_name => value
            )            
        );
        #echo 'DB created';
    } 
    
    static function executeSql($sql, $params=null, $all=true) {
        $response = array('success'=>false, 'exception'=>null, 'result'=>null);
        try {
            if (self::$db == null)
                self::loadDatabase();
            $sta = self::$db->prepare($sql);
            $sta->execute($params);
            $result = $all ? $sta->fetchAll() : $sta->fetch();
            $response['success'] = true;
            $response['result'] = $result;
        }
        catch (Exception $e) {
            $response['exception'] = $e->getMessage();
        }
        return $response;
    }
}

/*
//test();
function test() {
    //$sql = 'SELECT * FROM users';
    //$response = Database::executeSql($sql);
    $sql = 'SELECT * FROM users WHERE id = ?';
    $params = array(1);
    $response = Database::executeSql($sql, $params, false);
    $response['result']['id'] += 0; # string to int
    echo '<pre>';
    //var_dump($response);
    echo json_encode($response);
    echo '</pre>';
}
//*/

function isUserExists($username, $pass) {
    $sql = 'SELECT id, pass, email FROM users WHERE username = ?';
    $params = array($username);
    $response = Database::executeSql($sql, $params, false); # result: false|{id: <int>, pass: <str_hash>}
    
    # si el username existe
    if ($response['result']) {
        # pass OK
        if (password_verify($pass, $response['result']['pass'])) {
            unset($response['result']['pass']); # quitar pass
            # generar token y guardarlo
            $token = '';
            for ($i=0; $i < 8; $i++)
                $token .= dechex(rand(0, 15));
            $sql = 'UPDATE users SET token = ? WHERE id = ?';
            $params = array($token, $response['result']['id']);
            Database::executeSql($sql, $params);
            $response['result']['username'] = $username; # agregar el username
            $response['result']['token'] = $token; # agregar token
        }
        else
            $response['result'] = false;    
    }
    exit(json_encode($response));
}

function getUserData($token) {
    $sql = 'SELECT id, username, email FROM users WHERE token = ?';
    $params = array($token);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false|{id:<val>, username:<val>, email:<val>}
    exit(json_encode($response));
}

function addUpdateUserData() {
    # encriptar contraseña si no esta vacia
    if ($_POST['pass'] !== '')         
        $_POST['pass'] = password_hash($_POST['pass'], PASSWORD_BCRYPT); # encriptar pass

    # add?
    if (intval($_POST['id']) == 0) {
        $sql = 'INSERT INTO users (username, email, pass) VALUES (?, ?, ?)';
        $params = array($_POST['username'], $_POST['email'], $_POST['pass']);
        $response = Database::executeSql($sql, $params, false); 
        $response['result'] = true;
    }
    # update
    else {
        $sql = 'UPDATE users SET username = ?, email = ?';
        $params = array($_POST['username'], $_POST['email']);
        # si no esta vacia la contraseña, adherirla a sql
        if ($_POST['pass'] !== '') { 
            $sql .= ', pass = ?';
            array_push($params, $_POST['pass']);
        }
        $sql .= ' WHERE id = ?';
        array_push($params, $_POST['id']); 
        $response = Database::executeSql($sql, $params, false); 
        $response['result'] = true;       
    }
    exit(json_encode($response));
}

function isUserPass($id, $pass) {
    $sql = 'SELECT pass FROM users WHERE id = ?';
    $params = array($id);
    $response = Database::executeSql($sql, $params, false); # success: false|true, exception:<string>|null, result:null|<pass_hash> 
    if (isset($response['result']['pass'])) {
        $response['result'] = password_verify($pass, $response['result']['pass']);
        unset($response['result']['pass']); # quitar pass
    }        
    exit(json_encode($response));
}

function getUsersData() {
    $sql = 'SELECT id, username, email FROM users ORDER BY username';
    $response = Database::executeSql($sql);    
    exit(json_encode($response));
}

function isUsernameExists($username, $id) {
    $sql = 'SELECT 1 FROM users WHERE username = ? AND id <> ?';
    $params = array($username, $id);
    $response = Database::executeSql($sql, $params);
    $response['result'] = count($response['result']) > 0;
    exit(json_encode($response));
}

function delUser($id) {
    $sql = 'DELETE FROM users WHERE id = ?';
    $params = array($id);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:false
    $response['result'] = true;
    exit(json_encode($response));
}

function isEmailExists($email, $id) {
    $sql = 'SELECT 1 FROM users WHERE email = ? AND id <> ?';
    $params = array($email, $id);
    $response = Database::executeSql($sql, $params);
    $response['result'] = count($response['result']) > 0;
    exit(json_encode($response));
}

function getNewTokenUser($email) {
    # generar token
    $token = '';
    for ($i=0; $i < 8; $i++)
        $token .= dechex(rand(0, 15));
    # guardar token
    $sql = 'UPDATE users SET token = ? WHERE email = ?';
    $params = array($token, $email);
    $response = Database::executeSql($sql, $params, false);
    $response['result'] = $token;    
    exit(json_encode($response));
}

function isEmailTokenExists($email, $token) {
    $sql = 'SELECT id FROM users WHERE email = ? AND token = ?';
    $params = array($email, $token);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|{'id':<id>}
    exit(json_encode($response));
}

function updatePass($id, $pass) {
    $pass = password_hash($pass, PASSWORD_BCRYPT);
    $sql = 'UPDATE users SET pass = ? WHERE id = ?';
    $params = array($pass, $id);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false
    $response['result'] = true;
    exit(json_encode($response));
}

function getUsersMinimal() {
    $sql = 'SELECT id, username FROM users ORDER BY username';
    $response = Database::executeSql($sql); # todos usuarios id-username
    exit(json_encode($response));
}

function getArticles($idUser, $txt) {
    $sql = 'SELECT id, description, price, code, family, id_user FROM articles WHERE id_user = 2';
    $params = array();
    // if (strlen($txt) > 0) {
    //     $sql .= ' AND (description LIKE ? OR code LIKE ? OR family LIKE ?)';
    //     $txt = '%'.$txt.'%';
    //     $params = array($idUser, $txt, $txt, $txt);
    // }
    $response = Database::executeSql($sql, $params); 
    exit(json_encode($response));
}

######################### JSON ################################

##### files #####

/*
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
*/

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


##### users #####

/*
function isUserExists($username, $pass) {
    $response = array('success' => false, 'user' => null);
    
    $users = getFileUsers();
    $usernames = array_column($users, 'username'); # array de todos los username
    $key = array_search($username, $usernames); # <int>|false
    
    # key=false puede pasar x cero, comparacion estricta
    if ($key === 0 or $key > 0) { 
        # verificar contraseña
        if (password_verify($pass, $users[$key]->pass)) {
            # generar token
            $token = '';
            for ($i=0; $i < 8; $i++)
                $token .= dechex(rand(0, 15));
            $users[$key]->token = $token; # save token in user register   
            $response['success'] = true;
            $response['user']['id'] = $users[$key]->id;
            $response['user']['username'] = $users[$key]->username;
            $response['user']['email'] = $users[$key]->email;
            $response['user']['token'] = $token;
            # save file with new token
            saveFileUsers($users);            
        }
    }
    exit(json_encode($response));
}*/

/*
function isUserPass($id, $pass) {
    $response = array('success'=>false);
    $users = getFileUsers();
    $ids = array_column($users, 'id'); # array of id's
    $key = array_search($id, $ids); # false|<int>
    # comparacion estricta a cero integer, ya que false=0 tambien
    if ($key === 0 or $key > 0) {
        if (password_verify($pass, $users[$key]->pass))
            $response['success'] = true;
    }
    exit(json_encode($response));
}*/

/*
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
        $response['user']['email'] = $users[$key]->email;
    }    
    exit(json_encode($response));    
}*/

/*
function isUsernameExists($username, $id) {
    $response = array('success'=>false);
    $users = getFileUsers();
    $usernames = array_column($users, 'username'); # array of username's
    $key = array_search($username, $usernames);
    # si key es integer, existe; si es diferente el id, ya esta ocupado
    if ($key === 0 or $key > 0)
        if ($users[$key]->id != $id) # ya lo tiene otro usuario
            $response['success'] = true;
    exit(json_encode($response));
}
*/

/*
function isEmailExists($email, $id) {
    $response = array('success'=>false);
    $users = getFileUsers();
    $emails = array_column($users, 'email'); # array of email's
    $key = array_search($email, $emails);
    # si key es integer, existe; si es diferente el id, ya esta ocupado
    if ($key === 0 or $key > 0)
        if ($users[$key]->id != $id) # ya lo tiene otro usuario
            $response['success'] = true;
    exit(json_encode($response));
}*/

/*
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
            $userSend = array(
                'id'=>$user->id, 
                'username'=>$user->username
            ); # 
            array_push($usersSend, $userSend); # add new user created 
            if ($idUser != 1)
                break;
        }
    }
    $response['success'] = true;
    $response['users'] = $usersSend;    

    exit(json_encode($response));
}*/

/*
function getUsersData() {
    $result = array('success'=>false);
    $users = getFileUsers();
    
    # orden alfabetico ascendente
    usort($users, function ($a, $b) {
        return $a->username > $b->username;
    });

    $usersSend = array();
    foreach ($users as $user) {
        $userSend = array(
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email
        );
        array_push($usersSend, $userSend);
    }
    $result['success'] = true;
    $result['users'] = $usersSend;
    exit(json_encode($result));
}
*/

/*
function addUpdateUserData() {
    $response = array('success'=>false);
    $users = getFileUsers();
    
    # add?
    if (intval($_POST['id']) == 0) {
        $_POST['pass'] = password_hash($_POST['pass'], PASSWORD_BCRYPT); # encriptar contraseña obligatoria
        $user = array(
            'id' => end($users)->id + 1, # max id +1
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'pass' => $_POST['pass']
        );
        array_push($users, $user);
        saveFileUsers($users);
        $response['success'] = true;
    }
    # update
    else {
        $ids = array_column($users, 'id'); # array of id's
        $key = array_search($_POST['id'], $ids);
        # key debe ser esctrictamente entero
        if ($key === 0 or $key > 0) {
            $users[$key]->username = $_POST['username'];
            $users[$key]->email = $_POST['email'];
            if ($_POST['pass'] !== '') {
                # encriptar pass
                $_POST['pass'] = password_hash($_POST['pass'], PASSWORD_BCRYPT);
                $users[$key]->pass = $_POST['pass'];
            }
            saveFileUsers($users);
            $response['success'] = true;        
        }
    }
    exit(json_encode($response));
}
*/

/*
function delUser($id) {
    $users = getFileUsers();
    $ids = array_column($users, 'id'); # array of id's
    $key = array_search($id, $ids);
    if ($key === 0 or $key > 0) {
        array_splice($users, $key, 1); # en el array de users ubicar el que tenga el key y eliminar 1 a partir de el
        # eliminar familias y articulos de este usuario
        unlink('../json/families'.$id.'.json');
        unlink('../json/articles'.$id.'.json');
        saveFileUsers($users);
    }
    exit(json_encode(array('success'=>true)));
}*/

/*
function getNewTokenUser($email) {
    $response = array('success'=>false, 'token'=>null);
    $users = getFileUsers();
    $emails = array_column($users, 'email'); # array of email's
    $key = array_search($email, $emails);
    if ($key === 0 or $key > 0) {
        # generar token
        $token = '';
        for ($i=0; $i < 8; $i++)
            $token .= dechex(rand(0, 15));
        $users[$key]->token = $token;
        saveFileUsers($users);
        $response['success'] = true;
        $response['token'] = $token;    
    }
    exit(json_encode($response));
}*/

/*
function isEmailTokenExists($email, $token) {
    $response = array('success'=>false, 'id'=>null);
    $users = getFileUsers();
    $emails = array_column($users, 'email'); # array of email's
    $key = array_search($email, $emails);
    # email exists
    if ($key === 0 or $key > 0) {
        # token equals
        if ($users[$key]->token == $token)
            $response['success'] = true; 
            $response['id'] = $users[$key]->id; # retornar id
    }
    exit(json_encode($response));
}*/

/*
function updatePass($id, $pass) {
    $response = array('success'=>false);
    $users = getFileUsers();
    $ids = array_column($users, 'id');
    $key = array_search($id, $ids);
    # registro encontrado
    if ($key === 0 or $key > 0) {
        $pass = password_hash($pass, PASSWORD_BCRYPT);
        $users[$key]->pass = $pass;
        # del token expired
        $users[$key]->token = '';
        saveFileUsers($users);
        $response['success'] = true;
    }
    exit(json_encode($response));
}*/

##### articles #####

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

/*
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
*/

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

##### families #####

function _getFamilyDescription($idUser, $id) {
    $response = '';
    $families = getFileFamilies($idUser);
    $ids = array_column($families, 'id'); # array of id's
    $key = array_search($id, $ids);
    if ($key === 0 or $key > 0) 
        $response = $families[$key]->description;    
    return $response;
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
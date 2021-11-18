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
            getFamilies($_GET['idUser']);
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
        case 'getCountArticles':
            getCountArticles($_GET['idUser']);
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
            addUpdateArticle(); # en la funcion tomar $_POST
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
        case 'passRenew':
            passRenew($_POST['id'], $_POST['pass']);
            break;
    }
    exit();
}
###### solicitud JSON desde movil #####
else if (!empty(file_get_contents('php://input'))) {
    $post = json_decode(file_get_contents('php://input'));
    //echo json_encode($post->fn);
    switch ($post->fn) {
        case 'isUserExists':
            isUserExists($post->username, $post->pass);
            break;
        case 'addUpdateArticle':
            addUpdateArticleJson($post); # en la funcion tomar $post
            // fn, description, price, cost, code, idFamily, idUser, id
            break;
        case 'delArticle':
            delArticle($post->idUser, $post->id);
            break;
        case 'addUpdateFamily':
            addUpdateFamily($post->idUser, $post->id, $post->description);
            break;
        case 'delFamily':
            delFamily($post->idUser, $post->id);
            break;
        case 'getUsersMinimal':
            getUsersMinimal();
            break;
        case 'isUserPass':
            isUserPass($post->id, $post->pass);
            break;
        case 'addUpdateUserData':
            addUpdateUserData(); # se utiliza $_POST
            break;
        case 'delUser':
            delUser($post->id);
            break;
        case 'passRenew':
            passRenew($post->id, $post->pass);
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
        $response = array('success'=>false, 'exception'=>'', 'result'=>null);
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
    
    # si el username existe, result: {id:<int>, pass:<str_hash>}
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
            $response['result'] = null;    
    }
    else
        $response['result'] = null;
    exit(json_encode($response));
}

function getUserData($token) {
    $sql = 'SELECT id, username, email, token FROM users WHERE token = ?';
    $params = array($token);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false|{id:<val>, username:<val>, email:<val>}
    exit(json_encode($response));
}

function addUpdateUserData() {
    # encriptar contraseña si no esta vacia
    if ($_POST['pass'] !== '')         
        $_POST['pass'] = password_hash($_POST['pass'], PASSWORD_BCRYPT); # encriptar pass

    # add?
    if (intval($_POST['id']) == -1) {
        $sql = 'INSERT INTO users (username, email, pass) VALUES (?, ?, ?)';
        $params = array($_POST['username'], $_POST['email'], $_POST['pass']);
        Database::executeSql($sql, $params, false); 
        
        // get new id 
        $sql = 'SELECT MAX(id) id FROM users';
        $response = Database::executeSql($sql, null, false); // {'success'=>false|true, 'exception'=>'', 'result'=>null|[{'id':<int>}]};
        $_POST['id'] = $response['result']['id'];

        /// create new table families_<id>
        $sql = 'CREATE TABLE families_'.$_POST['id'].' (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            description TEXT    COLLATE NOCASE            
        )';
        Database::executeSql($sql);
        
        /// create new table articles_<id>
        $sql = 'CREATE TABLE articles_'.$_POST['id'].' (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            code        TEXT,
            description TEXT    COLLATE NOCASE,
            price       REAL,
            cost        REAL,
            id_family   INTEGER REFERENCES families_'.$response['result']['id'].' (id) 
                                                         ON DELETE SET NULL
                                                         ON UPDATE CASCADE           
        )';
        Database::executeSql($sql);

        /// create view v_articles_<id>
        $sql = 'CREATE VIEW v_articles_'.$_POST['id'].' AS
            SELECT a.id,
                a.description,
                a.price,
                a.cost,
                a.code,
                COALESCE(f.description, "") family
            FROM articles_'.$_POST['id'].' a
                LEFT JOIN
                families_'.$_POST['id'].' f ON f.id = a.id_family
            ORDER BY a.description';
        Database::executeSql($sql);    

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
    /// first delete view and tables of user
    $sql = 'DROP VIEW v_articles_'.$id;
    Database::executeSql($sql, null, false);

    $sql = 'DROP TABLE families_'.$id;
    Database::executeSql($sql, null, false);

    $sql = 'DROP TABLE articles_'.$id;
    Database::executeSql($sql, null, false);

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

# renovar contraseña 
function passRenew($id, $pass) {
    $pass = password_hash($pass, PASSWORD_BCRYPT);
    $sql = 'UPDATE users SET pass = ?, token = "" WHERE id = ?';
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

##### families (all request of dashboard.js)

function getFamilies($idUser) {
    $sql = 'SELECT id, description FROM families_'.$idUser.' ORDER BY description';    
    $response = Database::executeSql($sql);
    exit(json_encode($response));
}

function isFamilyExists($idUser, $description, $id) {
    $sql = 'SELECT 1 FROM families_'.$idUser.' WHERE description = ? AND id <> ?';
    $params = array($description, $id);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false|{1:'1'}
    if ($response['success'] and $response['result']) # si result no es false y contiene datos
      $response['result'] = true;
    exit(json_encode($response)); 
}

function addUpdateFamily($idUser, $id, $description) {
    # add
    if ($id == '0') {
        $sql = 'INSERT INTO families_'.$idUser.' (description) VALUES(?)';
        $params = array($description);
        $response = Database::executeSql($sql, $params); # success:false|true, exception:<string>|null, result:null|false
        if ($response['success'])
            $response['result'] = true;
    }
    # edit
    else {
        $sql = 'UPDATE families_'.$idUser.' SET description = ? WHERE id = ?';
        $params = array($description, $id);
        $response = Database::executeSql($sql, $params); # success:false|true, exception:<string>|null, result:null|false
        if ($response['success'])
            $response['result'] = true;
    }
    exit(json_encode($response));
}

function delFamily($idUser, $id) {
    $sql = 'DELETE FROM families_'.$idUser.' WHERE id = ?';
    $params = array($id);
    $response = Database::executeSql($sql, $params); # success:false|true, exception:<string>|null, result:null|false (false=sin datos, pero si exitoso)
    if ($response['success'])
        $response['result'] = true; # sustituir false(sin datos) x true(realizado)
    exit(json_encode($response));
}

##### articles (all request of dashboard.js)

function getArticles($idUser, $txt) {
    # consulta a vista indexada
    $sql = 'SELECT id, description, price, cost, code, family 
            FROM v_articles_'.$idUser;
    
    $params = array();
        
    # busqueda por texto
    if (strlen($txt) > 0) {
        $sql .= ' WHERE (REPLACE(description, "ñ", "Ñ") LIKE ? OR code LIKE ? OR family LIKE ?)';
        # quitar acentos
        $txt = utf8_encode(strtr(utf8_decode($txt), utf8_decode('áéíóúÁÉÍÓÚñ'), utf8_decode('aeiouAEIOUÑ')));
        
        $txt = '%'.$txt.'%';
        array_push($params, $txt, $txt, $txt);
    }
    $response = Database::executeSql($sql, $params);
    
    # convertir price-cost str en float
    foreach ($response['result'] as $key=>$article) {
        $response['result'][$key]['price'] += 0.0;
        # si cost no es null, convertirlo de texto a float
        if ($response['result'][$key]['cost'])
            $response['result'][$key]['cost'] += 0.0;       
    }
    exit(json_encode($response));
}

function isCodeExists($idUser, $code, $id) {
    $sql = 'SELECT description FROM articles_'.$idUser.' WHERE code = ? AND id <> ?';
    $params = array($code, $id);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false|{description:<description>} 
    exit(json_encode($response));
}

function isDescriptionExists($idUser, $description, $id) {
    $sql = 'SELECT 1 FROM articles_'.$idUser.' WHERE description = ? AND id <> ?';
    $params = array($description, $id);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false|{1:'1'} 
    if ($response['success'] and $response['result']) # result false=no_existe, {1:'1'}=existe
        $response['result'] = true;
    exit(json_encode($response));
}

# tomar los parametros de $_POST (idUser, id, code, description, price, family)
function addUpdateArticle() {
    # quitarle los acentos a descripcion respetando la ñ
    $_POST['description'] = utf8_encode(strtr(utf8_decode($_POST['description']), utf8_decode('áéíóúÁÉÍÓÚ'), 'aeiouAEIOU'));
    # costo vacio o cero pasarlo a null
    if (strlen($_POST['cost']) == 0 or floatval($_POST['cost']) == 0)
        $_POST['cost'] = null;
    # add
    if ($_POST['id'] == '0') {
        $sql = 'INSERT INTO articles_'.$_POST['idUser'].' (description, price, cost, code, id_family) VALUES (?, ?, ?, ?, ?)';
        $params = array($_POST['description'], $_POST['price'], $_POST['cost'], $_POST['code'], $_POST['idFamily']);
        $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false (false=sin datos, pero si exitoso) 
        if ($response['success'])
            $response['result'] = true; # sustituir false(sin datos) x true(realizado)
    }
    # edit
    else {
        $sql = 'UPDATE articles_'.$_POST['idUser'].' SET description = ?, price = ?, cost = ?, code = ?, id_family = ? WHERE id = ?';
        $params = array($_POST['description'], $_POST['price'], $_POST['cost'], $_POST['code'], $_POST['idFamily'], $_POST['id']);
        $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false (false=sin datos, pero si exitoso) 
        if ($response['success'])
            $response['result'] = true; # sustituir false(sin datos) x true(realizado)
    }    
    exit(json_encode($response));    
}

### misma funcion que la anterior pero solo para JSON, desde movil
function addUpdateArticleJson($post) {
    # quitarle los acentos a descripcion respetando la ñ
    $post->description = utf8_encode(strtr(utf8_decode($post->description), utf8_decode('áéíóúÁÉÍÓÚ'), 'aeiouAEIOU'));
    # costo vacio o cero pasarlo a null
    if (strlen($post->cost) == 0 or floatval($post->cost) == 0)
        $post->cost = null;
    # add
    if ($post->id == '0') {
        $sql = 'INSERT INTO articles_'.$post->idUser.' (description, price, cost, code, id_family) VALUES (?, ?, ?, ?, ?)';
        $params = array($post->description, $post->price, $post->cost, $post->code, $post->idFamily);
        $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false (false=sin datos, pero si exitoso) 
        if ($response['success'])
            $response['result'] = true; # sustituir false(sin datos) x true(realizado)
    }
    # edit
    else {
        $sql = 'UPDATE articles_'.$post->idUser.' SET description = ?, price = ?, cost = ?, code = ?, id_family = ? WHERE id = ?';
        $params = array($post->description, $post->price, $post->cost, $post->code, $post->idFamily, $post->id);
        $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false (false=sin datos, pero si exitoso) 
        if ($response['success'])
            $response['result'] = true; # sustituir false(sin datos) x true(realizado)
    }    
    exit(json_encode($response));    
}

function getArticle($idUser, $id) {
    $sql = 'SELECT a.code, a.description, a.price, a.cost, a.description, a.id_family 
            FROM articles_'.$idUser.' a 
            WHERE a.id = ?';
    $params = array($id);
    $response = Database::executeSql($sql, $params, false); # success:false|true, exception:<string>|null, result:null|false|{object}
    exit(json_encode($response));
}

function delArticle($idUser, $id) {
    $sql = 'DELETE FROM articles_'.$idUser.' WHERE id = ?';
    $params = array($id);
    $response = Database::executeSql($sql, $params); # success:false|true, exception:<string>|null, result:null|false(sin datos pero exitoso)
    if ($response['success'] and $response['result'])
        $response['result'] = true; # false(sin datos) a true(exitoso)
    exit(json_encode($response));
}

function getCountArticles($idUser) {
    $sql = 'SELECT COUNT(id) count FROM articles_'.$idUser;
    $response = Database::executeSql($sql, null, false); // {success:false|true, exception:<string>|null, result:null|{'count':<int>}}  
    $response['result'] = intval($response['result']['count']);
    exit(json_encode($response));
}

?>
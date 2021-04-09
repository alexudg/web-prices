<?php

    if (!empty($_GET) and isset($_GET['fn'])) {
        //exit(json_encode($_GET));
        switch ($_GET['fn']) {
            case 'getUserId':
                getUserId($_GET['token']);
                break;
            case 'getArticles':
                getArticles($_GET['txt']);
                break;
            case 'getArticle':
                getArticle($_GET['id']);
                break;
        }
        exit();
    }
    else if (!empty($_POST) and isset($_POST['fn'])) {
        switch ($_POST['fn']) {
            case 'isNamePass':
                isNamePass($_POST['username'], $_POST['pass']);
                break;
        }
        exit();
    }

class Database {
    ### MySQL
    private const DRIVER   = 'mysql';
    private const PORT     = '3306';

    // localhost
    private const HOST     = '127.0.0.1'; 
    private const DBNAME   = 'db_prices';  
    private const USERNAME = 'root';
    private const PASSWD   = '';
    //*/

    private const CHARSET  = 'utf8';
    private const DSN      = self::DRIVER.':host='.self::HOST.';port='.self::PORT.';dbname='.self::DBNAME.';charset='.self::CHARSET;    
    private const OPTIONS  = array(
                        PDO::ATTR_TIMEOUT => 5, // in seconds
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_EMULATE_PREPARES => false, // evita que todo se trate como string 
                        //PDO::ATTR_STRINGIFY_FETCHES => false // no consulta string (no funciona)
                     );        
    
    public static function getConnection() {
        try {
            return new PDO(self::DSN, self::USERNAME, self::PASSWD, self::OPTIONS);                
        } catch (PDOException $e) {
            die('ERROR CON BASE DE DATOS: ' . $e->getMessage()); //utf8_encode($e->getMessage()));
        }
    }

    public static function executeSql($sql, $params = array()) {
        // echo '<br>params: ';
        // var_dump($params);
        $con = self::getConnection();
        $res = array();
        try {
            //echo '<br>$sql='.$sql;
            $sta = $con->prepare($sql); // consulta sin parametros $con->query()
            // foreach ($params as $key => $value) {
            //     $key++;
            //     echo '<br>$key='.$key.' $value='.$value;
            //     $sta->bindParam($key, $value); // (indice inicia en 1, value)
            // } 
            $sta->execute($params);

            $res = $sta->fetchAll(PDO::FETCH_ASSOC); // solo nombres de campos

            // foreach($con->query($sql, PDO::FETCH_ASSOC) as $row) {
            //     var_dump($row);
            // }
        } catch (PDOException $e) {
            die('ERROR CON LA BASE DE DATOS: ' . $e->getMessage());
        }        
        $con = null;
        // echo '<br>var_dump($res): ';
        // var_dump($res);
        // echo '<br>print_r($res): ';
        // print_r($res);
        return $res;
    }

    public static function getLastInsertId($table) {
        $sql = 'SELECT MAX(id) id FROM '.$table;
        $result = self::executeSql($sql); // (('id' => <int>))
        return $result[0]['id'];
    }
}

// testDb();
// function testDb() {
//     die(json_encode(Database::executeSql('SELECT * FROM users')));
// }

function generateToken($id) {
    $token = '';
    for ($i=0; $i < 8; $i++)
        $token .= dechex(rand(0, 15));
    // save
    $sql = 'UPDATE users SET token = ? WHERE id = ?';
    $params = array($token, $id);
    Database::executeSql($sql, $params);
    return $token;
}

function isNamePass($name, $pass) {
    $echo = array('result' => false, 'user' => null);
    $sql = 'SELECT id FROM users WHERE username = ?';
    $params = array($name);
    $result = Database::executeSql($sql, $params); // ()|(('id' => <int>))
    if (count($result) > 0) {
        // verify pass
        $id = $result[0]['id'];
        $sql = 'SELECT pass FROM users WHERE id = ?';
        $params = array($id);
        $result = Database::executeSql($sql, $params); 
        // pass ok?
        if (password_verify($pass, $result[0]['pass'])) { 
            $echo['result'] = true;
            // get token o generate
            $sql = 'SELECT token FROM users WHERE id = ?';
            $params = array($id);
            $result = Database::executeSql($sql, $params); // (('token' => ''|<8-digits>))
            $echo['user']['id'] = $id;
            if (strlen($result[0]['token']) == 8) 
                $echo['user']['token'] = $result[0]['token'];
            // generate token
            else 
                $echo['user']['token'] = generateToken($id);
        }
    }
    exit(json_encode($echo));
}

function getUserId($token) {
    $sql = 'SELECT id FROM users WHERE token = ?';
    $params = array($token);
    $result = Database::executeSql($sql, $params);
    $id = null;
    if (count($result) > 0)
        $id = $result[0]['id'];
    die(json_encode(array('id' => $id)));
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
            if (gettype(strpos($value->description, $txt)) == 'integer') #objeto no array
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

?>
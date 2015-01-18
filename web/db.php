<?php

define("MONGODB_DATABASE", 'ntunetsec2014fall');
define("MONGODB_USER_COLLECTION", 'User');
define("MONGODB_RECORD_COLLECTION", 'Record');

class DB {
    private $self = array();

    private $connection = NULL;
    private $database = NULL;
    private $userCollection = NULL;
    private $recordCollection = NULL;

    // Class constructor
    public function __construct($param) {
        // Setup default variables
        $this->self['dbUrl']  = getenv('MongoURL');
        $this->self['dbUser'] = getenv('MongoUser');
        $this->self['dbPass'] = getenv('MongoPass');
        $this->self['dbName'] = getenv('MongoDB');

        // User-defined variables
        if (is_array($param)) {
            if (isset($param['url'])) {
                $this->self['dbUrl'] = $param['url'];
            }
            if (isset($param['user'])) {
                $this->self['dbUser'] = $param['user'];
            }
            if (isset($param['pass'])) {
                $this->self['dbPass'] = $param['pass'];
            }
            if (isset($param['database'])) {
                $this->self['dbName'] = $param['database'];
            }
        }
    }

    // Database initialization
    public function init() {
        // Connect to mongodb
        echo "test1";
        $this->connection = new MongoClient( $this->self['dbUrl'], [
            'username' => $this->self['dbUser'],
            'password' => $this->self['dbPass'],
            'db'       => $this->self['dbName']
        ]);
        echo "test2";
        // Choose database and collection
        if($this->connection) {
            $this->database = ($this->connection)
                ->MONGODB_DATABASE;
            echo "test3";
            $this->userCollection = ($this->database)
                ->MONGODB_USER_COLLECTION;
            echo "test4";
            $this->recordCollection = ($this->database)
                ->MONGODB_RECORD_COLLECTION;
            echo "test5";
            var_dump(($this->userCollection)->findOne());
        }
        echo "connection has been setup!\n";
    }

    // Database termination
    public function close() {
        // Close mongodb connection
        if($this->connection) {
            $closed = $connection->close();
            echo "connection has been terminated!\n";
        }
    }
}

echo "new db object\n";
$db= new DB();
echo "connect to db\n";
$db->init();
echo "close connection\n";
$db->close();

?>

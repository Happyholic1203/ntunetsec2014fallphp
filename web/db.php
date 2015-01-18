<?php

class DB {

    private $self = array();

    private $mongodb_database = 'ntunetsec2014fall';
    private $mongodb_user_collection = 'User';
    private $mongodb_record_collection = 'Record';

    private $connection = NULL;
    private $database = NULL;
    private $userCollection = NULL;
    private $recordCollection = NULL;

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
            echo "test3";
            $this->database = $this->connection->$mongodb_database;
            $this->userCollection = $this->connection->
                $mongodb_database->$mongodb_user_collection;
            $this->recordCollection = $this->connection->
                $mongodb_database->$mongodb_record_collection;
            $document = $this->userCollection->findOne();
            var_dump( $document );
        }
        echo "connection has been setup!\n";
    }

    public function close() {
        // Close mongodb connection
        if($this->connection) {
            $closed = $connection->close();
            echo "connection has been terminated!\n";
        }
    }

}

echo "new db object\n";
$test = new DB();
echo "connect to db\n";
$test->init();
echo "close connection\n";
$test->close();

?>

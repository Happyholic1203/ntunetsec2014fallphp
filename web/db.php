<?php

define("MONGODB_DATABASE", 'ntunetsec2014fall');
define("MONGODB_USER_COLLECTION", 'User');
define("MONGODB_RECORD_COLLECTION", 'Record');

class MONGO {
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

        $this->connection = new MongoClient( $this->self['dbUrl'], [
            'username' => $this->self['dbUser'],
            'password' => $this->self['dbPass'],
            'db'       => $this->self['dbName']
        ]);

        // Choose database and collection
        if($this->connection) {
            $this->database = $this->connection
                ->selectDB(MONGODB_DATABASE);
            $this->userCollection = $this->database
                ->selectCollection(MONGODB_USER_COLLECTION);
            $this->recordCollection = $this->database
                ->selectCollection(MONGODB_RECORD_COLLECTION);
        }
    }

    // Database termination
    public function close() {
        // Close mongodb connection
        if($this->connection) {
            $this->$connection->close();
        }
    }

    public function dump() {
        $cursor = $this->userCollection->find();
        foreach ( $cursor as $id => $value )
        {
            echo "$id: ";
            var_dump( $value );
            // why? no results?
        }
    }

}

echo "new db object\n";
$db = new MONGO();
echo "connect to db\n";
$db->init();
echo "dump all data\n";
$db->dump();
echo "close connection\n";
$db->close();

?>

<?php

define("MONGODB_DATABASE", 'ntunetsec2014fall');
define("MONGODB_USER_COLLECTION", 'User');
define("MONGODB_RECORD_COLLECTION", 'Record');

class MongoClass {
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
            if (isset($param['url']))
                $this->self['dbUrl'] = $param['url'];

            if (isset($param['user']))
                $this->self['dbUser'] = $param['user'];

            if (isset($param['pass']))
                $this->self['dbPass'] = $param['pass'];

            if (isset($param['database']))
                $this->self['dbName'] = $param['database'];
        }
        echo "<div>>new db object initlization completed</div>";
    }

    // Initializes database connection
    public function init() {
        // Connect to mongodb
        $this->connection = new MongoClient( $this->self['dbUrl'], [
            'username' => $this->self['dbUser'],
            'password' => $this->self['dbPass'],
            'db'       => $this->self['dbName']
        ]);

        // Choose database and collection
        if ($this->connection) {
            echo "<div>>connect to database successfully</div>";
            $this->database = $this->connection
                ->selectDB(MONGODB_DATABASE);
            $this->userCollection = $this->database
                ->selectCollection(MONGODB_USER_COLLECTION);
            $this->recordCollection = $this->database
                ->selectCollection(MONGODB_RECORD_COLLECTION);
            echo "<div>>choose database and collections</div>";
        }
    }

    // Terminates database connection
    public function close() {
        // Close mongodb connection
        if ($this->connection) {
            echo "test1";
            $closed = $this->$connection->close(TRUE);
            echo "test2";
            echo $closed ?
            "<div>>close database connection successfully</div>" :
            "<div>>failed to close database connection</div>";
        }
    }

    // Checks user email for registration
    public function isUserEmailOccupied($email) {
        $cursor = $this->userCollection->findOne(
            array('email' => $email ));

        if (!is_null($cursor))
            return TRUE;
        else
            return FALSE;
    }

    // Handles user registration
    public function userRegistration($registration) {
        if (is_array($registration) && count($registration) === 4) {
            $required = array('email', 'password', 'type', 'publickey');

            if (count(array_intersect_key(array_flip($required),
                $registration)) === count($required)) {
                $this->userCollection->insert($registration);

                echo "<div>>added a new user acoount</div>";
                // TBD return _id
                return TRUE;
            }
        }
        echo "<div>>bad request for user registration</div>";
        return FALSE;
    }

    // Handles user login authentication
    public function userLoginAuth($information) {
        if (is_array($information) && count($information) === 3) {
            $required = array('email', 'password', 'type');

            if (count(array_intersect_key(array_flip($required),
                $information)) === count($required)) {

                $cursor = $this->userCollection->findOne($information);

                if (is_null($cursor)) {
                    echo "<div>>bad request for user login</div>";
                    return FALSE;
                }
                else {
                    echo "<div>>user passed authentication</div>";
                    // TBD return _id
                    return TRUE;
                }
            }
        }
        echo "<div>bad request for user login</div>";
        return FALSE;
    }
}

$newUser = array(
    'email' => 'buyer2@example.com',
    'password' => 'test54321',
    'type' => 'buyer'
    //'publickey' => 'keytest54321key'
    );


echo "<div>new db object</div>";
$db = new MongoClass();
echo "<div>connect to db</div>";
$db->init();

/* Test user registration
echo "<div>check user email for registration</div>";
if(!$db->isUserEmailOccupied($newUser['email'])) {
    $db->userRegistration($newUser);
}
else
    echo "<div>user account exists.</div>";
*/

/* Test user login authentication*/
echo "<div>check user login authentication</div>";
if ($db->userLoginAuth($newUser)) {
    echo "<div>user authenticated successfully</div>";
}
else
    echo "<div>user authenticated unsuccessfully</div>";


echo "<div>close connection</div>";
$db->close();

?>

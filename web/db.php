<?php

/**
 * Declaration of constants for MongoDB
 */
define("MONGODB_DATABASE", 'ntunetsec2014fall');
define("MONGODB_USER_COLLECTION", 'User');
define("MONGODB_RECORD_COLLECTION", 'Record');

/**
 * A class for handling interaction with MongoDB
 *
 * @author Meng-Han John Tsai <mhtsai1010@gmail.com>
 * @version 1.0
 */
class MongoClass {
    private $self = array();

    private $connection = NULL;
    private $database = NULL;
    private $userCollection = NULL;
    private $recordCollection = NULL;

    /**
     * Class constructor
     * @param Array $param Consists of database variables, including url,
     * user, password, database name for connection
     */
    public function __construct($param) {
        // Setup default database variables
        $this->self['dbUrl']  = getenv('MongoURL');
        $this->self['dbUser'] = getenv('MongoUser');
        $this->self['dbPass'] = getenv('MongoPass');
        $this->self['dbName'] = getenv('MongoDB');
        echo "<div>>>new database object initlization is done</div>";

        // Setup user-defined variables
        if (is_array($param)) {
            if (isset($param['url']))
                $this->self['dbUrl'] = $param['url'];

            if (isset($param['user']))
                $this->self['dbUser'] = $param['user'];

            if (isset($param['pass']))
                $this->self['dbPass'] = $param['pass'];

            if (isset($param['database']))
                $this->self['dbName'] = $param['database'];
            echo "<div>>>update database object with user-defined info.</div>";
        }
    }


    /**
     * Initializes database connection
     * @return NULL
     */
    public function init() {
        // Connect to mongodb
        $this->connection = new MongoClient( $this->self['dbUrl'], [
            'username' => $this->self['dbUser'],
            'password' => $this->self['dbPass'],
            'db'       => $this->self['dbName']
        ]);

        // Choose predefined database and collections
        if ($this->connection) {
            echo "<div>>>connect to database successfully</div>";
            $this->database = $this->connection
                ->selectDB(MONGODB_DATABASE);
            $this->userCollection = $this->database
                ->selectCollection(MONGODB_USER_COLLECTION);
            $this->recordCollection = $this->database
                ->selectCollection(MONGODB_RECORD_COLLECTION);
            echo "<div>>>choosing database and collections is done</div>";
        }
        else
            echo "<div>>>failed to connect database</div>";
    }


    /**
     * Terminates database connection
     * @return [type] [description]
     */
    public function close() {
        // Close mongodb connection
        if ($this->connection) {
            $closed = $this->$connection->close(TRUE);
            echo $closed ?
            "<div>>close database connection successfully</div>" :
            "<div>>failed to close database connection</div>";
        }
    }

    // A helper function to check user email for registration
    private function isUserEmailOccupied($email) {
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

                if (!isUserEmailOccupied($registration['email'])) {
                    // Insert new 'User' document
                    $this->userCollection->insert($registration);

                    echo "<div>>added a new user acoount</div>";
                    echo "<div>>>_id: ".$registration['_id']."</div>";
                    // TBD return _id
                    return TRUE;
                }
            }
            echo "<div>>added a new user acoount unsuccessfully</div>";
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

                $cursor = $this->userCollection->findOne($information,
                    array('_id'));

                if (is_null($cursor)) {
                    echo "<div>>bad request for user login</div>";
                    return FALSE;
                }
                else {
                    echo "<div>>user passed authentication</div>";
                    echo "<div>>>_id: ".$cursor['_id']."</div>";
                    // TBD return _id
                    return TRUE;
                }
            }
            echo "<div>>unsuccessful user authentication</div>";
        }
        echo "<div>bad request for user login</div>";
        return FALSE;
    }

    // Handles collection of reward points
    public function userAddPoints($collection) {
        if (is_array($collection) && count($collection) === 5) {
            $required = array('buyerid', 'sellerid', 'action',
                'numPoints', 'timetamp');

            if (count(array_intersect_key(array_flip($required),
                $collection)) === count($required)) {

                // Insert new 'Record' document
                $this->recordCollection->insert($collection);

                echo "<div>>user added points successfully</div>";

                // Update buyer and seller points
                $availablePoints = handleUserPoints($collection);

                echo "<div>>>availablePoints: $availablePoints </div>";
                // TBD return availablePoints
                return TRUE;
            }
            echo "<div>>user added points unsuccessfully</div>";
        }
        echo "<div>>bad request for adding user points</div>";
        return FALSE;
    }

    // A helper function to validate request of points redemption
    private function validateRedeemRequest($request) {
        $buyerCurrentPoints = getUserAvailablePoints(
            $request['buyerid']);
        $sellerCurrentPoints = getUserAvailablePoints(
            $request['sellerid']);

        if ($buyerCurrentPoints  > int($request['numPoints']) &&
            $sellerCurrentPoints > int($request['numPoints'])) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    // Handle redemption of reward points
    public function userRedeemPoints($redemption) {
        if (is_array($redemption) && count($redemption) === 5) {
            $required = array('buyerid', 'sellerid', 'action',
                'numPoints', 'timetamp');

            if (count(array_intersect_key(array_flip($required),
                $redemption)) === count($required)) {

                if (validateRedeemRequest($redemption)) {
                    // Insert new 'Record' document
                    $this->recordCollection->insert($redemption);

                    echo "<div>>user redeemed points successfully</div>";

                    // Update buyer and seller points
                    $availablePoints = handleUserPoints($redemption);

                    echo "<div>>>availablePoints: $availablePoints </div>";
                    // TBD return availablePoints
                    return TRUE;
                }
            }
            echo "<div>>user redeemed points unsuccessfully</div>";
        }
        echo "<div>>bad request for redeeming user points</div>";
        return FALSE;
    }

    // A helper function to handle growth and decline of user points
    private function handleUserPoints($param) {
        if ($param['action'] === 'add') { // Collection
            // Update points
            $buyerAvailablePoints = increasePoints(
                $param['buyerid'], $param['numPoints']);
            $sellerPublishedPoints = increasePoints(
                $param['sellerid'], $param['numPoints']);

            return $buyerAvailablePoints; // return results to buyer
        }
        elseif ($param['action'] === 'redeem') { // Redemption
            // Update points
            $buyerAvailablePoints = decreasePoints(
                $param['buyerid'], $param['numPoints']);
            $sellerPublishedPoints = decreasePoints(
                $param['sellerid'], $param['numPoints']);

            return $sellerPublishedPoints; // return results to seller
        }
        else {
            // TBD: Error handler
        }
    }

    // A helper function to fetch user current points
    private function getUserAvailablePoints($id_string) {
        $mongo_id = new MongoID($id_string);
        $cursor = $this->userCollection->findOne(
            array('_id' => $mongo_id ));

        if (!is_null($cursor)) {
            return int($cursor['points']);
        }

        return 0;
    }

    // A helper function to increase user points
    private function increasePoints($id_string, $points) {
        $mongo_id = new MongoID($id_string);
        $userCurrentPoints = getUserAvailablePoints($id_string);
        $newAvailablePoints = int($currentPoints) + int($points);

        $this->userCollection->update(
            array('_id' => $mongo_id ),
            array('$set'=> array('points' => $newAvailablePoints))
            );
        return int($newAvailablePoints);
    }

    // A helper function to decrease user points
    private function decreasePoints($id_string, $points) {
        $mongo_id = new MongoID($id_string);
        $userCurrentPoints = getUserAvailablePoints($id_string);
        $newAvailablePoints = int($currentPoints) - int($points);

        $this->userCollection->update(
            array('_id' => $mongo_id ),
            array('$set'=> array('points' => $newAvailablePoints))
            );
        return int($newAvailablePoints);
    }
}

// MongoClass Testing Procedure
// testing data
$newBuyer = array(
    'email' => 'buyer3@example.com',
    'password' => 'testing0000',
    'type' => 'buyer',
    'publickey' => 'keytesting0000key'
    );
$newSeller = array(
    'email' => 'seller3@example.com',
    'password' => 'testing1111',
    'type' => 'seller',
    'publickey' => 'keytesting1111key'
    );

//
echo "<div>[Step 01] generate a new db object</div>";
$db = new MongoClass();
echo "<div>[Step 02] connect to database</div>";
$db->init();

/* Test user registration
echo "<div>check user email for registration</div>";
if(!$db->isUserEmailOccupied($newUser['email'])) {
    $db->userRegistration($newUser);
}
else
    echo "<div>user account exists.</div>";
*/

/* Test user login authentication
echo "<div>check user login authentication</div>";
if ($db->userLoginAuth($newUser)) {
    echo "<div>user authenticated successfully</div>";
}
else
    echo "<div>user authenticated unsuccessfully</div>";
*/

echo "<div>[Final  ] close database connection</div>";
$db->close();

?>

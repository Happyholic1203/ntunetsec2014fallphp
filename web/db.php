<?php

/**
 * Declaration of constants for MongoDB
 */
define("MONGODB_DATABASE", 'ntunetsec2014fall');
define("MONGODB_USER_COLLECTION", 'User');
define("MONGODB_RECORD_COLLECTION", 'Record');
define("DEBUG", TRUE);

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
     * @param array $param Consists of database variables, including url,
     *                     username, password, database name for connection.
     */
    public function __construct($param) {
        // Setup default database variables
        $this->self['dbUrl']  = getenv('MongoURL');
        $this->self['dbUser'] = getenv('MongoUser');
        $this->self['dbPass'] = getenv('MongoPass');
        $this->self['dbName'] = getenv('MongoDB');

        if (DEBUG) {
            echo "<div>>>new database object initlization is done</div>";
        }

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

            if (DEBUG) {
                echo "<div>>>update database object with user-defined info.".
                     "</div>";
            }
        }
    }


    /**
     * Initializes database connection
     * @return NULL No return value.
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
            if (DEBUG) {
                echo "<div>>>connect to database successfully</div>";
            }
            $this->database = $this->connection
                ->selectDB(MONGODB_DATABASE);
            $this->userCollection = $this->database
                ->selectCollection(MONGODB_USER_COLLECTION);
            $this->recordCollection = $this->database
                ->selectCollection(MONGODB_RECORD_COLLECTION);
            if (DEBUG) {
                echo "<div>>>choosing database and collections is done</div>";
            }
        }
        else {
            if (DEBUG) {
                echo "<div>>>failed to connect database</div>";
            }
        }
    }


    /**
     * Terminates database connection
     * @return NULL No return value.
     */
    public function close() {
        // Close mongodb connection
        if ($this->connection) {
            $closed = $this->$connection->close(TRUE); //TBD: potential error
            if (DEBUG) {
                echo $closed ?
                "<div>>>close database connection successfully</div>" :
                "<div>>>failed to close database connection</div>";
            }
        }
    }


    /**
     * A helper function to check user email for registration
     * @param  string  $email User email address.
     * @return boolean        Returns true if the email address is not
     *                                occupied, otherwise returns false.
     */
    private function isUserEmailOccupied($email) {
        $cursor = $this->userCollection->findOne(
            array('email' => $email ));

        if (!is_null($cursor)) return TRUE;

        return FALSE;
    }


    /**
     * Handles user registration
     * @param  array $registration User information for registration.
     * @return string              Returns new user id if registration is
     *                                     successful, otherwise returns false.
     */
    public function userRegistration($registration) {
        // Check input registration array
        if (is_array($registration) && count($registration) === 4) {
            $required = array('email', 'password', 'type', 'publickey');

            // Check input registration array keys
            if (count(array_intersect_key(array_flip($required),
                $registration)) === count($required)) {

                // Check user email address
                if (!$this->isUserEmailOccupied($registration['email'])) {

                    if (DEBUG) {
                        echo "<div>>>new user email address is verified".
                             "</div>";
                    }
                    // Add basic user points
                    $registration['points'] = '0';

                    // Insert a new 'User' document
                    try {
                        $result = $this->userCollection->insert($registration,
                            array("w" => 1));
                    }
                    catch (MongoCursorException $e) {
                        if (DEBUG) {
                            echo "<div>>>operation error: $e</div>";
                        }
                    }

                    // Check operation result
                    if (is_null($result['err'])) {
                        if (DEBUG) {
                            echo "<div>>>added a new user acoount</div>";
                        }
                        return $registration['_id'];
                    }
                    else {
                        if (DEBUG) {
                            echo "<div>>>failed to add a new user acoount".
                                 "</div>";
                            echo "<div>>>error message: ".$result['err'].
                                 "</div>";
                        }
                    }
                }
            }
            if (DEBUG) {
                echo "<div>>>unsuccessful to register a new user</div>";
            }
        }
        else {
            if (DEBUG) {
                echo "<div>>>bad request for user registration</div>";
            }
        }
        return FALSE;
    }


    /**
     * Handles user login authentication
     * @param  array $information User information for login.
     * @return string             Returns user id if authentication is
     *                                    successful, otherwise returns false.
     */
    public function userLoginAuth($information) {
        // Check input information array
        if (is_array($information) && count($information) === 3) {
            $required = array('email', 'password', 'type');

            // Check input information array keys
            if (count(array_intersect_key(array_flip($required),
                $information)) === count($required)) {

                // Check whether user exists or not
                try {
                    $result = $this->userCollection->findOne($information,
                        array('_id'));
                }
                catch (MongoConnectionException $e) {
                    if (DEBUG) {
                        echo "<div>>>operation error: $e</div>";
                    }
                }

                // Check operation result
                if (!is_null($result)) {
                    if (DEBUG) {
                        echo "<div>>>user passed authentication</div>";
                    }
                    return $result['_id'];
                }
                else {
                    if (DEBUG) {
                        echo "<div>>>invalid request for user authentication".
                             "</div>";
                    }
                }
            }
            if (DEBUG) {
                echo "<div>>>unsuccessful user authentication</div>";
            }
        }
        else {
            if (DEBUG) {
                echo "<div>>>bad request for user authentication</div>";
            }
        }
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
/* Testing user data
///
$newBuyer = array(
    'email' => 'buyer4@example.com',
    'password' => 'testing4444',
    'type' => 'buyer',
    'publickey' => 'keytesting4444key'
    );
//*/
$newSeller = array(
    'email' => 'seller4@example.com',
    'password' => 'testing4444',
    'type' => 'seller',
    'publickey' => 'keytesting4444key'
    );
$loginUser = array(
    'email' => 'buyer1@example.com',
    'password' => 'testing4444',
    'type' => 'buyer'
    );

/* Testing database connection
*///
echo "<div>[Step 01] generate a new db object</div>";
$db = new MongoClass();
echo "<div>[Step 02] connect to database</div>";
$db->init();
//*/

/* Testing user registration
///
echo "<div>[Step 03] user registration</div>";
$uid = $db->userRegistration($newBuyer);
if ($uid)
    echo "<div>>>new user id is: ".$uid."</div>";
else
    echo "<div>>>mail address existed or bad request</div>";
//*/

/* Test user login authentication
*///
echo "<div>[Step 04] user login authentication</div>";
$uid = $db->userLoginAuth($loginUser);
if ($uid)
    echo "<div>>>successful user authentication</div>";
else
    echo "<div>>>unsuccessful user authentication</div>";
//*/

/* Test collection of reward points
///
TBD
//*/

/* Test redemption of reward points
///
TBD
//*/

echo "<div>[Final Step] close database connection</div>";
$db->close();

?>

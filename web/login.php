<?php

define('DEBUG', FALSE);
define('TESTING', FALSE);
if (TESTING) { // Testing HTTP POST method handler
?>
<div>[INPUT FORM]</div>
<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    email:<input type="text" name="email" value="buyer5@example.com"><br>
    password:<input type="text" name="password" value="55555"><br>
    type:<input type="text" name="type" value="buyer"><br>
    <input type="submit" value="submit" name="Click">
</form>
<div>[TESTING]</div>
<?php
}

/**
 * Requires MongoClass
 */
require_once('./db.php');

$response = array(
    'status' => 'rejected',
    'id'     => NULL,
    'cert'   => NULL
    ); // response message

// Checks whether a request is GET or POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User posts something
    if (!empty($_POST)) {
        if (DEBUG) {
            echo "<div>>>received user input data: ".json_encode($_POST).
                 "</div>"; // Show user inputs
        }
        if (TESTING) unset($_POST['Click']);

        // Check POST fields
        $required = array('email', 'password', 'type');
        if (count(array_intersect_key(array_flip($required),
                $_POST)) === count($required)) {

            // Validates email address
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

                // Validates user type
                $type = $_POST['type'] ?: 0;
                if ($type === 'buyer' || $type === 'seller') {
                    // User authentication
                    $db = new MongoClass();
                    $db->init();
                    $result = $db->userLoginAuth($_POST);

                    // set response
                    if ($result) {
                        $response['status'] = 'ok';
                        $response['id'] = $result;
                        $response['cert'] = getServerCertificats(
                            $result, $type, $db->getUserPublickey($result));
                    }
                }
            }
        }
    }
    if (TESTING) {
?>
<br />
<div>[OUTPUT JSON]</div>
<?php
    }
    echo json_encode($response); // response message in JSON
    $db->close(); // TBD :error
} // end of POST checks
?>


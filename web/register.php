<?php

define('DEBUG', TRUE);
define('TESTING', TRUE);

if (TESTING) { // Testing HTTP POST method handler
?>
<div>[INPUT FORM]</div>
<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    email:<input type="text" name="email" value="buyer5@example.com"><br>
    password:<input type="text" name="password" value="55555"><br>
    type:<input type="text" name="type" value="jumper"><br>
    publickey:<input type="text" name="publickey" value="key55555key"><br>
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
        if (TESTING) unset($_POST['submit']);

        // Check POST fields
        $required = array('email', 'password', 'type', 'publickey');
        if (count(array_intersect_key(array_flip($required),
                $_POST)) === count($required)) {

            // Fetches user input email address
            $email = $_POST['email'] ?: 0;
            if ($email) {

                // Validates email address
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    if (DEBUG) {
                        echo "<div>>>validated user input email successfully: "
                             .$email."</div>"; // Show user email
                    }

                    // Validates user type
                    $type = $_POST['email'] ?: 0;
                    if ($type === 'buyer' || $type === 'seller') {

                        // User registration
                        $db = new MongoClass();
                        $db->init();
                        $result = $db->userRegistration($_POST);
                        $db->close();

                        // set response
                        if ($result) {
                            $response['status'] = 'ok';
                            $response['id'] = $result;
                            $response['cert'] = getServerCertificats(
                                $result, $type, $_POST['publickey']);
                            if (DEBUG) {
                                echo "<div>>>successful user registration".
                                     "</div>";
                            }
                        }
                        else {
                            if (DEBUG) {
                                echo "<div>>>user registration failed</div>";
                            }
                        }
                    }
                    else {
                        if (DEBUG) {
                            echo "<div>>>bad request with user input 'type' ".
                                 "field</div>";
                        }
                    }
                }
                else {
                    if (DEBUG) {
                        echo "<div>>>failed to validate user input email: ".
                             $email."</div>";
                    }
                }
            }
            else {
                if (DEBUG) {
                    echo "<div>>>bad request with user input 'email' field".
                         "</div>";
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
} // end of POST checks
?>

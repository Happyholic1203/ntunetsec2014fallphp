<?php
if (TESTING) {// Testing HTTP POST method handler
?>
<div>[INPUT FORM]</div>
<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    <input type="text" name="email" value="buyer5@example.com"><br>
    <input type="text" name="password" value="55555"><br>
    <input type="text" name="type" value="buyer"><br>
    <input type="text" name="publickey" value="key55555key"><br>
    <input type="submit" value="submit" name="Click">
</form>
<div>[OUTPUT JSON]</div>
<?php
}

define('DEBUG', TRUE);
define('TESTING', TRUE);

/**
 * Requires MongoClass
 */
require_once('./db.php');

$response = array(); // response message

// Checks whether a request is GET or POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User posts something
    if (!empty($_POST)) {
        if (DEBUG) {
            echo "<div>>>received user input data:<br />";
            echo json_encode($_POST)."</div>"; // Show user inputs
        }
        // Fetches user input email address
        $email = $_POST['email'] ?: 0;
        if ($email){
            // validates email address
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if (DEBUG) {
                    echo "<div>>>validated user input email:<br />";
                    echo $email."</div>"; // Show user email
                }
            // query user exists or not
            // if user not exist, then register the user
            // output response
            }
        }
    }
    echo json_encode($response);
}
?>

<?php
require_once('./db.php');
define('TESTING', TRUE);

if (!empty($_POST))
{
    echo json_encode($_POST);
}
else {
    echo "{ }";
}
?>

<?php
if (TESTING) {// Testing POST method handler
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    <input type="text" name="email" value="buyer5@example.com"><br>
    <input type="text" name="password" value="55555"><br>
    <input type="text" name="type" value="buyer"><br>
    <input type="text" name="publickey" value="key55555key"><br>
    <input type="submit" value="submit" name="Click">
</form>
<?php
}
?>

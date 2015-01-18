<?php
echo "before conn";

$connection = new MongoClient(getenv('MongoURL'),[
    'username' => getenv('MongoUser'),
    'password' => getenv('MongoPass'),
    'db'       => getenv('MongoDB')
]);

echo "after conn";
echo "check conn";
if ($connection) {
    $collection = $connection->database->collectionName;
    $document = $collection->findOne();
    var_dump( $document );
    $closed = $connection->close();
}
else {
    echo "damn";
}
echo "close conn";

?>

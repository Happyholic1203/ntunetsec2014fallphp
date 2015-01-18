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
    echo "why1";
    $collection = $connection->database->collectionName;
    echo "why2";
    $document = $collection->findOne();
    echo "why3";
    var_dump( $document );
    $closed = $connection->close();
}
else {
    echo "damn";
}
echo "close conn";

?>

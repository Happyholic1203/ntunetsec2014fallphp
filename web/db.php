<?php

$connection = new MongoClient(getenv('MongoURL'),[
    'username' => getenv('MongoUser'),
    'password' => getenv('MongoPass'),
    'db'       => getenv('MongoDB')
]);

if ($connection) {
    $collection = $connection->database->collectionName;
    $document = $collection->findOne();
    var_dump( $document );
    $closed = $connection->close();
}
else {
    echo "damn";
}

?>

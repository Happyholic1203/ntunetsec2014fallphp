<?php
echo "test1";
$connection = new MongoClient(getenv('MongoURL'),[
    'username' => getenv('MongoUser'),
    'password' => getenv('MongoPassword'),
    'db'       => getenv('MongoDB')
]);

echo getenv('MongoURL');
echo getenv('MongoUser');
echo getenv('MongoPassword');
echo getenv('MongoDB');

if ($connecion) {
    $collection = $connection->database->collectionName;
    $document = $collection->findOne();
    var_dump( $document );
    $closed = $connection->close();
}
else {
    echo "damn";
}

echo "test2";
?>

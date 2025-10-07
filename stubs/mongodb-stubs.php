<?php
namespace MongoDB;

/** @phpstub */
class Client {
    public function __construct(string $uri = "mongodb://127.0.0.1:27017", array $options = [], array $driverOptions = []) {}
    public function __get(string $name): Collection { return new Collection(); }
    public function selectDatabase(string $name): Database { return new Database(); }
}

/** @phpstub */
class Database {
    public function __get(string $name): Collection { return new Collection(); }
    public function selectCollection(string $name): Collection { return new Collection(); }
}

/** @phpstub */
class Collection {
    public function insertOne(array $document) {}
    public function find(array $filter = [], array $options = []) {}
    public function findOne(array $filter = [], array $options = []) {}
    public function updateOne(array $filter, array $update, array $options = []) {}
    public function deleteOne(array $filter, array $options = []) {}
}

# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2020-01-15
### Added
- ArangoDB v3.6 support.

## [1.0.0] - 2019-11-27
### Added
- Graphs support 
- Implemented  ``ArangoDB\Graph\Traversal\Traversal`` class to represent graphs traversals on server.
- Implemented  ``ArangoDB\Graph\Traversal\Path`` class to represent paths returned from traversals.
- All traversals are implemented on AQL statements, once the endpoint `/_api/traversal` was deprecated since version 3.4.0 of ArangoDB Server. See [Traversals](https://www.arangodb.com/docs/3.4/http/traversal.html) on ArangoDB Documentation for v3.4.x versions.

### Changed
- Minor fixes on `ArangoDB\Collection\Collection` class.

## [0.6.0-alpha] - 2019-10-31
### Added
- Import and Export features 
- Improve indexes representations - 
- Implemented ``ArangoDB\Collection\Index\PrimaryIndex`` and ``ArangoDB\Collection\Index\EdgeIndex`` classes. 
- Implemented ``ArangoDB\Collection\Collection::isGraph()``  method

### Changed
- Minor fixes on ``ArangoDB\AQL\QueryInterface`` interface.

## [0.5.0-alpha] - 2019-10-31

### Added
- Full collections indexes management 
- Implemented  ``ArangoDB\Collection\Index``, ``ArangoDB\Collection\FullTextIndex``, ``ArangoDB\Collection\GeoSpatialIndex``, ``ArangoDB\Collection\HashIndex``, ``ArangoDB\Collection\SkipListIndex``, ``ArangoDB\Collection\PersistentIndex``, and ``ArangoDB\Collection\TTLIndex`` classes to manage the suported indexes on ArangoDB.
- Recover all collection indexes calling ``ArangoDB\Collection\Collection::getIndexes()``.
- Create a new index on collection calling ``ArangoDB\Collection\Collection::addIndex()``.
- Drops a index on collection calling ``ArangoDB\Collection\Collection::dropIndex()``.
- Access to server time - ``ArangoDB\Admin::time()`` 

### Changed
- Minor fixes on ``ArangoDB\Collection\Collection`` class.

## [0.4.0-alpha] - 2019-10-24

### Added
 - Full AQL Functions management 
   - Implemented  ``ArangoDB\AQL\Functions\AQLFunctions`` class to manage individual user defined AQL Functions.
   - Recover all functions calling ``ArangoDB\AQL\AQL::functions()``.
 - Access to server log level configurations - ``ArangoDB\Server::logLevel()`` 
 - Access to server write-ahead log properties - ``ArangoDB\Admin::flushWal()`` 
 - Flush write-ahead log on server - ``ArangoDB\Admin::flushWal()`` 


### Changed
 - Improvements of ``ArangoDB\Admin\Admin`` unit tests.

## [0.3.0-alpha] - 2019-10-24

### Added
- Tasks management.

### Changed
 - Added a default constructor for ``ArangoDB\Validation\Validator`` class.
 - `EntityInterface` is now Json serializable.

## [0.2.0-alpha] - 2019-10-23

### Added
- Stream transactions support.

## [0.1.7-alpha] - 2019-10-22

### Added
- PHP 7.2+ support.
- Document basic operations (Create, Read, Update and Delete)
- Key-store support (through Document class)
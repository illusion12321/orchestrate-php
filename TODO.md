# TODO and IDEAS

- Review the use case of turning KeyValue array access to magic getters, so Phalcon views can be fed easier. Or even have a toObject()? ... still the best is to have a typed Class, not an abstract object, for that we have Arrays
** Added the get/setters, now will consider to allow both getters and arrayAccess to read restricted properties like 'key', 'ref' etc... **

- Add totalCount method to Collection?

- Maybe even a getAll to KeyValues?

- Add a getFirst to Collection/Application?

- Change some direct var accesss ($this->key) to the respective getter ($this->getKey(), $this->setValue()), but review carefully because it's not good for all occasions, like the reset method

- Inline Docs

- Work on Query builders for Search (search/sort/aggregate) and Range (list collection and events)??

- Reconsider adding methods of getEvent, postEvent, etc, to KeyValue?

- Implement some common interfaces, and general organization of the base classes

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Implement async operations?

- Study the advantages of turning all objects to resources like: $application['collection']['key'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources

- The List results should be associative array? With the keys? (could be nice to have array_keys()) -- confirm later, considering conflicts with the same key

- Add sort operations to List objects? basically just a map to PHP sort?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- method to move a KeyValue to another Collection or Application ?


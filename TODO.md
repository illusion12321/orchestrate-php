# TODO and IDEAS

- change some direct var accesss ($this->key) to the respective getter ($this->getKey(), $this->setValue()), but review carefully because it's not good for all occasions, like the reset method

- Inline Docs

- Reconsider adding methods of getEvent, postEvent, etc, to KeyValue?

- Work on Query builders for Search (search/sort/aggregate)?

- Implement Tests

- Implement some common interfaces, and general organization of the base classes

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Implement async operations?

- Study the advantages of turning all objects to resources like: $application['collection']['key'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources

- The List results should be associative array? With the keys? (could be nice to have array_keys()) -- confirm later, considering conflicts with the same key

- add sort operations to List objects?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- method to move a KeyValue to another Collection or Application ?


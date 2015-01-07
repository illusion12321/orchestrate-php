# TODO and IDEAS

- Implement some common interfaces, and general organization of the base classes

- Implement Tests

- Add Docs

- Implement async operations?

- Study the advantages of turning all objects to resources like: $application['collection']['key'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources

- The List results should be associative array? With the keys? (could be nice to have array_keys()) -- confirm later, considering conflicts with the same key

- add sort operations to List objects?

- Maybe later add a static Facade ? 'Orchestrate' as Application ?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- method to move KeyValue to another Collection or Application ?


# TODO and IDEAS

- Allow Collection/Application to change the list's children class! Specially important for Models.

- Rethink the Collection class, turning into a resource-like object, acting as a KeyValue storage, by key

- Study the advantages of turning all objects to resources like: $application['collection']['key']['some_property'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources

- Maybe move out the Client from the application, so every object would consistently have ->setClient — Client could extend Guzzle\Client and bring back the static getDefault()

- Study the best way to integrate Refs and Events into a KeyValue for easy access/management ? unite the KeyValues class with Search (turning into Collection), then move Refs and Events into KeyValue?

- Implement some common interfaces, and general organization of the base classes

- ! consider turning all objects paramaters to a single array!!?? -- long parameter list on constructor is too crazy to follow anyway...

- Work on Query builders for Search (search/sort/aggregate) and Range (list collection and events)??

- Add totalCount method to Collection or KeyValues?

- Maybe even a getAll to KeyValues?

- Add a getFirst to Collection/Application?

- Add an addNext addPrev to List, so we get the next set of results without erasing the last page?

- Inline Docs

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Implement async operations?

- Add sort operations to List objects? basically just a map to PHP sort?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- method to move a KeyValue to another Collection or Application?

- add serialize/unserialize? Review the use case. (unserialize could use init, and serialize toArray)

- event system?
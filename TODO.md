# TODO and IDEAS

- Allow Collection/Application to change the list's children class! Specially important for Models.

- Maybe move out the Client from the application, so every object would consistently have ->setClient — Client could extend Guzzle\Client and have an interface, so can easily be swaped

- Implement some common interfaces, and general organization of the base classes

- ! consider turning all objects paramaters to a single array!!?? -- long parameter list on constructor is too crazy to follow anyway...

- Work on Query builders for Search (search/sort/aggregate) and Range (list collection and events)??

- Add totalCount method to Collection or KeyValues?

- Maybe even a getAll to KeyValues?

- Add a getFirst to Collection/Application?

- Add an addNext addPrev to List, so we get the next set of results without erasing the last page?

- Inline Docs

- Reconsider adding methods of getEvent, postEvent, etc, to KeyValue?

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Implement async operations?

- Study the advantages of turning all objects to resources like: $application['collection']['key']['some_property'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources

- Add sort operations to List objects? basically just a map to PHP sort?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- method to move a KeyValue to another Collection or Application?


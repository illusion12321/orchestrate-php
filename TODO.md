# TODO and IDEAS

!!!
- Reconsider the naming of the method 'listCollection' to 'getValues'? — maybe even turn all listEvents, listRefs, to 'get', or 'getList' — and then 'Search->search' to 'Search->query' or even 'Search->collection'
--- talvez get mesmo! ai no search query

- Consider to move the putRelation and deleteRelation in its own object. Doesn't make a straight reason to create a KeyValue object just to put a relation, and sometimes suggest calling a get API before doing the putRelation

- still consider to remove listRelations / listRefs / listEvents, it's confusing to sometimes return self, other times, completely different values — I got myself reading the success in the current KeyValue object



- Study the advantages of turning all objects to resources like: $application['collection']['key'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources


- The List results should be associative array? With the keys? (could be nice to have array_keys()) -- confirm later, considering conflicts with the same key

- Maybe (very) later apply a static Facade ? 'Orchestrate' (Application) ?

- add sort operations to List objects?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- check the 'archival' property of the ruby client

- method to move KeyValue to another Collection and Application ?

- later test how the Abstract classes works on arguments, and implement some common interfaces for response, etc


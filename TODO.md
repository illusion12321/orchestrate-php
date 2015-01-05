# TODO and IDEAS

!!!
- Reconsider the naming of the method 'listCollection' to 'getValues'? — maybe even turn all listEvents, listRefs, to 'get', or 'getList' — and then 'Search->search' to 'Search->query' or even 'Search->collection'
--- talvez get mesmo! ai no search query

- Consider to move the putRelation and deleteRelation in its own object. Doesn't make a straight reason to create a KeyValue object just to put a relation, and sometimes suggest calling a get API before doing the putRelation


- Study the advantages of turning all objects to resources like: $application['collection']['key'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources

- Try to remove the Application parameter and simplify the others
    // sometimes it's interesting to instantiate these objects directly, to populate with data then send
setStatic() // enableStatic / disableStatic?
getStatic()

- The List results should be associative array? With the keys? (could be nice to have array_keys()) -- confirm later, considering conflicts with the same key

- Maybe (very) later apply a static Facade ? 'Orchestrate' (Application) ?

- add sort operations to List objects?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- check the 'archival' property of the ruby client

- method to move KeyValue to another Collection and Application ?

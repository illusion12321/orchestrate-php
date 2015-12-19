# TODO and IDEAS   

- Upgrade Relation class with value support. As well as RelationInterface and Collection setRelationClass. Probably rename to Relationship to match Orchestrate.

- Review the deprecation of Events and Relations?

- Maybe add an 'newInstance' (or clone) method to each Object, to make it easier to create a empty instance to work on. Probably it should come pre-set with the same name/collection.

- On Application object, add method to setCollectionClass, and allow to set many, by collection name.

- Study the removal of several functions parameters: Conditionals would turn to method names like "putIf()", "putIfNone"

- Maybe change 'patchMerge' on KeyValue to just 'merge'

- Study the removal of the 'value' parameter from put and post, to favor the object syntax. Review the use cases, considering a ODM that would use a 'save' method

- Study the removal of the constructor params in favor of a single array or string — if array use init / if string consider as path and split accordingly

- Maybe move the Properties to the Common folder, to better organize, maybe go ahead and do that on some interfaces too

- Remove the Client class as we know it, turning it into an Operations constructor — we should to create api operations, then use at will: execute on Pools, async, etc..
- Consider renaming Collection/Events 'get' method to their client counterpart (review the Client class for ideas)

- Work on Search Query builder (query + sort + aggregate builder, then be used on collection->query($queryBuilder))
---Do not use the object itself to build the query chain, it's confusing---

- Provide a quicker access to query builders by allowing regular arrays, which get passed to a init on each query builder

- MAYBE go ahead and add the collection->my_item map.. that loads the item with get already? OK, but should implement an internal cache right?
. application->my_collection->my_item
. application->collection('my_collection')->item('my_item')
. application->item('my_collection', 'my_item')
. Note that collection->my_item should return null when it doens't exist, because of implementations could favor that: Model::get($id) ?? $default_model;

- Add feature of getting lists above the limit of 100, even passing -1 to get entire list (pages loading will happen in background)

- Implement more common interfaces, and general organization of the classes / folders (create an AbstractCollection, to gather all common parts of searchable lists: collection/events)

- could have find/search/findFirst method on KeyValue to search the collection and load the first match.

- Collection could follow too? findFirst?

- Implement a better pagination (study AWS 3), including an option to automatically load the next pages when doing a iteration (foreach)

- Maybe implement __toString and __debugInfo for debug pourposes. But study the best pattern before release, so it doesn't change later. __toString could print some like the fully qualified path

- add a pingMessage method, for debugging?

- Add an appendNextPage appendPrevPage to List, so we get the next set of results without erasing the last page?

- Inline Docs

- Improve the Guzzle implementation, and add parallel and async operations

- Add Cache interface right!?

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Add sort operations to List objects?

- method to move an item to another Collection or even Application?

- Event system?

- Implement Guzzle logger interface?

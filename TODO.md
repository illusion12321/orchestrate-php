# TODO and IDEAS   

- Docs on Graph Get and Graph conditionals, check for client api change as well.

- Maybe add an 'newInstance' (or clone) method to each Object, to make it easier to create a empty instance to work on. Probably it should come pre-set with the same name/collection.

- Study the removal of the constructor params in favor of a single array or string — if array use init / if string consider as path and split accordingly

- Add relationships(), relationship() methods to Collection (and probably Application)

- Work on Search Query builder (query + sort + aggregate builder, then be used on collection->query($queryBuilder)) or better, use collection->search('query*', $options), where $options = (new Orchestrate/Query/SearchOptions())->limit(10)->sort('title')
- scheme: app->query(‘*')->sort(’title:asc')->get(20) — ou ->find() ou ->send() ou ->search()
- scheme: app->search(‘*’, [’sort’ => ’title:asc’, ‘limit’ => 20]]);
- scheme: listItems()->range()->limit()->get() (maybe always end in 'get')
---Do not use the object itself to build the query chain, it's confusing---

- Study the removal of several functions parameters

- Provide a quicker access to query builders by allowing regular arrays, which get passed to a init on each query builder! I.E. collection->search('query*', ['limit' => 10, 'sort' => 'title'])

- Consider renaming Collection/Events 'get' method to their client counterpart (review the Client class for ideas)

- Implement Bulk operations support 

- On Application object, add method to setCollectionClass, and allow to set many, by collection name?

- getReftime could automatically load the reftime if not provided? only if required, to not make API calls without the user knowing

- Maybe change 'patchMerge' on KeyValue to just 'merge'

- Remove the Client class as we know it, turning it into an Operations constructor — we should to create api operations, then use at will: execute on Pools, async, etc..

- Add feature of getting lists above the limit of 100, even passing -1 to get entire list (pages loading will happen in background)

- Consider BLOB storage support?

- could have find/search/findFirst method on KeyValue to search the collection and load the first match?

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

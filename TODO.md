# TODO and IDEAS

- MAYBE remove value parameter from put and post, to favor the object syntax? Review the use cases, considering a ODM that would use a 'save' method

- Add feature of getting lists above the limit of 100, even passing -1 to get entire list (pages loading will happen in background)

- Work on Search Query builder (query + sort + aggregate builder, then be used on collection->query($queryBuilder))
---Do not use the object itself to build the query chain, it's confusing---

- Provide a quicker access to query builders by allowing regular arrays, which get passed to a init on each query builder

- Implement more common interfaces, and general organization of the classes / folders (create an AbstractCollection, to gather all common parts of searchable lists: collection/events)

- could have find/search/findFirst method on KeyValue to search the collection and load the first match.

- Collection could follow too? findFirst?

- Implement a better pagination (study AWS 3), including an option to automatically load the next pages when doing a iteration (foreach)

- Maybe implement __toString and __debugInfo for debug pourposes. But study the best pattern before release, so it doesn't change later

- add a pingMessage method, for debugging?

- Maybe even a getAll to Collection? getFirst?

- Add an appendNextPage appendPrevPage to List, so we get the next set of results without erasing the last page?

- Inline Docs

- Improve the Guzzle implementation, and add parallel and async operations

- Add Cache interface right!?

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Add sort operations to List objects? basically just a map to PHP sort?

- method to move an item to another Collection or even Application?

- Event system?

- Implement Guzzle logger interface?
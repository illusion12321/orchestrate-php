# TODO and IDEAS

- Move setItemClass to traits to complete the implementation of the Client class.

- Study the best pattern to include the Events Search, including thoughts on the new $itemKind internal var

- Will Events handle both items and events like Collection? Should them merge together? Should we merge Refs too? (not good to merge...)

- KeyValue should support setEventClass after all.


- Implement more common interfaces, and general organization of the classes / folders

- Work on Search Query builder (query + sort + aggregate builder, then be used on collection->query($queryBuilder))
---Do not use the object itself to build the query chain, it's confusing---

- Provide a quicker access to query builders, for instance $item->patchBuilder()->add(...)->copy(...)->patch(); ?

- could have find/search/findFirst method on KeyValue to search the collection and load the first match.
- Collection could follow too? findFirst?

- Implement a better pagination (study AWS 3), including an option to automatically load the next pages when doing a iteration (foreach)

- Maybe implement __toString and __debugInfo for debug pourposes. But study the best pattern before release, so it doesn't change later

- Maybe even a getAll to Collection? getFirst?

- Add an appendNextPage appendPrevPage to List, so we get the next set of results without erasing the last page?

- Inline Docs

- Improve the Guzzle implementation, and add async operations

- Add Cache interface right!?

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Add sort operations to List objects? basically just a map to PHP sort?

- method to move an item to another Collection or even Application?

- Event system?

- Implement Guzzle logger interface?
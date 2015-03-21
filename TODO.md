# TODO and IDEAS

- do the best to handle gracefully user defined getters/setters

--- if I map property that is get only, what should happen if we set it with $this->myGetOnlyProp ? vai ter que dar exception!? testar mais, parece que do jeito que está está correto



- Implement more common interfaces, and general organization of the classes / folders

- Work on Search Query builder (query + sort + aggregate builder, then be used on collection->query($queryBuilder))
---Do not use the object itself to build the query chain, it's confusing---

- update init/toArray of List objects with more data.. relation for instance

- Implement a better pagination (study AWS 3), including an option to automatically load the next pages when doing a iteration (foreach)

- Maybe implement __toString and __debugInfo for debug pourposes. But study the best pattern before release, so it doesn't change later

- Add totalCount method to Collection or KeyValues?

- Maybe even a getAll to Collection? getFirst?

- Add an appendNextPage appendPrevPage to List, so we get the next set of results without erasing the last page?

- Inline Docs

- Improve the Guzzle implementation, and add async operations

- Add Cache interface right!?

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Add sort operations to List objects? basically just a map to PHP sort?

- method to move an item to another Collection or even Application?

- add serialize/unserialize? Review the use case. (unserialize could use init, and serialize toArray)

- Event system?

- Implement Guzzle logger interface?
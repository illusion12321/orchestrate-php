# TODO and IDEAS

- Integrate Refs and Events directly into a KeyValue for easy access/management:

Some API like:
```php
$item->relations('kind')->get()
$item->relation('kind')->put($item)
$item->relation('kind')->delete()

$item->events('type')->get() // get list
$item->event('type')->get()
$item->event('type')->put()
$item->event('type')->post()
$item->event('type')->delete()
$item->event('type')->purge()

$item->refs()->get()
```

- Review the naming of the properties 'kind' and 'type' — too weak and 'kind' conflicts with toArray

- Consider removing post from KeyValue?

- Implement a better pagination (study AWS 3), including an option to automatically load the next pages when doing a iteration (foreach)

- Check out https://github.com/jmespath/jmespath.php

- Implement some common interfaces, and general organization of the base classes

- Work on Query builders (query builder (search) / sort / aggregate builder / range builder

- Add totalCount method to Collection or KeyValues?

- Maybe even a getAll to Collection? getFirst?

- Add an appendNextPage appendPrevPage to List, so we get the next set of results without erasing the last page?

- Inline Docs

- Improve the Guzzle implementation, and add async operations

- Add Cache interface right!?

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Add sort operations to List objects? basically just a map to PHP sort?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- method to move a KeyValue to another Collection or Application?

- add serialize/unserialize? Review the use case. (unserialize could use init, and serialize toArray)

- Later, can a 'toView' method bring something new, to help template engines or views? (getValue suffices for most cases)

- Event system?

- Implement Guzzle logger interface?
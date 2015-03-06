# CHANGELOG

## HEAD
- Radical change on classes, Application is now rightfully 'Client'.
- Application is defined as the entry point to the object API.
- Collection is defined as an Object, merging KeyValues (list query) and Search classes.
- Ref and SearchResult merged into KeyValue, to favor custom child classes.
- Added methods to change the child classes generated, for example, if you extend KeyValue to create your model, you can tell a Collection to always use your class instead of the default KeyValue.

## 0.7.0 - 2015-02-28
- KeyValue stores values directly in itself, making it a snap to use as Models!
- Introduces the ObjectArray, a class that makes data accessible either via object syntax or array syntax.
- Added methods to merge one object into another (merge search results, merge items Value).
- All object's operations now return boolean of the success, i.e. $keyValue->get(), $keyValue->put(). Application and Collection still returns the respective object instance.
- Objects now have a handy toJson method.

## 0.6.0 - 2015-02-04
- Removed the automatic client linking for the Objects, it has to be linked manually with setClient(). It would create conflicts on larger systems, where multiple clients could be created anywhere.
- The Collection now has a __toString, to get the collection name
- Many getters that has required values for the API calls now has the option to throw errors
- General code cleanup and inline docs improvements

## 0.5.0 - 2015-01-08

- Implemented Patch operations!
- Full API parity
- Now road is clear to optimizations, Docs and Tests

## 0.4.0 - 2015-01-08

- Implemented search Aggregates
- Added KeyValue subclasses (Ref and SearchResult)

## 0.3.0 - 2015-01-06

- Full API parity (excluding only Patch operations)
- Graph implemented
- Cleaned project further

## 0.2.0-alpha - 2015-01-04

- Implemented List, Refs and Events
- Added pagination methods for all List operations (search, events, refs)
- Cleaner file structure

## 0.1.0-alpha - 2014-12-31

- Initial version
- Client logic OK
- KeyValue OK (without Patch yet)
- Search OK (but still misses a method for pagination)
- List, Events and Graph NOT YET IMPLEMENTED
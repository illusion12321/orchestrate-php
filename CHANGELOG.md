# CHANGELOG

## 0.6.0 - 2015-02-04
- Removed the automatic client linking for the Objects, it has to be linked manually with setApplication(). It would create conflicts on larger systems, where multiple clients could be created anywhere.
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
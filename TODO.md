# TODO and IDEAS

!!!
ver se o parametro Application fica ou sai dos constructors

!!!
mudar o setApplication para setClient anyway!??? ele seria um ClientInterface?

o lance do naming do 'kind' e 'type', principalmente o kind porque conflita com o toArray

resumir os traits, e testar, numa dessa eles somem! Rever mais uma vez se precisa reorganizar a estrutura de pastas, objetos viram top level, properties volta para common, ou quebra Objects em Lists tambem?


- Allow Collection/Application to change the list's children class! Specially important for Models. --- improve setItemClass, setListClass, setEventClass and make it pass from one class to another

- Study the best way to integrate Refs and Events into a KeyValue for easy access/management

- Consider removing post from KeyValue?

- Implement a better pagination (study AWS 3), including an option to automatically load the next pages when doing a iteration (foreach)

- Check out https://github.com/jmespath/jmespath.php

- Implement some common interfaces, and general organization of the base classes

- Work on Query builders (query builder (search) / sort / aggregate builder / range builder

- Add totalCount method to Collection or KeyValues?

- Maybe even a getAll to Collection? getFirst?

- Add an appendNextPage appendPrevPage to List, so we get the next set of results without erasing the last page?

- Inline Docs

- Implement Tests

- Add Docs (ApiGen, Sami or something that can be created from the source files)?

- Implement async operations?

- Add sort operations to List objects? basically just a map to PHP sort?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?

- method to move a KeyValue to another Collection or Application?

- add serialize/unserialize? Review the use case. (unserialize could use init, and serialize toArray)

- event system?
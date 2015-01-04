# TODO and IDEAS

- Ver de voltar o getValue e fazer com que o toArray, retorne realmente uma represetação do objeto, por exemplo, path/value, etc
Mas ai vai conflitar com o toArray do Search, que é os results.. ou voltar o getResults e getValue para não complicar tanto?


- Maybe change the naming of Objects to Resources

- Study the possibility of turning all objects to resources like: $application['collection']['key'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources


- Try to remove the Application parameter and simplify the others
    // sometimes it's interesting to instantiate these objects directly, to populate with data then send
setStatic() // enableStatic / disableStatic?
getStatic()


- The List results should be associative array? With the keys? (could be nice to have array_keys()) -- confirm later, after the refs

- Maybe (very) later apply a static Facade ? 'Orchestrate' (Application) ?

- include a method 'refs' ou 'listRefs' inside the KeyValue object, as well as events?

- add sort operations to List objects?

- maybe implement a __toString() in the objects, return something like 'key/ref' or the effectiveUrl in the others?



- check the 'archival' property of the ruby client

- method to move object to another Collection and Application ?


!!!!!! matar o Search object???
!!!!! arrancar os cross-object do Collection ????
!!!! com isso voltar todos os inner methods de listEvents listRefs para get!


!!! 
ver se eu consigo implementar o reset em todos os request do KeyValue tambem
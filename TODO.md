# TODO and IDEAS

- Talvez alguns métodos do KeyValue que estão lá não deveriam (o post) porque o objeto tem que ter os métodos para mexer com ele, não com o collection


- Maybe change the naming of Objects to Resources

- Study the possibility of turning all objects to resources like: $application['collection']['key'] that loads automatically the object, if not already on cache  - more ideas on https://github.com/awslabs/aws-sdk-php-resources


- Try to remove the Application parameter and simplify the others
    // sometimes it's interesting to instantiate these objects directly, to populate with data then send
setStatic() // enableStatic / disableStatic?
getStatic()


- The List results should be associative array? With the keys? (could be nice to have array_keys())

- Maybe (very) later apply a static Facade ? 'Orchestrate' (Application) ?


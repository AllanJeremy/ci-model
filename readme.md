# About

The model override allows for simple creation of models in Codeigniter 3.x
For default functionality, all you need to do is extend the `MY_Model` class.

In most cases, this means all you have to do is specify the table you want the model to be used with and you are as good as done

## Assumptions

- A single model will only attach to a single table. 
  
> Although you may change the value of the table from method to method through `$this->table_name = 'new_table_name'`, this is not recommended as it may be confusing to understand in future.

## Usage

1. Copy the core folder into your Codeigniter 3.x `application/` directory

2. Extend the MY_Model class when creating a model.

```php
    class Custom_Model extends MY_Model
    {
        function __construct()
        {
            # This is the table name the model will use
            parent:__construct('your_table_name');
        }

        // Overrides go here
    }
```

### Using joins

When using joins, simply create an override for the `read` method and add the joins at the top as shown in this example. Ensure that the parameters are the same as the `parent` class(`MY_Model`) method parameters

```php
    // Public because we won't be extending this class
    public function read($filters,$limit=NULL,$offset=0,$is_strict=TRUE)
    {
        # Your joins go here.. For example:
        $this->db->join('comments', 'comments.id = blogs.id');

        return $this->read($filters,$limit,$offset,$is_strict);
    }
```

## Documentation

I'll be adding some soon

## Contributing

Clone the project, make changes and make a pull request
Ensure you don't make breaking changes. Any breaking changes should be marked as such while submitting a pull request. Failure to do so will have your request disqualified.

## Bugs & Issues

Feel free to use [github issues](https://www.github.com/AllanJeremy/ci-model/issues) to post any issues you may find.
Before submitting an issue, check through the issues to see if the issue has already been raised.

Alternatively, [shoot me a message](mailto:dev@allanjeremy.com)

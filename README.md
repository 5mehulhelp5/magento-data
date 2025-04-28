# Rkt_MageData

Data Object & Data Transfer Objects for Magento 2

## Installation

```
composer require rkt/magento-data
```

## Features
### 1. Get an instance

  Instantiate a data object class using `from` method.
  With a below data object:
  ```
    <?php
    use Rkt\MageData\Data;

    class ProductImageData extends Data
    {
        public function __construct(
            public string $src,
            public ?string $alt,
        ) {
        }
    }
  ```
  You can get instance using
  ```
  $image = ProductImageData::from(['src' => 'https://example.com/image.jpg', 'alt' => 'Awesome image']);
  ```
### 2. Validation

  Validation is a critical feature you get out of box for data object. All you need to do is define `rules()` method
  within your data object and specify the rules. An example is given below:
  ```
    <?php
    use Rkt\MageData\Data;

    class Customer extends Data
    {
        public function __construct(
            public string $email,
            public string $firstname,
            public string $lastname,
            public array $street,
        ) {
        }
        
        public function rules(): array
        {
            return [
                'email' => 'email|required',
                'firstname' => 'required|min:1|max:250',
                'lastname' => 'required|min:1|max:250',
                'street.0' => 'required',
            ];
        }
    }
  ```
  As you can see here `rules()` provides array of rules for DO's property. Key represents property name and value represents
  property rules. Multiple rules are separated by a pipe (`|`). If rule requires additional params (eg: `max`), then they
  will be provided after a colon (`:`). If there are multiple parameters, then they should be separated with comma (`,`).

  If you have an array property then, it is also possible to provide validation to an individual item as shown above.

  If you want to provide custom validation messages, then that is also possible. All you need to do is include another method
  `messages()` in your DO. An example:
  ```
    public function messages(): array
    {
        return [
            'email:email' => __('Customer email is invalid.'),
            'firstname:required' => __('First name cannot be empty. Please fill it.'),
        ];
    }
  ```

  If you want to use aliases for data properties, then it is available via `aliases()` method. An example:
  ```
  public function aliases(): array
  {
      return [
          'email' => __('Email Address'),
      ];
  }
  ```
  Now in errors, it will use `Email Address`  for `email` property.

  Internally it uses `rakit/validation` package to validate these properties.

  You can also validate complex data objects as well. For example:
  ```
  class Person extends Data
  {
        public function __construct(
            public string $firstname,
        ) {}
        
        public function rules(): array
        {
            return ['firstname' => 'required']
        }
  }
  
  class Family extends Data
  {
        public function __construct(
            public Person $father,
            public Person $mother,
            public ?array $children = [],
        ) {}
        
        public function rules(): array
        {
            return ['father' => 'required', 'mother' => 'required']
        }
  }
  ```
  For this setup, it will make sure `Family` instance should have both father and mother and both father and mother
  should have a firstname as `Person` rules specifies it. Suppose you specified `children` for Family and they are
  instance of `Person` data object, then they also get validated. But `children` can be empty in the `Family` instance
  as it has no rule for it.


### 3. Convert to array or json.
  You have `toArray()` and `toJson()` methods available to convert your data object to an array or json respectively.
____
**Note:** This module is under construction. More features will be added. Stay tuned :)

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
    public function rules(): array
    {
        return [
            'email.email' => 'Customer email is invalid.',
            'firstname.required' => 'First name cannot be empty. Please fill it.',
        ];
    }
  ```
  Internally it uses symfony validation to validate these properties.

### 3. Convert to array or json.
  You have `toArray()` and `toJson()` methods available to convert your data object to an array or json respectively.
____
**Note:** This module is under construction. More features will be added. Stay tuned :)

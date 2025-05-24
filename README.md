# Rkt_MageData

A powerful, lightweight **Data Object & DTO (Data Transfer Object)** system for **Magento 2** — supporting smart instantiation, validation, and serialization.

---

## 🚀 Installation

```bash
composer require rkt/magento-data
```

---

## ✨ Features Overview

### 1. ✅ Easy Data Object Instantiation

Use the static `from()` or `create()` methods to easily instantiate data objects.

#### Example

```php
use Rkt\MageData\Data;

class ProductImageData extends Data
{
    public function __construct(
        public string $src,
        public ?string $alt = null,
    ) {}
}
```

You can instantiate it like this:

```php
$image = ProductImageData::from([
    'src' => 'https://example.com/image.jpg',
    'alt' => 'Awesome image'
]);
```

Or using `create()`:

```php
$image = ProductImageData::create([
    'src' => 'https://example.com/image.jpg',
    'alt' => 'Awesome image'
]);
```

---

### 2. 🛡 Validation Built In

This module includes built-in validation using [`rakit/validation`](https://github.com/rakit/validation).

#### 🔹 Basic Validation

Just define a `rules()` method in your data object:

```php
class Customer extends Data
{
    public function __construct(
        public string $email,
        public string $firstname,
        public string $lastname,
        public array $street,
    ) {}

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

> You can validate array elements (like `street.0`) using dot notation.

#### 🔹 Custom Validation Messages

Override `messages()`:

```php
public function messages(): array
{
    return [
        'email:email' => __('Customer email is invalid.'),
        'firstname:required' => __('First name cannot be empty.'),
    ];
}
```

#### 🔹 Custom Field Aliases

Override `aliases()`:

```php
public function aliases(): array
{
    return [
        'email' => __('Email Address'),
    ];
}
```

In error messages, `email` will now appear as "Email Address".

---

### 3. 🧩 Nested Object Validation

Nested and recursive validation works out of the box:

```php
class Person extends Data
{
    public function __construct(public string $firstname) {}

    public function rules(): array
    {
        return ['firstname' => 'required'];
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
        return [
            'father' => 'required',
            'mother' => 'required',
        ];
    }
}
```

✅ In this setup:

* `father` and `mother` are required and must pass `Person` validation.
* `children` can be a list of `Person`, and they’ll be validated too (if provided).

---

### 4. 🧵 Event-Driven Rule Customization

You can dynamically modify validation rules/messages/aliases using Magento events.

#### 🔹 Example

```php
namespace Rkt\Example\Data;

class MyData extends Data
{
    public function __construct(public string $email) {}

    public function rules(): array
    {
        return ['email' => 'required'];
    }
}
```

#### 🔸 Event Name

When `validate()` is called, the event `rkt_example_data_mydata_validate_before` is dispatched.

#### 🔹 Observer Configuration (`events.xml`)

```xml
<event name="rkt_example_data_mydata_validate_before">
    <observer name="update_mydata_validation_data"
              instance="Rkt\Example\Observer\UpdateMyDataValidation" />
</event>
```

#### 🔹 Sample Observer

```php
class UpdateMyDataValidation implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $transport = $observer->getData('transport');

        $rules = $transport->getData('rules');
        $rules['email'] = 'required|email';

        $aliases = $transport->getData('aliases');
        $aliases['email'] = 'Email Address';

        $transport->setData('rules', $rules);
        $transport->setData('aliases', $aliases);
    }
}
```

✅ Now your validation dynamically adds the `email` rule and alias based on observer logic.

---

### 5. 🔄 Serialization Support

Convert data objects to array or JSON easily:

```php
$data->toArray(); // → returns an array representation

$data->toJson(); // → returns a JSON string
```


### 6. Fetch validations rules applicable to the data

You can get the validation rules applicable for a payload like this.

```php
class Family extends Data
{
    public function __construct(
        public Person $father,
        public Person $mother,
        public ?array $children = [],
    ) {
    }

    public function rules(): array
    {
        return [
            'father' => 'required',
            'mother' => 'required',
            'children' => 'array|nullable'
        ];
    }
}

class Person extends Data
{
    use UseValidation;

    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $email,
    ) {
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'firstname' => 'required',
            'lastname' => 'required',
        ];
    }
}
```

Now if you call 

```php
$rules = Family::getValidationRules([
    'father' => ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@example.com'],
    'mother' => ['firstname' => 'Jane', 'lastname' => 'Doe', 'email' => 'jane@example.com'],
    'children' => [
        ['firstname' => 'Jimmy', 'lastname' => 'Doe', 'email' => 'jimmy@example.com'],
    ],
]);
```
Will provide you below result:

```php
$rules = [
    'father' => 'required',
    'father.firstname' => 'required',
    'father.lastname' => 'required',
    'father.email' => 'required|email',
    'mother' => 'required',
    'mother.firstname' => 'required',
    'mother.lastname' => 'required',
    'mother.email' => 'required|email',
    'children' => 'array|null',
]
```
---

## 📌 Notes

* This module is **under active development** — more features and integrations are coming soon!
* Built for flexibility, testability, and ease of use in **Magento 2 backend and frontend service layers**.

---

## 💬 Stay Connected

Got feedback or feature requests? PRs and ideas are welcome!

---

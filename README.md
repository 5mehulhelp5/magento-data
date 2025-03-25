# Rkt_MageData

Data Object & Data Transfer Objects for Magento 2

## Features
1. Instantiate a data object class using `from` method.
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
____
**Note:** This module is under construction. More features will be added. Stay tuned :)

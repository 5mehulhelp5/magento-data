<?php

declare(strict_types=1);

namespace Rkt\MageData\Tests\Integration\Traits;

use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Rkt\MageData\Exceptions\ValidationException;
use Rkt\MageData\Model\DataObjectFactory;
use Rkt\MageData\Tests\TestDoubles\Person;
use Rkt\MageData\Tests\TestDoubles\Family;

class UseValidationTest extends TestCase
{
    protected function setUp(): void
    {
        DataObjectFactory::init(ObjectManager::getInstance());
    }

    public function test_validation_passes_without_exception(): void
    {
        $this->expectNotToPerformAssertions();

        $person = new Person('John', 'Doe', 'john.doe@example.com');
        $person->validate();
    }

    public function test_validation_fails_and_throws_exception(): void
    {
        $this->expectException(ValidationException::class);

        $person = new Person('John', 'Doe', '');
        $person->validate();
    }

    public function test_validation_fails_without_throwing_exception(): void
    {
        $person = new Person('John', 'Doe', '');
        $person->validate(throwException: false);

        $this->assertTrue(true);
    }

    public function test_nested_validation_passes(): void
    {
        $father = new Person('John', 'Doe', 'john@example.com');
        $mother = new Person('Jane', 'Doe', 'jane@example.com');
        $child1 = new Person('Jimmy', 'Doe', 'jimmy@example.com');
        $child2 = new Person('Jenny', 'Doe', 'jenny@example.com');

        $family = new Family($father, $mother, [$child1, $child2]);

        $this->expectNotToPerformAssertions();
        $family->validate();
    }

    public function test_nested_validation_fails(): void
    {
        $this->expectException(ValidationException::class);

        $father = new Person('John', 'Doe', '');
        $mother = new Person('Jane', 'Doe', 'jane@example.com');

        $family = new Family($father, $mother);
        $family->validate();
    }

    public function test_nested_validation_fails_for_array_values(): void
    {
        $this->expectException(ValidationException::class);

        $father = new Person('John', 'Doe', 'john@example.com');
        $mother = new Person('Jane', 'Doe', 'jane@example.com');
        $child1 = new Person('Jimmy', 'Doe', '');
        $child2 = new Person('Jenny', 'Doe', 'jenny@example.com');

        $family = new Family($father, $mother, [$child1, $child2]);
        $family->validate();
    }

    public function test_get_validation_rules_passes(): void
    {
        $rules = Person::getValidationRules([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('firstname', $rules);
        $this->assertArrayHasKey('lastname', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertEquals('required|email', $rules['email']);
    }

    public function test_get_validation_rules_provides_rules_for_nested_objects(): void
    {
        $rules = Family::getValidationRules([
            'father' => ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@example.com'],
            'mother' => ['firstname' => 'Jane', 'lastname' => 'Doe', 'email' => 'jane@example.com'],
            'children' => [
                ['firstname' => 'Jimmy', 'lastname' => 'Doe', 'email' => 'jimmy@example.com'],
                ['firstname' => 'Jenny', 'lastname' => 'Doe', 'email' => 'jenny@example.com'],
            ],
        ]);

        $this->assertArrayHasKey('father', $rules);
        $this->assertEquals('required', $rules['father']);
        $this->assertEquals('required|email', $rules['father.email']);
        $this->assertEquals('required', $rules['father.firstname']);

        $this->assertArrayHasKey('mother', $rules);
        $this->assertEquals('required', $rules['mother']);
        $this->assertEquals('required|email', $rules['mother.email']);

        $this->assertArrayHasKey('children', $rules);
        $this->assertEquals('array|nullable', $rules['children']);
    }
}

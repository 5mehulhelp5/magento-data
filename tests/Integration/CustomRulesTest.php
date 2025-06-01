<?php

declare(strict_types=1);

namespace Rkt\MageData\Tests\Integration\Traits;

use PHPUnit\Framework\TestCase;
use Rkt\MageData\Exceptions\ValidationException;
use Rkt\MageData\Tests\TestDoubles\CustomRuleData;

class CustomRulesTest extends TestCase
{
    public function test_it_is_possible_to_add_custom_rules()
    {
        $data = CustomRuleData::from(['email' => 'johndoe@example.com', 'name' => 'John Doe' ]);
        $this->assertTrue(
            is_callable([$data, 'customRules']),
            'customRules method should be callable'
        );

        $result = $data->customRules();

        $this->assertIsArray($result, 'customRules should return an array');
    }

    public function test_callback_custom_validator_fails_as_expected()
    {
        $data = CustomRuleData::from(['email' => 'johndoe@example.com', 'name' => 'john doe' ]);
        try {
            $data->validate();
            $this->fail('callback validation failed');
        } catch (ValidationException $e) {
            $this->assertContains('The Name is not valid', $e->getErrors());
        }
    }

    public function test_custom_rule_validation_fails_as_expected()
    {
        $data = CustomRuleData::from(['email' => 'johndoe@gmail.com', 'name' => 'john doe' ]);
        try {
            $data->validate();
            $this->fail('Custom rule validation failed');
        } catch (ValidationException $e) {
            $this->assertContains('The Email must be end with example.com', $e->getErrors());
        }
    }

    public function test_custom_validation_rules_passes_as_expected()
    {
        $data = CustomRuleData::from([
            'email' => 'johndoe@example.com',
            'name' => 'John Doe Disussa',
        ]);

        $data->validate();
    }
}

<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

use InvalidArgumentException;
use Rkt\MageData\Data;
use Rkt\MageData\Exceptions\ValidationException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;

trait UseValidation
{
    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }

    public function validate(bool $throwException = true): array
    {
        $validator = Validation::createValidator();
        $rules = $this->rules();
        $errors = [];

        foreach ($rules as $propertyPath => $ruleString) {
            $value = $this->resolveValueByPath($propertyPath);

            // 1. Handle nested object validation
            if ($value instanceof Data) {
                $nestedErrors = $value->validate(false);
                foreach ($nestedErrors as $nestedField => $nestedMessages) {
                    $errors["$propertyPath.$nestedField"] = $nestedMessages;
                }
                continue;
            }

            // 2. Handle array of nested objects
            if (is_array($value) && !empty($value) && $value[0] instanceof Data) {
                foreach ($value as $index => $item) {
                    $nestedErrors = $item->validate(false);
                    foreach ($nestedErrors as $nestedField => $nestedMessages) {
                        $errors["$propertyPath.$index.$nestedField"] = $nestedMessages;
                    }
                }
                continue;
            }

            // 3. Validate scalar fields
            $ruleParts = explode('|', $ruleString);
            $constraints = [];

            foreach ($ruleParts as $rule) {
                $constraints[] = $this->wrapWithCustomMessageConstraint($propertyPath, $rule);
            }

            $violations = $validator->validate($value, $constraints);

            if (count($violations) > 0) {
                /** @var ConstraintViolationInterface $violation */
                foreach ($violations as $violation) {
                    $errors[$propertyPath][] = $violation->getMessage();
                }
            }
        }

        if ($throwException && !empty($errors)) {
            $this->throwValidationException($errors);
        }

        return $errors;
    }

    private function wrapWithCustomMessageConstraint(string $property, string $rule): Constraint
    {
        $ruleName = $rule;
        $params = [];

        if (str_contains($rule, ':')) {
            [$ruleName, $params] = explode(':', $rule, 2);
            $params = explode(',', $params);
        }

        $matchedRule = match ($ruleName) {
            'required' => new Assert\NotBlank(),
            'email' => new Assert\Email(),
            'max' => new Assert\Length(['max' => (int) $params[0]]),
            'min' => new Assert\Length(['min' => (int) $params[0]]),
            'boolean' => new Assert\Type('bool'),
            'string' => new Assert\Type('string'),
            'array' => new Assert\Type('array'),
            default => throw new InvalidArgumentException("Unknown rule: $ruleName"),
        };

        $this->addCustomMessageToRule($property, $ruleName, $matchedRule);

        return $matchedRule;
    }

    private function addCustomMessageToRule(string $property, string $ruleName, Constraint $rule): void
    {
        $key = "$property.$ruleName";
        $messages = $this->messages();
        // This explicit string conversion required as messages can be Phrase instance as well.
        $customMessage = isset($messages[$key]) ?  (string) $messages[$key] : null;

        if (!$customMessage) {
            return;
        }

        match ($ruleName) {
            'max' => $rule->maxMessage = $customMessage,
            'min' => $rule->minMessage = $customMessage,
            default => $rule->message = $customMessage,
        };
    }

    private function resolveValueByPath(string $path): mixed
    {
        $segments = explode('.', $path);
        $value = $this;

        foreach ($segments as $segment) {
            if (is_object($value) && property_exists($value, $segment)) {
                $value = $value->{$segment};
            } elseif (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return null;
            }
        }

        return $value;
    }

    public function throwValidationException(array $errors): void
    {
        $exception = new ValidationException();
        $exception->setErrors($errors);

        throw $exception;
    }
}

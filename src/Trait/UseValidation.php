<?php

declare(strict_types=1);

namespace Rkt\MageData\Trait;

use InvalidArgumentException;
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

    public function validate(): array
    {
        $validator = Validation::createValidator();
        $rules = $this->rules();
        $errors = [];

        foreach ($rules as $propertyPath => $ruleString) {
            $value = $this->resolveValueByPath($propertyPath);
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

        /**
         * @todo we should allow custom validation rules as well.
         */
        $matchedRule = match ($ruleName) {
            'required' => new Assert\NotBlank(),
            'email' => new Assert\Email(),
            'max' => new Assert\Length(['max' => (int) $params[0]]),
            'min' => new Assert\Length(['min' => (int) $params[0]]),
            default => throw new InvalidArgumentException("Unknown rule: $ruleName"),
        };
        $this->addCustomMessageToRule($property, $ruleName, $matchedRule);

        return $matchedRule;
    }

    private function addCustomMessageToRule(string $property, string $ruleName, Constraint $rule): void
    {
        $key = "$property.$ruleName";
        $messages = $this->messages();
        $customMessage = $messages[$key] ?? null;

        if (!$customMessage) {
            return;
        }

        match($ruleName) {
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
}

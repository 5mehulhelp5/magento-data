<?php

declare(strict_types=1);

namespace Rkt\MageData\Model\Validation\Rule;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Encryption\Helper\Security;
use Somnambulist\Components\Validation\Rule;

class FormKeyRule extends Rule
{
    protected bool $implicit = true;

    public function __construct(
        private readonly FormKey $formKey
    ) {
        $this->message = __('Invalid form key.')->render();
    }

    public function check($value): bool
    {
        return $value && Security::compareStrings($value, $this->formKey->getFormKey());
    }
}

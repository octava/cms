<?php
namespace Octava\Bundle\MuiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class FileNotBlank extends Constraint
{
    public $message = 'This value should not be blank.';
}

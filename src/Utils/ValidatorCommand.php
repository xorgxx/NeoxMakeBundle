<?php
    
    namespace NeoxMake\NeoxMakeBundle\Utils;
    
    class ValidatorCommand
    {
        public function checkBool(?bool $value): ?bool
        {
            if ($value === null) {
                throw new \InvalidArgumentException('Value must be true or false');
            }
            return $value;
        }
    }
<?php

namespace Karolina;

use Respect\Validation\Validator as v;

class Currency
{
    private $iso;
    private $symbol;
    private $formatting;

    public function __construct($iso, $symbol = false, $formatting = false)
    {
        $this->setISO($iso);

        if ($symbol) {
            $this->symbol = $symbol;
        } else {
            $this->symbol = $this->getDefaultSymbol($iso);
        }

        if ($formatting) {
            $this->formatting = $formatting;
        } else {
            $this->formatting = $this->getDefaultFormatting($iso);
        }
    }


    public function __toString()
    {
        return $this->iso;
    }

    public function getSymbol()
    {
        return $this->symbol;
    }

    private function setISO($iso)
    {
        if (v::stringType()->length(3, 3)->validate($iso)) {
            $this->iso = $iso;
        } else {
            throw new KarolinaException(
                'Currency must be three digits ISO format',
                500,
                null,
                "incorrect_currency_format"
            );
        }
    }

    public function getISO()
    {
        return $this->iso;
    }

    public function getFormatted($amount)
    {
        $string = sprintf($this->formatting, $amount, $this->symbol);
        return $string;
    }

    private function getDefaultSymbol($iso)
    {
        $symbols['EUR'] = "€";
        $symbols['USD'] = "$";

        if (isset($symbols[$iso])) {
            return $symbols[$iso];
        } else {
            return $this->iso;
        }
    }

    private function getDefaultFormatting($iso)
    {
        $formatting['NOK'] = '%1$s %2$s';
        $formatting['EUR'] = '%2$s%1$s';


        if (isset($formatting[$iso])) {
            return $formatting[$iso];
        } else {
            return '%2$s %1$s';
        }
    }
}

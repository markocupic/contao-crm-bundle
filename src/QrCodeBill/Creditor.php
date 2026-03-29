<?php

declare(strict_types=1);

/*
 * This file is part of Contao CRM Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

namespace Markocupic\ContaoCrmBundle\QrCodeBill;

class Creditor
{
    public function __construct(
        private readonly string $fullname,
        private readonly string $street,
        private readonly string $streetNumber,
        private readonly string $postal,
        private readonly string $city,
        private readonly string $countryCode,
        private readonly string $iban,
    ) {
    }

    public function getFullname(): string
    {
        return $this->fullname;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getStreetNumber(): string
    {
        return $this->streetNumber;
    }

    public function getPostal(): string
    {
        return $this->postal;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getIban(): string
    {
        return $this->iban;
    }
}

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

use Fpdf\Fpdf;
use Sprain\SwissQrBill\DataGroup\Element\AdditionalInformation;
use Sprain\SwissQrBill\DataGroup\Element\CreditorInformation;
use Sprain\SwissQrBill\DataGroup\Element\PaymentAmountInformation;
use Sprain\SwissQrBill\DataGroup\Element\PaymentReference;
use Sprain\SwissQrBill\DataGroup\Element\StructuredAddress;
use Sprain\SwissQrBill\PaymentPart\Output\DisplayOptions;
use Sprain\SwissQrBill\PaymentPart\Output\FpdfOutput\FpdfOutput;
use Sprain\SwissQrBill\QrBill;
use Sprain\SwissQrBill\Reference\RfCreditorReferenceGenerator;
use Symfony\Component\Filesystem\Path;

readonly class QrInvoiceGenerator
{
    public function __construct(
        private string $projectDir,
    ) {
    }

    public function generate(Creditor $creditor, Deptor $deptor, string $referenceCode, float $amount = 0.00, string $currencyCode = 'CHF', string $language = 'de', string $additionalInformation = ''): \SplFileInfo
    {
        $qrBill = $this->generateQrBill($creditor, $deptor, $referenceCode, $amount, $currencyCode, $additionalInformation);

        // 2. Create an FPDF instance (or use an existing one from your project)
        // – alternatively, an instance of \setasign\Fpdi\Fpdi() is also accepted by FpdfOutput.
        $fpdf = new Fpdf('P', 'mm', 'A4');

        // In case your server does not support "allow_url_fopen", use this way to create your FPDF instance:
        // $fpdf = new class('P', 'mm', 'A4') extends \Fpdf\Fpdf {
        //     use \Fpdf\Traits\MemoryImageSupport\MemImageTrait;
        // };

        // In case you want to draw scissors and dashed lines, use this way to create your FPDF instance:
        // $fpdf = new class('P', 'mm', 'A4') extends \Fpdf\Fpdf {
        //    use \Sprain\SwissQrBill\PaymentPart\Output\FpdfOutput\FpdfTrait;
        // };

        $fpdf->AddPage();

        // 3. Create a full payment part for FPDF
        $output = new FpdfOutput($qrBill, $language, $fpdf);

        // 4. Optional, set layout options
        $displayOptions = new DisplayOptions();
        $displayOptions
            ->setPrintable(false) // true to remove lines for printing on a perforated stationer
            ->setDisplayTextDownArrows(false) // true to show arrows next to separation text, if shown
            ->setDisplayScissors(false) // true to show scissors instead of separation text
            ->setPositionScissorsAtBottom(false) // true to place scissors at the bottom, if shown
        ;

        // 5. Generate the output
        $output
            ->setDisplayOptions($displayOptions)
            ->getPaymentPart()
        ;

        // 6. Let's save the generated example in a file
        $outputPath = Path::join($this->projectDir, '/system/tmp', md5(random_bytes(12).'.pdf'));
        $fpdf->Output($outputPath, 'F');

        return new \SplFileInfo($outputPath);
    }

    /**
     * Generate an ISO 11649 Creditor Reference (RF reference).
     *
     * @param string $reference Base reference (max 21 alphanumeric chars)
     */
    private function generateIso11649Reference(string $reference): string
    {
        // Normalize: remove spaces, uppercase
        $reference = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $reference));

        if (empty($reference)) {
            throw new \InvalidArgumentException('Reference must not be empty.');
        }

        if (\strlen($reference) > 21) {
            $err = \sprintf('Reference "%s" must not be longer than 21 characters.', $reference);

            throw new \InvalidArgumentException($err);
        }

        // Step 1: Append "RF00" to the reference
        $temp = $reference.'RF00';

        // Step 2: Convert letters to numbers (A=10 ... Z=35)
        $converted = '';

        foreach (str_split($temp) as $char) {
            if (ctype_alpha($char)) {
                $converted .= \ord($char) - 55; // A=10, B=11, ..., Z=35
            } else {
                $converted .= $char;
            }
        }

        // Step 3: Calculate checksum
        $checksum = 98 - bcmod($converted, '97');
        $checksum = str_pad((string) $checksum, 2, '0', STR_PAD_LEFT);

        // Step 4: Return final RF reference
        return 'RF'.$checksum.$reference;
    }

    private function generateQrBill(Creditor $creditor, Deptor $deptor, string $referenceCode, float $amount = 0.00, string $currencyCode = 'CHF', $additionalInformation = ''): QrBill
    {
        $qrBill = QrBill::create();

        // Add creditor information
        // Who will receive the payment and to which bank account?
        $qrBill->setCreditor(
            StructuredAddress::createWithStreet(
                $creditor->getFullname(),
                $creditor->getStreet(),
                $creditor->getStreetNumber(),
                $creditor->getPostal(),
                $creditor->getCity(),
                $creditor->getCountryCode(),
            ),
        );

        $qrBill->setCreditorInformation(
            CreditorInformation::create(
                $creditor->getIban(), // With SCOR, this is a classic iban. QR-IBANs will not be valid here.
            ),
        );

        // Add debtor information
        // Who has to pay the invoice? This part is optional.
        $qrBill->setUltimateDebtor(
            StructuredAddress::createWithStreet(
                $deptor->getFullname(),
                $deptor->getStreet(),
                $deptor->getStreetNumber(),
                $deptor->getPostal(),
                $deptor->getCity(),
                $deptor->getCountryCode(),
            ),
        );

        // Add payment amount information
        // What amount is to be paid?
        $qrBill->setPaymentAmountInformation(
            PaymentAmountInformation::create(
                $currencyCode,
                $amount,
            ),
        );

        // Add payment reference
        // This is what you will need to identify incoming payments.
        $qrBill->setPaymentReference(
            PaymentReference::create(
                PaymentReference::TYPE_SCOR,
                RfCreditorReferenceGenerator::generate($this->generateIso11649Reference($referenceCode)),
            ),
        );

        // Optionally, add some human-readable information about what the bill is for.
        if ($additionalInformation) {
            $qrBill->setAdditionalInformation(AdditionalInformation::create($additionalInformation));
        }

        try {
            $qrBill->getQrCode()->writeFile($this->projectDir.'/system/tmp/qr.png');
            $qrBill->getQrCode()->writeFile($this->projectDir.'/system/tmp/qr.svg');
        } catch (\Exception) {
            foreach ($qrBill->getViolations() as $violation) {
                throw new \Exception($violation->getMessage());
            }
        }

        return $qrBill;
    }
}

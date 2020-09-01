<?php

/**
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 *
 * @author     Marko Cupic
 * @package    Contao CRM Bundle
 * @license    MIT
 * @see        https://github.com/markocupic/contao-crm-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoCrmBundle\Invoice\Pdf;

use CloudConvert\Api;
use Contao\File;

/**
 * Class Pdf
 *
 * @package Markocupic\ContaoCrmBundle\Invoice\Pdf
 */
class Pdf
{

    /** @var string */
    protected $projectDir;

    /**
     * Pdf constructor.
     *
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {

        $this->projectDir = $projectDir;
    }

    /**
     * Convert docx to pdf
     *
     * @param File $objFile
     * @param string $apiKey
     * @return File
     * @throws \CloudConvert\Exceptions\ApiException
     * @throws \CloudConvert\Exceptions\InvalidParameterException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function generate(File $objFile, string $apiKey): File
    {

        $pdfSrc = preg_replace('/\.docx$/', '.pdf', $objFile->path);

        // Delete no more used file
        if (file_exists($this->projectDir . '/' . $pdfSrc))
        {
            unlink($this->projectDir . '/' . $pdfSrc);
        }

        $api = new Api($apiKey);

        $api->convert([
            'inputformat'  => 'docx',
            'outputformat' => 'pdf',
            'input'        => 'upload',
            'file'         => fopen($this->projectDir . '/' . $objFile->path, 'r'),
        ])
            ->wait()
            ->download($this->projectDir . '/' . $pdfSrc);

        if (file_exists($this->projectDir . '/' . $pdfSrc))
        {
            return new File($pdfSrc);
        }

        throw new \Exception('Failed converting from docx to pdf.');
    }

}

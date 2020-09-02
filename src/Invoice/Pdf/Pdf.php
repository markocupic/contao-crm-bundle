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
use Contao\System;

/**
 * Class Pdf
 *
 * @package Markocupic\ContaoCrmBundle\Invoice\Pdf
 */
class Pdf
{

    /** @var string */
    protected $projectDir;

    /** @var string */
    protected $cloudconvertApiKey;

    /**
     * Pdf constructor.
     *
     * @param string $projectDir
     * @param string $cloudconvertApiKey
     */
    public function __construct(string $projectDir, string $cloudconvertApiKey = '')
    {

        $this->projectDir = $projectDir;
        $this->cloudconvertApiKey = $cloudconvertApiKey;
    }

    /**
     * Convert docx to pdf
     *
     * @param File $objFile
     * @return File
     * @throws \CloudConvert\Exceptions\ApiException
     * @throws \CloudConvert\Exceptions\InvalidParameterException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function generate(File $objFile): File
    {

        if (empty($this->cloudconvertApiKey))
        {
            new \Exception('No API Key defined for the Cloud Convert Service. https://cloudconvert.com/api');
        }

        $pdfSrc = preg_replace('/\.docx$/', '.pdf', $objFile->path);

        // Delete no more used file
        if (file_exists($this->projectDir . '/' . $pdfSrc))
        {
            unlink($this->projectDir . '/' . $pdfSrc);
        }

        $api = new Api($this->cloudconvertApiKey);

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

<?php

declare(strict_types=1);

/*
 * This file is part of Contao Crm Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-crm-bundle
 */

namespace Markocupic\ContaoCrmBundle\Invoice\Pdf;

use CloudConvert\Api;
use CloudConvert\Exceptions\ApiException;
use CloudConvert\Exceptions\InvalidParameterException;
use Contao\File;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Pdf.
 */
class Pdf
{
    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var string
     */
    protected $cloudconvertApiKey;

    /**
     * Pdf constructor.
     */
    public function __construct(string $projectDir, string $cloudconvertApiKey = '')
    {
        $this->projectDir = $projectDir;
        $this->cloudconvertApiKey = $cloudconvertApiKey;
    }

    /**
     * Convert docx to pdf.
     *
     * @throws ApiException
     * @throws InvalidParameterException
     * @throws GuzzleException
     */
    public function generate(File $objFile): File
    {
        if (empty($this->cloudconvertApiKey)) {
            new \Exception('No API Key defined for the Cloud Convert Service. https://cloudconvert.com/api');
        }

        $pdfSrc = preg_replace('/\.docx$/', '.pdf', $objFile->path);

        // Delete no more used file
        if (file_exists($this->projectDir.'/'.$pdfSrc)) {
            unlink($this->projectDir.'/'.$pdfSrc);
        }

        $api = new Api($this->cloudconvertApiKey);

        $api->convert([
            'inputformat' => 'docx',
            'outputformat' => 'pdf',
            'input' => 'upload',
            'file' => fopen($this->projectDir.'/'.$objFile->path, 'r'),
        ])
            ->wait()
            ->download($this->projectDir.'/'.$pdfSrc)
        ;

        if (file_exists($this->projectDir.'/'.$pdfSrc)) {
            return new File($pdfSrc);
        }

        throw new \Exception('Failed converting from docx to pdf.');
    }
}

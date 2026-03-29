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

use iio\libmergepdf\Merger;

class PdfMerger
{
    public function merge(array $files, string $targetPath): \SplFileInfo
    {
        $merger = new Merger();

        foreach ($files as $file) {
            $merger->addFile($file);
        }

        file_put_contents($targetPath, $merger->merge());

        return new \SplFileInfo($targetPath);
    }
}

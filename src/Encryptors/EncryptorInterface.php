<?php

/*
 * This file is part of the DoctrineEncryptBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ambta\DoctrineEncryptBundle\Encryptors;

/**
 * Encryptor interface for encryptors.
 *
 * @author Victor Melnik <melnikvictorl@gmail.com>
 */
interface EncryptorInterface
{
    /**
     * @param string $data Plain text to encrypt
     *
     * @return string Encrypted text
     */
    public function encrypt($data);

    /**
     * @param string $data Encrypted text
     *
     * @return string Plain text
     */
    public function decrypt($data);
}

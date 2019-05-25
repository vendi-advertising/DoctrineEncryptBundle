<?php

/*
 * This file is part of the DoctrineEncryptBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ambta\DoctrineEncryptBundle\Tests\Unit\DependencyInjection;

use Ambta\DoctrineEncryptBundle\Encryptors\HaliteEncryptor;
use PHPUnit\Framework\TestCase;

class HaliteEncryptorTest extends TestCase
{
    private const DATA = 'foobar';

    public function testEncrypt()
    {
        $keyfile = __DIR__.'/fixtures/halite.key';
        $key = file_get_contents($keyfile);
        $halite = new HaliteEncryptor($keyfile);

        $encrypted = $halite->encrypt(self::DATA);
        $this->assertNotSame(self::DATA, $encrypted);
        $decrypted = $halite->decrypt($encrypted);

        $this->assertSame(self::DATA, $decrypted);
        $newkey = file_get_contents($keyfile);
        $this->assertSame($key, $newkey, 'The key must not be modified');
    }

    public function testGenerateKey()
    {
        $keyfile = sys_get_temp_dir().'/halite-'.md5(time());
        if (file_exists($keyfile)) {
            unlink($keyfile);
        }
        $halite = new HaliteEncryptor($keyfile);
        $halite->encrypt(self::DATA);

        $this->assertFileExists($keyfile);
        $this->assertNotEmpty(file_get_contents($keyfile), 'A key should have been created and saved to the file');

        unlink($keyfile);
    }
}

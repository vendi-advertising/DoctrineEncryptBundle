<?php

namespace Ambta\DoctrineEncryptBundle\Configuration;

use Attribute;

/**
 * The Encrypted class handles the @Encrypted annotation.
 *
 * @author Victor Melnik <melnikvictorl@gmail.com>
 * @Annotation
 * @Target("PROPERTY")
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Encrypted implements Annotation
{
    // Placeholder
}

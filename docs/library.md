### Using [Halite](https://github.com/paragonie/halite)

_All deps are already installed with this package_

```yml
// Config.yml
ambta_doctrine_encrypt:
    encryptor_class: Halite
```

### Using [Defuse](https://github.com/defuse/php-encryption)

_You will need to require Defuse yourself_

`composer require "defuse/php-encryption ^2.0"`

```yml
// Config.yml
ambta_doctrine_encrypt:
    encryptor_class: Defuse
```

### Secret key

The secret key should be a max 32 byte hexadecimal string (`[0-9a-fA-F]`).

Secret key is generated if there is no key found. This is automatically generated and stored in the folder defined in the configuration

```yml
// Config.yml
ambta_doctrine_encrypt:
    secret_directory_path: '%kernel.project_dir%'   # Default value
```

Filenames for your secret keys that are generated: `.DefuseEncryptor.key` or `.HaliteEncryptor.key`

**Do not forget to add these files to your .gitignore file, you do not want this on your repository!**

## [Next: Installation](installation.md)

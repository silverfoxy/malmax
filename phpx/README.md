# Installation

First do 

```bash
make clean
phpize clean
```

Use sudo if they fail. Then make sure both `phpize` and `php-config` exist in your path and are related to the PHP version you are targeting (homebrew installations should be fine).

Then run the following:

```bash
phpize
./configure
make
make install
```

Note the installation path. Modify php.ini, add `extension=phpx.so` somewhere (beginning of file is fine), then run:

```bash
php -m | grep PHPx
```

If you see it there, all is well. Another way to test is:

```bash
php -r "echo function_exists('deep_copy'),PHP_EOL;"
```

Good luck!
PHP_ARG_ENABLE(phpx,
  [Whether to enable the "phpx" extension],
  [  enable-phpx        Enable "phpx" extension support])

if test $PHP_PHPX != "no"; then
  AC_DEFINE(HAVE_PHPX, 1, [Whether you have PHPX])
  PHP_SUBST(PHPX_SHARED_LIBADD)
  PHP_NEW_EXTENSION(phpx, phpx.c , $ext_shared)
fi

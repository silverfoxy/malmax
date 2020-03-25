#ifndef PHPX_H
/* Prevent double inclusion */
#define PHPX_H

/* Define Extension Properties */
#define PHPX_EXTVER    "1.2"
#define PHPX_EXTNAME    "PHPx"

/* Import configure options
   when building outside of
   the PHP source tree */
#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

/* Include PHP Standard Header */
#include "php.h"

/* Define the entry point symbol
 * Zend will use when loading this module
 */
extern zend_module_entry phpx_module_entry;
#define phpext_phpx_ptr &phpx_module_entry


#define XG(x) (phpx_globals.x)

ZEND_BEGIN_MODULE_GLOBALS(phpx)
	int			prevFile;
ZEND_END_MODULE_GLOBALS(phpx)
ZEND_DECLARE_MODULE_GLOBALS(phpx)


#endif 

#include "phpx.h"
//ZEND_BEGIN_ARG_INFO(phpx_byref, pass_rest_by_reference)
// #define PHPX_DEBUG 
#ifdef PHPX_DEBUG
#define say(...) do { printf(__VA_ARGS__); fflush(stdout); } while (0)
#endif
ZEND_BEGIN_ARG_INFO_EX(phpx_byref_arginfo, 
    1 /*pass_rest_by_reference*/, 
    0/*return_reference*/,
    1/*required_num_args*/)
ZEND_ARG_PASS_INFO(1/*by_ref*/)
ZEND_END_ARG_INFO();

zend_ulong zval_id(const zval *var)
{
    return (zend_ulong) 
    (zval_get_type(var)*10000000000000)  //type
    +(zend_uintptr_t) Z_COUNTED_P(var);
}
PHP_FUNCTION(zval_id)
{
    zval *z;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC,"z",&z)==FAILURE)
        return;
    RETURN_LONG(zval_id(z));
}
PHP_FUNCTION(is_ref)  
{
    zval *z;
    int res;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC,"z",&z)==FAILURE)
        return;
    RETURN_BOOL(Z_REFCOUNT_P(z) > 2); 
    //1 is the reference sent to this function, the other is the actual var. 
    //if more, reference!
}
static int isCloneable(const zval *obj)
{
    // zend_object *zobj=obj->value.obj;
    // if (zobj->ce->clone) {
    if (Z_OBJCE_P(obj)->clone) {
        // return zobj->ce->clone->common.fn_flags & ZEND_ACC_PUBLIC;
        return Z_OBJCE_P(obj)->clone->common.fn_flags & ZEND_ACC_PUBLIC;
    } else {
        return Z_OBJ_HANDLER_P(obj, clone_obj) != NULL;
    }
}
int deep_copy_intern_ex(const zval *var,zval * out,HashTable *pool,HashTable *object_pool,int depth)
{
    //it seems like object pool is not really needed. zval pool covers it.
    #ifdef PHPX_DEBUG
    for (int i=0;i<depth;++i) printf("\t");
    printf("Output type: %d, Depth %d, Size %d, Type %d\n",Z_TYPE_P(out),depth,zend_hash_num_elements(pool),zval_get_type(var));
    fflush(stdout);
    #endif
    zend_ulong id=zval_id(var);
    #ifdef PHPX_DEBUG
    for (int i=0;i<depth;++i) printf("\t");
    printf("Going to check pool for id %llu... ",id);
    fflush(stdout);
    #endif
    zval *copy;
    if ((copy=zend_hash_index_find(pool,id)))
    { 
        // if (Z_ISREF_P(copy))
        // // if (Z_REFCOUNTED_P(copy)) //anything that can be addreffed, should be addreffed
        //     Z_ADDREF_P(copy); //basically this copy is a copy of the ref, so we need +1 refcount
        // ZVAL_COPY_VALUE(out,copy);
        #ifdef PHPX_DEBUG
        printf("Already available with id (%llu) and type (%d), returning.\n",id,zval_get_type(copy));   
        fflush(stdout);
        #endif
        
        ZVAL_COPY(out,copy); //this does addref if supported.
        return 0; //no element actually copied
    }
    #ifdef PHPX_DEBUG
    printf("not in the pool.\n");
    fflush(stdout);
    #endif
    if (Z_TYPE_P(var)==IS_ARRAY)
    {
        array_init(out); //sets refcount to 1
        zend_array *arr = Z_ARRVAL_P(var);
        int i=zend_array_count(arr);
        int res=0;
        #ifdef PHPX_DEBUG
        for (int i=0;i<depth;++i) printf("\t");
        printf("Going to copy array with %d elements.\n",i);   
        fflush(stdout);
        #endif        
        if (i > 0) 
        {
            zend_string *key;
            zval *data;
            zend_ulong index;
            zend_hash_index_add_new(pool,id,out); //assign reference to pool
            Z_ADDREF_P(out); //this is needed because out was added to array above.
            ZEND_HASH_FOREACH_KEY_VAL_IND(arr, index, key, data) 
            {
                #ifdef PHPX_DEBUG
                for (int i=0;i<depth;++i) printf("\t");
                printf("Element with index: ");   
                if (!key)
                    printf("%llu\n",index);
                else
                    printf("%s\n",ZSTR_VAL(key));
                fflush(stdout);
                #endif                        
                zval tmp;
                ZVAL_UNDEF(&tmp); //default to undefined
                res+=deep_copy_intern_ex(data,&tmp,pool,object_pool,depth+1);
                //add_*_zval does not adjust refcount
                if (!key)
                    add_index_zval(out,index,&tmp); //copied into array
                else
                    add_assoc_zval(out,ZSTR_VAL(key),&tmp);
                // Z_TRY_ADDREF(tmp); //no need to add ref with add_*_zval
                // Z_ADDREF_P(&tmp);
                // zval_dtor(&tmp);
            } ZEND_HASH_FOREACH_END();        
        }
        return res+1;
    }
    else if (Z_TYPE_P(var)==IS_OBJECT)
    {
        zval *cloned_obj;
        zend_ulong handle=Z_OBJ_HANDLE_P(var);
        #ifdef PHPX_DEBUG
        for (int i=0;i<depth;++i) printf("\t");
        printf("Deep copying object #%llu: ",handle);
        fflush(stdout);
        #endif
        if ((cloned_obj=zend_hash_index_find(object_pool,handle)))
        {
            #ifdef PHPX_DEBUG
            printf("already cloned, returning from object pool.\n");
            fflush(stdout);
            #endif
            ZVAL_COPY(out,cloned_obj); //same object
            return 0;
        }
        else //new object
        {
            #ifdef PHPX_DEBUG
            printf("first object encounter. Cloning.\n");
            fflush(stdout);
            #endif
            if (!isCloneable(var))
            {
                php_error_docref("phpx",E_NOTICE,"Attempting to deep copy an uncloneable object.");
                // ZVAL_COPY_VALUE(out,var); //copy zval value, same zval
                ZVAL_COPY(out,var); //copy zval, same object
            }
            else 
            {
                //  \/ zval from obj 
                ZVAL_OBJ(out,zend_objects_clone_obj((zval *)var)); //clone
                //^ basically allocates zend_object, then inits with refcount 1, then clones members
                // Z_ADDREF_P(out);
            }
            zend_hash_index_add_new(object_pool,handle,out);
            Z_ADDREF_P(out);
        }
        zend_hash_index_add_new(pool,id,out);
        Z_ADDREF_P(out);
        return 1;
    }
    else if (Z_TYPE_P(var)==IS_REFERENCE)
    {
        int res=deep_copy_intern_ex(Z_REFVAL_P(var),out,pool,object_pool,depth+1);
        ZVAL_NEW_REF(out,out); //make it a reference again
        // Z_ADDREF_P(out); //this is the culprit for the leak
        
        zend_hash_index_add_new(pool,id,out); //cache reference
        Z_ADDREF_P(out); //this is the fix
        return res;
    }
    else
    {
        if (Z_TYPE_P(var)==IS_RESOURCE)
            php_error_docref("phpx",E_NOTICE,"Attempting to deep copy a resource.");
        
        // ZVAL_COPY(out, var); //refcount++ in here
        ZVAL_DUP(out, var); //inherent refcount++ OR ctor call
        zend_hash_index_add_new(pool,id,out); //cache this zval
        Z_TRY_ADDREF_P(out);
        return 1;
    } 
    return 0;

}

// #define DEEPCOPY_OLD 1
PHP_FUNCTION(deep_copy)
{
    zval *var;
    if (zend_parse_parameters(ZEND_NUM_ARGS(), "z", &var) == FAILURE) {
        return;
    }
    HashTable ht;
    zend_hash_init(&ht, 16, NULL, ZVAL_PTR_DTOR, 0);
    
    HashTable object_pool;
    zend_hash_init(&object_pool, 16, NULL, ZVAL_PTR_DTOR, 0);

    ///unwrap one level of referencing, because reference is sent to this function
    int res=deep_copy_intern_ex(Z_REFVAL_P(var),return_value,&ht,&object_pool,0);
    #ifdef PHPX_DEBUG
    printf("Deep Copy: A total number of %d elements were copied.\n",res);
    fflush(stdout);
    #endif
    

    zend_hash_destroy(&ht); //most leakage is here (about 30%)
    zend_hash_destroy(&object_pool); //and some here (about 10%)
    //the rest of (60%) leakage is in deep_copy_intern's mallocs
}

PHP_MSHUTDOWN_FUNCTION(phpx)
{
        return SUCCESS;

}
PHP_MINIT_FUNCTION(phpx)
{
        return SUCCESS;

}
PHP_RINIT_FUNCTION(phpx)
{
    return SUCCESS;
}
PHP_RSHUTDOWN_FUNCTION(phpx)
{
    return SUCCESS;
}
static zend_function_entry phpx_functions[] = {
    PHP_FE(is_ref,              phpx_byref_arginfo)
    PHP_FE(zval_id,              phpx_byref_arginfo)
    PHP_FE(deep_copy,              phpx_byref_arginfo)

    { NULL,NULL,NULL}
};
               

zend_module_entry phpx_module_entry = {
     STANDARD_MODULE_HEADER,
    PHPX_EXTNAME, //name
    phpx_functions, // Functions 
    // NULL,NULL,NULL,NULL,
    PHP_MINIT(phpx), // MINIT 
    PHP_MSHUTDOWN(phpx), // MSHUTDOWN 
    PHP_RINIT(phpx), // RINIT 
    PHP_RSHUTDOWN(phpx), // RSHUTDOWN 
    NULL, // MINFO 
    PHPX_EXTVER,
    NO_MODULE_GLOBALS,
    NULL,//ZEND_MODULE_POST_ZEND_DEACTIVATE_N(phpx),

    STANDARD_MODULE_PROPERTIES_EX
};
// #ifdef COMPILE_DL_MYLIB
ZEND_GET_MODULE(phpx)
// #endif

<?php

return [
    'user'          => 'admin',
    'password'      => 'admin',

    'protocol'      => 'http',
    'host'          => 'localhost',
    'port'          => 8080,
    'path'          => 'exist/rest',

    /* alternatively, you can specify the URI as a whole in the form */
    // 'uri'=>'http://localhost:8080/exist/rest/'

    /**
     * Applies an XSL stylesheet to the requested resource.
     * If the _xsl parameter contains an external URI, the corresponding external resource is retrieved.
     * Otherwise, the path is treated as relative to the database root collection and the stylesheet is loaded from the database.
     * This option will override any XSL stylesheet processing instructions found in the source XML file.
     * Setting _xsl to no disables any stylesheet processing.
     * This is useful for retrieving the unprocessed XML from documents that have a stylesheet declaration.
     */
    'xsl'           => 'no',

    /**
     * Returns indented pretty-print XML
     */
    'indent'        => 'yes',

    /**
     * Specifies the number of items to return from the resultant sequence
     */
    'howMany'       => 10,

    /**
     * Specifies the index position of the first item in the result sequence to be returned
     */
    'start'         => 1,

    /**
     * Specifies whether the returned query results are to be wrapped into a surrounding <exist:result> element.
     */
    'wrap'          => 'yes'
];
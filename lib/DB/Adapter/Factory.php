<?php

/**
 * Factory class. Provide universal connection to any supported DB
 *
 * @package DB_Adapter
 *
 * DB_Adapter PHP library provides elegant interface for some SQL databases.
 * It supports several types of handy and secure placeholders
 * and provide comfortable debugging.
 *
 * (c) DB_Adapter community
 * @see http://db-adapter.vbo.name
 * 
 * Original idea by Dmitry Koterov and Konstantin Zhinko
 * @see http://dklab.ru/lib/DbSimple/
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * @see http://www.gnu.org/copyleft/lesser.html
 *
 * @author  Borodin Vadim <vbo@vbo.name>
 * @version 10.10 beta
 */
class DB_Adapter_Factory
{
    /**
     * Universal DB connector (uses DSN)
     * Universal static function to connect ANY database using DSN syntax.
     * Choose database driver according to DSN. Return new instance
     * of this driver.
     *
     * @param   string                 $dsn
     * @return  DB_Adapter_Generic_DB  $object
     */
    public static function connect($dsn)
    {
        $config = self::parseDSN($dsn);
        if (!$config) {
            return;
        }        
        $driver = self::_loadDriver($config);
        $driver->setIdentPrefix(@$config['ident_prefix']);
        return $driver;
    }

    /**
     * Universal DSN parser.
     * Parses a data source name into an array.
     * @see  http://en.wikipedia.org/wiki/Database_Source_Name
     *
     * @param  string/array $dsn
     * @return array        $parsed
     *
     * @todo Throw an Exception if not parsed
     */
    public static function parseDSN($dsn)
    {
        if (is_array($dsn)) {
            return $dsn;
        }
        $parsed = @parse_url($dsn);
        if (!$parsed) {
            return null;
        }
        if (!empty($parsed['query'])) {
            $params = null;
            parse_str($parsed['query'], $params);
            $parsed += $params;
        }

        $parsed['dsn'] = $dsn;
        return $parsed;
    }

    /**
     * @param array $config
     * @return DB_Adapter_Generic_DB
     */
    private static function _loadDriver(array $config)
    {
        $classname = self::_determineDriverClassName($config['scheme']);
        if (!class_exists($classname)) {
            $path = str_replace('_', '/', $classname) . ".php";
            require_once $path;
        }
        return new $classname($config);
    }

    private static function _determineDriverClassName($db_type)
    {
        $db_type = ucfirst($db_type);
        $class = "DB_Adapter_{$db_type}_DB";
        return $class;
    }

    private function __construct() {}
    private function __clone() {}
}

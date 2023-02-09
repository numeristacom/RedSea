<?php
/**
 * This file defines internal debug services to the RedSea class library.
 * @author Daniel Page <daniel@danielpage.com>
 * @copyright Copyright (c) 2021, Daniel Page
 * @license Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace RedSea;
/**
 * Stores the internal status of the template caching state. Default: True. Can be accessed directly from out side
 */
class cache {   
    
    public static $enableContentCaching = true;

    static function makeElementByIdCacheName($PathToFileContainingElementID, $elementID, $onlyInnerHTML) {
        return basename($PathToFileContainingElementID) . "." . $elementID . "." . $onlyInnerHTML;
    }

    static function makeTemplateCacheName($PathToFileContainingElementID) {
        return basename($PathToFileContainingElementID);
    }

    static function isInCache($cacheName) {
        return file_exists(RS_CACHE . $cacheName);
    }

    static function setCachedElement($cacheName, $cacheContent) {
        if(!is_dir(RS_CACHE)) {
            mkdir(RS_CACHE);
        }
        return file_put_contents(RS_CACHE . $cacheName, $cacheContent);
    }

    static function getCachedElement($cacheName) {
        if(self::isInCache($cacheName)) {
            return file_get_contents(RS_CACHE . $cacheName);
        } else {
            return false;
        }
    }
}

?>
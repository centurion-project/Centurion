<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @subpackage Destination
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
//$1 'Zend/Pdf/Element.php';


/** Zend_Pdf_Target */
//$1 'Zend/Pdf/Target.php';


/**
 * Abstract PDF destination representation class
 *
 * @package    Zend_Pdf
 * @subpackage Destination
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Destination extends Zend_Pdf_Target
{
    /**
     * Load Destination object from a specified resource
     *
     * @internal
     * @param $destinationArray
     * @return Zend_Pdf_Destination
     */
    public static function load(Zend_Pdf_Element $resource)
    {
        //$1 'Zend/Pdf/Element.php';
        if ($resource->getType() == Zend_Pdf_Element::TYPE_NAME  ||  $resource->getType() == Zend_Pdf_Element::TYPE_STRING) {
            //$1 'Zend/Pdf/Destination/Named.php';
            return new Zend_Pdf_Destination_Named($resource);
        }

        if ($resource->getType() != Zend_Pdf_Element::TYPE_ARRAY) {
            //$1 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('An explicit destination must be a direct or an indirect array object.');
        }
        if (count($resource->items) < 2) {
            //$1 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('An explicit destination array must contain at least two elements.');
        }

        switch ($resource->items[1]->value) {
            case 'XYZ':
                //$1 'Zend/Pdf/Destination/Zoom.php';
                return new Zend_Pdf_Destination_Zoom($resource);
                break;

            case 'Fit':
                //$1 'Zend/Pdf/Destination/Fit.php';
                return new Zend_Pdf_Destination_Fit($resource);
                break;

            case 'FitH':
                //$1 'Zend/Pdf/Destination/FitHorizontally.php';
                return new Zend_Pdf_Destination_FitHorizontally($resource);
                break;

            case 'FitV':
                //$1 'Zend/Pdf/Destination/FitVertically.php';
                return new Zend_Pdf_Destination_FitVertically($resource);
                break;

            case 'FitR':
                //$1 'Zend/Pdf/Destination/FitRectangle.php';
                return new Zend_Pdf_Destination_FitRectangle($resource);
                break;

            case 'FitB':
                //$1 'Zend/Pdf/Destination/FitBoundingBox.php';
                return new Zend_Pdf_Destination_FitBoundingBox($resource);
                break;

            case 'FitBH':
                //$1 'Zend/Pdf/Destination/FitBoundingBoxHorizontally.php';
                return new Zend_Pdf_Destination_FitBoundingBoxHorizontally($resource);
                break;

            case 'FitBV':
                //$1 'Zend/Pdf/Destination/FitBoundingBoxVertically.php';
                return new Zend_Pdf_Destination_FitBoundingBoxVertically($resource);
                break;

            default:
                //$1 'Zend/Pdf/Destination/Unknown.php';
                return new Zend_Pdf_Destination_Unknown($resource);
                break;
        }
    }
}

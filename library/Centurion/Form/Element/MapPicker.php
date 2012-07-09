<?php
/**
 * Centurion
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@centurion-project.org so we can send you a copy immediately.
 *
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lchenay@gmail.com>
 */
class Centurion_Form_Element_MapPicker extends Zend_Form_Element
{
    protected $_locale = null;

    protected $_longitude = null;
    protected $_latitude = null;

    public function render(Zend_View_Interface $view = null)
    {
        /**
         * The js for Google map must be added only once even they have multiple instance of mapPicker.
         */
        static $_firstTime = true;
        if ($_firstTime) {
            if (null === $view) {
                $view = $this->getView();
            }
            $view->headScript()->appendFile('http://maps.googleapis.com/maps/api/js?v=3.5&sensor=false&lang='.$this->getLocale());
            $_firstTime = false;
        }

        
        $name = $this->getName();
        $this->setIsArray(true);

        $longName = $name . '[coord_long]';
        $latName = $name . '[coord_lat]';
        $longId = $name . '-coord_long';
        $latId = $name . '-coord_lat';
        
        $translatedWording = $view->escape($view->translate('City, address...'));
        $translatedCancel = $view->translate('Cancel');
        $translatedSave = $view->translate('Save');
        
        return <<<EOS
    <div class="form-item form-location">
        <fieldset class="form-input-text">
            <legend>Position</legend>
            <div class="field-wrapper">
                <div class="field-group field-group-editable">
                    <div class="form-item">
                        <label for="coord_long">Longitude</label>
                        <div class="field-wrapper">
                             <input type="text" name="$longName" id="$longId" value="4.0564" class=" field-text" />
                        </div>
                    </div>
                    <div class="form-item">
                        <label for="coord_lat">Latitude</label>
                        <div class="field-wrapper">
                             <input type="text" name="$latName" id="$latId" value="9.70676" class=" field-text" />
                        </div>
                    </div>
                    <div class="actions">
                         <a href="#" data-lat-id="$latId" data-lng-id="$longId" class="settings-map help" title="Edit"><span class="ui-icon ui-icon-pencil">Edit properties</span><span class="ui-icon ui-icon-triangle-1-s queue"></span></a>
                    </div>
                </div>
            </div>
        </fieldset>
        
        <div id="dialog-map" title="Edit settings marker" class="form-dialog">
            <div class="dialog-inner">
                <div id="map"></div>
                <div class="field-wrapper field-search">
                    <input type="text" placeholder="$translatedWording" class="field-text field-text-map-search" />
                    <span class="ui-icon ui-icon-search"></span>
                </div>
            </div>
            <div class="dialog-buttons">
                <button id="ui-button-cancel" class="ui-button ui-button-nicy ui-button-text-icon" role="button" aria-disabled="false">
                    <span class="ui-button-icon-primary ui-icon ui-icon-red ui-icon-nicy-cross"></span>
                    <span class="ui-button-text ui-button-text-red">$translatedCancel</span>
                </button>
                <button id="ui-button-save" class="ui-button ui-button-nicy ui-button-text-icon" role="button" aria-disabled="false">
                    <span class="ui-button-icon-primary ui-icon ui-icon-nicy-arrow-right"></span>
                    <span class="ui-button-text">$translatedSave</span>
                </button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function(){
            $(".form-location").CUI("map", {});
        });
    </script>
EOS;
    }

    /**
     * @return mixed|string
     */
    public function getValue()
    {
        return $this->_longitude . ',' . $this->_longitude;
    }

    /**
     * @param mixed $value
     * @return void|Zend_Form_Element
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->_longitude = $value['coord_long'];
            $this->_latitude = $value['coord_lat'];
        } else if (is_string($value)) {
            $tab = explode(',', $value);
            $this->_longitude = trim($tab[0]);
            $this->_latitude = trim($tab[1]);
        }
    }

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale = null)
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * @return string the current local
     */
    public function getLocale()
    {
        if ($this->_locale == null) {
            $this->_locale = Zend_Registry::get('Zend_Translate')->getLocale();
        }
        return $this->_locale;
    }
}

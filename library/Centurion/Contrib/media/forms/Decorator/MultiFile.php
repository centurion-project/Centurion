<?php

class Media_Form_Decorator_MultiFile extends Zend_Form_Decorator_Abstract
{
    protected static $_firstTime = true;

    public function render($content)
    {
        $view = Zend_Layout::getMvcInstance()->getView();

        $headScript = $view->headScript();

        //TODO: set url on call, it's not a generic decorator if the url is hardcoded
        $url = $view->url(array(), 'media_upload', true);

        $ticket = $this->_element->getAttrib('ticket');

        $name = $this->_element->getFile()->getFilename()->getName();
        $inputName = $this->_element->getName();
        $label = $this->_element->getLabel();

        $fileTypes = array();

        $fileExtensions = explode(',', $this->_element->getFile()->getExtension());

        foreach ($fileExtensions as $extension) {
            $fileTypes[] = '*.' . $extension;
        }

        $fileTypes = implode(';', $fileTypes);

        $fileSizeLimit = '100MB';
        $description = $this->_element->getFile()->getFileDescription();

        $alreadyLoadedImage = array();

        $fileTable = Centurion_Db::getSingleton('media/file');

        if (null !== $this->_element->getValue()) {
            foreach ($this->_element->getValue() as $fileId) {
                $fielRow = $fileTable->findOneById($fileId);
                if (null !== $fielRow) {
                    if ($fielRow->proxy_model == 'Media_Model_DbTable_Image')
                        $alreadyLoadedImage[] = array($fileId, $fielRow->filename, $fielRow->getStaticUrl(array('resize' => array('maxWidth' => 100, 'maxHeight' => 100))));
                    else
                        $alreadyLoadedImage[] = array($fileId, $fielRow->filename, '');
                }
            }
        }

        $alreadyLoadedImage = json_encode($alreadyLoadedImage);
        $basePath = Zend_Controller_Front::getInstance()->getBaseUrl(); 

        $headScript->appendScript(<<<EOS
        $(function() {
            $(".$name").CUI('files', {
                basePath: '$basePath',                 
                flash_url : "/cui/plugins/swfupload/swfupload.swf",
                upload_url: "$url",
                file_post_name : "$name",
                post_params: {"uploadTicket" : "$ticket"},
                file_size_limit : "$fileSizeLimit",
                file_types : "$fileTypes",
                file_types_description : "$description",
                file_upload_limit : 100,
                file_queue_limit : 0,
                custom_settings : {
                    progressTarget : "fsUploadProgress-$name",
                    cancelButtonId : "btnCancel",
                    inputName : "$inputName",
                    alreadyLoadedImage : $alreadyLoadedImage
                },
                debug: false
            });
        });
EOS
                    );


        $content = <<<EOS
        <div class="form-item">
            <label for="$name">$label</label>
            <input type="hidden" name="${inputName}[]" id="${inputName}-empty" value=""/>
            <div class="field-wrapper field-upload-wrapper">
                <div class="float-right">
                    <span id="divStatus">0 files uploaded</span> &nbsp;
                    <span id="btnCancel" onclick="swfu.cancelQueue();">Remove All</span>
                </div>
                <span id="spanButtonPlaceHolder" class="field-files $name"></span>
                <div class="ui-button-tiny-squared">
                    <a href="#" class="ui-button ui-button-text-icon ui-button-bg-white-gradient">
                        <span class="ui-icon ui-icon-arrowthickstop-1-n ui-icon-red"></span>
                        <span class="ui-button-text">Browse</span>
                    </a>
                </div>
            </div>
            <div class="fieldset flash fsUploadProgress" id="fsUploadProgress-$name">

            </div>
            <div class="clear"></div>
        </div>
EOS;

        return $content;
    }
}
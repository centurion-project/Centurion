<?php

error_reporting(E_ALL | E_STRICT);

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__)),
    get_include_path(),
)));
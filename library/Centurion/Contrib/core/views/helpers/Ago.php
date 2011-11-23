<?php

class Centurion_View_Helper_Ago extends Zend_View_Helper_Abstract
{
    public function ago($tm)
    {
        if ($tm instanceof Zend_Date) {
            $tm = $tm->getTimestamp();
        }
        
        $cur_tm = time();
        $dif = $cur_tm-$tm;
        $pds = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
        
        $lngh = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);
        
        for($v = sizeof($lngh)-1; ($v >= 0) && (($no = $dif/$lngh[$v])<=1); $v--);
        
        if($v < 0)
            $v = 0;
            
        $_tm = $cur_tm-($dif % $lngh[$v]);
        
        $no = floor($no);
        if ($no <> 1)
            $pds[$v] .='s';
        $x = $this->view->translate('%d %s ago', $no, $this->view->translate($pds[$v]));
        
        return $x;
    }
}
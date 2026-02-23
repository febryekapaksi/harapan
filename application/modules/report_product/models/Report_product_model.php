<?php

use Mpdf\Tag\P;

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_product_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Report_Product.Add');
        $this->ENABLE_MANAGE  = has_permission('Report_Product.Manage');
        $this->ENABLE_VIEW    = has_permission('Report_Product.View');
        $this->ENABLE_DELETE  = has_permission('Report_Product.Delete');
    }
}

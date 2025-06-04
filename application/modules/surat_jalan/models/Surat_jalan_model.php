<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Surat_jalan_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Surat_Jalan.Add');
        $this->ENABLE_MANAGE  = has_permission('Surat_Jalan.Manage');
        $this->ENABLE_VIEW    = has_permission('Surat_Jalan.View');
        $this->ENABLE_DELETE  = has_permission('Surat_Jalan.Delete');
    }

    public function data_side_surat_jalan() {}

    public function get_query_json_surat_jalan() {}
}

<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master_kendaraan_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Master_Kendaraan.Add');
        $this->ENABLE_MANAGE  = has_permission('Master_Kendaraan.Manage');
        $this->ENABLE_VIEW    = has_permission('Master_Kendaraan.View');
        $this->ENABLE_DELETE  = has_permission('Master_Kendaraan.Delete');
    }
}

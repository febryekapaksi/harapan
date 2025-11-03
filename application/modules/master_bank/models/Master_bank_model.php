<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master_bank_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Master_Bank.Add');
        $this->ENABLE_MANAGE  = has_permission('Master_Bank.Manage');
        $this->ENABLE_VIEW    = has_permission('Master_Bank.View');
        $this->ENABLE_DELETE  = has_permission('Master_Bank.Delete');
    }
}

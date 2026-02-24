<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master_debt_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Master_Debt.Add');
        $this->ENABLE_MANAGE  = has_permission('Master_Debt.Manage');
        $this->ENABLE_VIEW    = has_permission('Master_Debt.View');
        $this->ENABLE_DELETE  = has_permission('Master_Debt.Delete');
    }

    public function get_data($table, $where_field = '', $where_value = '')
    {
        if ($where_field != '' && $where_value != '') {
            $query = $this->db->get_where($table, array($where_field => $where_value));
        } else {
            $query = $this->db->get($table);
        }

        return $query->result();
    }

    public function get_data_where_array($table, $where)
    {
        if (!empty($where)) {
            $query = $this->db->get_where($table, $where);
        } else {
            $query = $this->db->get($table);
        }

        return $query->result();
    }
}

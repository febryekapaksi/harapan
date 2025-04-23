<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Penawaran_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Penawaran.Add');
        $this->ENABLE_MANAGE  = has_permission('Penawaran.Manage');
        $this->ENABLE_VIEW    = has_permission('Penawaran.View');
        $this->ENABLE_DELETE  = has_permission('Penawaran.Delete');
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

    public function get_data_group($table, $where_field = '', $where_value = '', $where_group = '')
    {
        if ($where_field != '' && $where_value != '') {
            $query = $this->db->group_by($where_group)->get_where($table, array($where_field => $where_value));
        } else {
            $query = $this->db->get($table);
        }

        return $query->result();
    }
}

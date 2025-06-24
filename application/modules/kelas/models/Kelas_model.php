<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Kelas_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Master_Kelas.Add');
        $this->ENABLE_MANAGE  = has_permission('Master_Kelas.Manage');
        $this->ENABLE_VIEW    = has_permission('Master_Kelas.View');
        $this->ENABLE_DELETE  = has_permission('Master_Kelas.Delete');
    }

    protected $table_name = 'kelas';
    protected $key        = 'id';

    // list data
    public function GetList()
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->order_by('a.id', 'asc');
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    // get data
    public function GetData($id)
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->where('a.id', $id);
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->row();
        } else {
            return false;
        }
    }
}

<?php defined('BASEPATH') or exit('No direct script access allowed');

class Vouchers_model extends CI_Model
{
    protected $table = 'vouchers';

    public function insert($row)
    {
        $this->db->insert($this->table, $row);
        return $this->db->insert_id();
    }
    public function token_exists($t)
    {
        return $this->db->where('token', $t)->count_all_results($this->table) > 0;
    }
    public function code_exists($c)
    {
        return $this->db->where('code', $c)->count_all_results($this->table) > 0;
    }

    public function next_code($prefix)
    {
        $row = $this->db->select('code')->like('code', $prefix, 'after')->order_by('code', 'DESC')->limit(1)->get($this->table)->row_array();
        $n = 0;
        if ($row && preg_match('/^' . preg_quote($prefix, '/') . '(\d{5,})$/', $row['code'], $m)) $n = (int)$m[1];
        do {
            $n++;
            $code = $prefix . str_pad($n, 5, '0', STR_PAD_LEFT);
        } while ($this->code_exists($code));
        return $code;
    }
}

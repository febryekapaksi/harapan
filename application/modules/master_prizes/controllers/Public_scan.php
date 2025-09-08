<?php defined('BASEPATH') or exit('No direct script access allowed');

class Public_scan extends MX_Controller
{
    public function resolve($token = '')
    {
        $v = $this->db->get_where('vouchers', ['token' => $token])->row_array();
        if (!$v) {
            show_404();
            return;
        }

        // Sudah dipakai / tidak valid
        if ($v['status'] !== 'UNSCANNED') {
            $data = [
                'state'      => 'used',
                'title'      => 'Kode sudah digunakan / tidak valid',
                'subtitle'   => 'Sepertinya kode ini sudah pernah dipakai atau tidak dikenal.',
                'claim_url'  => null,
                'prize_name' => null,
                'token'      => $token
            ];
            $this->load->view('resolve', $data);
            return;
        }

        // Cek zonk vs hadiah
        $prize = null;
        $isZonk = false;
        if (!empty($v['prize_id'])) {
            $prize   = $this->db->get_where('master_prizes', ['id' => $v['prize_id']])->row_array();
            $isZonk  = $prize && !empty($prize['is_zonk']);
        } else {
            $isZonk = true;
        }

        if ($isZonk) {
            // ZONK → kunci sekali lihat
            $this->db->where('id', $v['id'])->update('vouchers', [
                'status'     => 'REVEALED',
                'scanned_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $data = [
                'state'      => 'zonk',
                'title'      => 'Maaf, Anda belum beruntung',
                'subtitle'   => 'Terima kasih sudah berpartisipasi.',
                'claim_url'  => null,
                'prize_name' => null,
                'token'      => $token
            ];
            $this->load->view('resolve', $data);
            return;
        }

        // Hadiah beneran
        $name = $prize ? html_escape($prize['name']) : 'Hadiah';
        $urlClaim = site_url('master_prizes/public_scan/claim/' . $token);

        $data = [
            'state'      => 'win',
            'title'      => 'Selamat! 🎉',
            'subtitle'   => 'Anda mendapatkan:',
            'claim_url'  => $urlClaim,
            'prize_name' => $name,
            'token'      => $token
        ];
        $this->load->view('resolve', $data);
    }

    public function claim($token = '')
    {
        if (!$token) {
            show_404();
            return;
        }

        $v = $this->db->get_where('vouchers', ['token' => $token])->row_array();
        if (!$v) {
            show_404();
            return;
        }

        if (!in_array($v['status'], ['UNSCANNED', 'REVEALED'], true)) {
            // (opsional) samakan mode supaya view gampang
            $this->load->view('claim', [
                'mode'  => 'results', // <-- tambahkan
                'state' => 'error',
                'title' => 'Kode sudah digunakan / tidak valid',
                'msg'   => 'Sepertinya kode ini sudah pernah dipakai atau tidak dikenal.',
                'token' => $token
            ]);
            return;
        }

        $prize = !empty($v['prize_id'])
            ? $this->db->get_where('master_prizes', ['id' => $v['prize_id']])->row_array()
            : null;

        $name = $prize ? html_escape($prize['name']) : 'Hadiah';

        $this->load->view('claim', [
            'mode'       => 'form',
            'token'      => $token,
            'prize_name' => $name,
            'action'     => site_url('master_prizes/public_scan/submit')
        ]);
    }

    public function submit()
    {
        $token = $this->input->post('token', true);
        if (!$token) {
            show_404();
            return;
        }

        $toko  = trim($this->input->post('guest_toko', true));
        $name  = trim($this->input->post('guest_name', true));
        $phone = trim($this->input->post('guest_phone', true));
        $email = trim($this->input->post('guest_email', true));

        if ($name === '' || $phone === '') {
            $this->load->view('claim', [
                'mode'  => 'results', // <-- tambahkan
                'state' => 'error',
                'title' => 'Data belum lengkap',
                'msg'   => 'Nama dan nomor telepon wajib diisi.',
                'token' => $token
            ]);
            return;
        }

        $this->db->trans_begin();

        $v = $this->db->query("SELECT * FROM vouchers WHERE token=? FOR UPDATE", [$token])->row_array();
        if (!$v || !in_array($v['status'], ['UNSCANNED', 'REVEALED'], true)) {
            $this->db->trans_rollback();
            $this->load->view('claim', [
                'mode'  => 'results', // <-- tambahkan
                'state' => 'error',
                'title' => 'Kode sudah digunakan / tidak valid',
                'msg'   => 'Mohon cek kembali atau hubungi CS.',
                'token' => $token
            ]);
            return;
        }

        // >>> ambil nama hadiah untuk ditampilkan di halaman sukses
        $prize_name = 'Hadiah';
        if (!empty($v['prize_id'])) {
            $p = $this->db->select('name')->get_where('master_prizes', ['id' => $v['prize_id']])->row_array();
            if (!empty($p['name'])) $prize_name = $p['name'];
        }

        // simpan klaim
        $this->db->insert('claims', [
            'voucher_id'  => $v['id'],
            'prize_id'    => $v['prize_id'],
            'guest_toko'  => $toko,
            'guest_name'  => $name,
            'guest_phone' => $phone,
            'guest_email' => $email,
            'status'      => 'PENDING',
            'created_at'  => date('Y-m-d H:i:s'),
            'ip'          => $this->input->ip_address(),
            'user_agent'  => $this->input->user_agent()
        ]);

        // tandai claimed
        $this->db->query(
            "UPDATE vouchers SET status='CLAIMED', claimed_at=NOW(), updated_at=NOW()
         WHERE id=? AND status IN ('UNSCANNED','REVEALED')",
            [$v['id']]
        );

        if (!empty($v['prize_id'])) {
            $this->db->query("UPDATE master_prizes SET stock_claimed = stock_claimed + 1 WHERE id=?", [$v['prize_id']]);
        }

        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            $this->load->view('claim', [
                'mode'  => 'results', // <-- tambahkan
                'state' => 'error',
                'title' => 'Gagal menyimpan klaim',
                'msg'   => 'Silakan coba lagi beberapa saat.',
                'token' => $token
            ]);
            return;
        }
        $this->db->trans_commit();

        // >>> kirim prize_name + wording yang kamu mau
        $this->load->view('claim', [
            'mode'       => 'results',
            'state'      => 'success',
            'title'      => 'Klaim diterima! 🎉',
            'msg'        => 'Tim akan segera menghubungi Anda. '
                . 'Hadiah ini tidak dikenakan biaya apapun. Hati-hati penipuan.',
            'prize_name' => $prize_name,     // <-- penting
            'token'      => $token
        ]);
    }
}

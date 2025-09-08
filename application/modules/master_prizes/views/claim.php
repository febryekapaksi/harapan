<?php
$prize_name = isset($prize_name) && $prize_name !== '' ? $prize_name : 'Hadiah';
$action     = isset($action) && $action !== '' ? $action : site_url('master_prizes/public_scan/submit');
$token      = isset($token) ? $token : '';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Klaim Hadiah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ===== Global & background foto ala login ===== */
        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: "Open Sans", Arial, sans-serif;
            background: url('<?= base_url(); ?>assets/img/wallpaper_harapan.jpg') 50%/cover fixed no-repeat;
        }

        /* overlay gelap + blur kaca */
        .wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
            padding: 24px;
            background: rgba(4, 40, 68, .55);
            backdrop-filter: blur(6px) saturate(110%);
            -webkit-backdrop-filter: blur(6px) saturate(110%);
        }

        /* ===== Card (samakan dengan .login) ===== */
        .scan-card,
        .card-claim {
            position: relative;
            z-index: 2;
            width: 90%;
            max-width: 380px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, .06);
            box-shadow: 0 12px 34px rgba(3, 24, 43, .25);
            padding: 22px 22px 24px;
        }

        /* header/body agar rapi (tanpa background tambahan) */
        .scan-header,
        .card-claim .head {
            text-align: center;
            margin-bottom: 12px;
            padding: 0;
            background: none;
        }

        .scan-body,
        .card-claim .body {
            padding: 0;
            background: transparent;
        }

        .emoji {
            font-size: 52px;
            line-height: 1;
        }

        .badge-pill {
            border-radius: 999px;
            font-size: .78rem;
            padding: .35rem .7rem;
            background: #f6f9fc;
            border: 1px solid #e6eef6;
        }

        .prize-name {
            font-weight: 700;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .footer-note {
            font-size: .85rem;
            color: #6b7b8c;
        }

        /* tombol biru gradasi seperti login */
        .btn-primary {
            background: linear-gradient(180deg, #49a9e7, #2e86c1) !important;
            border: 0 !important;
            box-shadow: 0 6px 16px rgba(52, 152, 219, .35);
            transition: transform .08s ease, box-shadow .2s ease;
        }

        .btn-primary:hover {
            box-shadow: 0 8px 20px rgba(52, 152, 219, .45);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, .18);
        }

        /* konfeti tetap (dipakai di resolve) */
        #confetti-canvas {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        /* responsive kecil */
        @media (max-width:420px) {

            .scan-card,
            .card-claim {
                padding: 18px 16px 20px;
                border-radius: 14px;
            }
        }
    </style>

</head>

<body>
    <div class="wrapper">
        <?php if (($mode ?? 'form') === 'results') { ?>
            <div class="card-claim">
                <div class="head">
                    <div class="emoji"><?= ($state === 'success' ? '🎉' : '⚠️') ?></div>
                    <h1 class="h4 mt-2 mb-1"><?= htmlspecialchars($title) ?></h1>
                    <?php if ($state === 'success'): ?>
                        <p class="text-secondary mb-1">
                            Selamat telah mengklaim hadiah <strong><?= htmlspecialchars($prize_name ?? 'Hadiah') ?></strong>.
                        </p>
                    <?php endif; ?>
                    <p class="text-secondary"><?= htmlspecialchars($msg) ?></p>
                </div>
                <!-- <div class="body">
                <a href="<?= site_url() ?>" class="btn btn-primary w-100">Kembali ke Beranda</a>
            </div> -->
            </div>
        <?php } else { ?>
            <div class="card-claim">
                <div class="head">
                    <div class="emoji">📝</div>
                    <h1 class="h4 mt-2 mb-1">Klaim Hadiah</h1>
                    <div class="text-secondary">
                        Anda mendapatkan: <strong><?= htmlspecialchars($prize_name, ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                </div>

                <div class="body">
                    <form method="post" id="claimForm" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="mt-3" novalidate>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                        <!-- penanda konfirmasi swal -->
                        <input type="hidden" name="confirm_ok" id="confirm_ok" value="">

                        <div class="mb-3">
                            <label class="form-label">Nama Toko</label>
                            <input type="text" class="form-control form-control-lg" name="guest_toko" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-lg" name="guest_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No. Telepon / WA</label>
                            <input type="tel" class="form-control form-control-lg" name="guest_phone" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email (opsional)</label>
                            <input type="email" class="form-control form-control-lg" name="guest_email" placeholder="nama@contoh.com">
                        </div>

                        <button class="btn btn-primary btn-lg w-100" type="submit" id="btnSubmit"><i class="fa fa-paper-plane"></i>&emsp;Kirim Klaim</button>

                        <p class="text-muted mt-3 mb-0" style="font-size:.9rem">
                            Dengan mengirim data ini, Anda setuju data digunakan untuk verifikasi hadiah.
                        </p>
                    </form>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between text-muted" style="font-size:.9rem">
                        <span>Scan ID: <strong><?= htmlspecialchars(substr($token, 0, 8), ENT_QUOTES, 'UTF-8') ?>•••</strong></span>
                        <!-- <span>Keamanan: sekali pakai</span> -->
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</body>

<!-- SweetAlert v1 (kalau belum ada global) -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    // Konfirmasi sebelum submit; kalau OK → submit normal (server render halaman sukses)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('claimForm');
        const okField = document.getElementById('confirm_ok');
        const btn = document.getElementById('btnSubmit');

        form.addEventListener('submit', function(e) {
            if (okField.value === '1') { // sudah konfirmasi
                btn.disabled = true;
                btn.textContent = 'Mengirim...';
                return; // biarkan submit jalan
            }
            e.preventDefault();

            // Validasi HTML5 dulu
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const title = 'Kirim klaim sekarang?';
            const text = 'Pastikan data yang Anda isi sudah benar.';
            swal({
                    title: title,
                    text: text,
                    icon: 'warning',
                    buttons: ['Batal', 'Kirim']
                })
                .then(function(ok) {
                    if (ok) {
                        okField.value = '1';
                        form.submit();
                    }
                });
        }, false);
    });
</script>

</html>
<?php
// Variabel dari controller: $state ('win'|'zonk'|'used'), $title, $subtitle, $claim_url, $prize_name, $token
$isWin  = ($state === 'win');
$isZonk = ($state === 'zonk');
$isUsed = ($state === 'used');
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap 5 (CDN) -->
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
        <canvas id="confetti-canvas"></canvas>

        <div class="scan-card">
            <div class="scan-header">
                <?php if ($isWin): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle badge-pill">Kode valid</span>
                    <div class="emoji mt-2">🎉</div>
                <?php elseif ($isZonk): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle badge-pill">Terima kasih</span>
                    <div class="emoji mt-2">🙇</div>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle badge-pill">Tidak valid</span>
                    <div class="emoji mt-2">😕</div>
                <?php endif; ?>
                <h1 class="h3 mt-3 mb-1"><?= htmlspecialchars($title) ?></h1>
                <?php if (!empty($subtitle)): ?>
                    <p class="mb-0 text-secondary"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
            </div>

            <div class="scan-body">
                <?php if ($isWin): ?>
                    <div class="mb-3">
                        <div class="prize-name text-center"><?= htmlspecialchars($prize_name) ?></div>
                    </div>
                    <div class="actions">
                        <a href="<?= htmlspecialchars($claim_url) ?>" class="btn btn-primary btn-lg w-100">
                            <i class="fa fa-paper-plane"></i>&emsp;Klaim Sekarang
                        </a>
                        <!-- <a href="<?= site_url() ?>" class="btn btn-outline-secondary w-100">
                            Beranda
                        </a> -->
                    </div>
                <?php elseif ($isZonk): ?>
                    <div class="mb-3">
                        <p class="mb-2 text-center">Coba lagi di kesempatan berikutnya ya!</p>
                    </div>
                    <!-- <div class="actions">
                    <a href="<?= site_url() ?>" class="btn btn-light w-100">Lihat promo lainnya</a>
                </div> -->
                <?php else: ?>
                    <div class="mb-3 text-center">
                        <p class="mb-2">Jika Anda merasa ini kesalahan, hubungi CS dan sertakan kode di bawah.</p>
                        <code class="d-inline-block px-2 py-1 bg-light rounded border"><?= htmlspecialchars($token) ?></code>
                    </div>
                    <!-- <div class="actions">
                        <a href="<?= site_url() ?>" class="btn btn-outline-light w-100">Kembali</a>
                    </div> -->
                <?php endif; ?>

                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="footer-note">Scan ID: <strong><?= htmlspecialchars(substr($token, 0, 8)) ?>•••</strong></div>
                    <!-- <div class="footer-note">Keamanan: token sekali pakai</div> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional for modal/tooltips etc.) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($isWin): ?>
        <!-- konfeti ringan (tanpa lib besar) -->
        <script>
            (function() {
                const c = document.getElementById('confetti-canvas');
                const ctx = c.getContext('2d');
                let W, H, pieces = [];

                function resize() {
                    W = c.width = window.innerWidth;
                    H = c.height = window.innerHeight;
                }
                window.addEventListener('resize', resize);
                resize();

                const COLORS = ['#16a34a', '#22c55e', '#60a5fa', '#eab308', '#f97316', '#ef4444', '#a855f7'];
                for (let i = 0; i < 220; i++) {
                    pieces.push({
                        x: Math.random() * W,
                        y: Math.random() * -H,
                        r: Math.random() * 6 + 2,
                        c: COLORS[(Math.random() * COLORS.length) | 0],
                        s: Math.random() * 2 + 0.5,
                        a: Math.random() * Math.PI * 2
                    });
                }
                const t0 = Date.now();
                (function draw() {
                    const t = (Date.now() - t0) / 1000;
                    ctx.clearRect(0, 0, W, H);
                    pieces.forEach(p => {
                        p.y += p.s * 3;
                        p.x += Math.sin((p.y + p.r) * 0.02) * 1.2;
                        ctx.save();
                        ctx.translate(p.x, p.y);
                        ctx.rotate(p.a + p.y * 0.01);
                        ctx.fillStyle = p.c;
                        ctx.fillRect(-p.r / 2, -p.r / 2, p.r, p.r);
                        ctx.restore();
                    });
                    pieces = pieces.filter(p => p.y < H + 20);
                    if (t < 5) requestAnimationFrame(draw); // animasi 5 detik
                })();
            })();
        </script>
    <?php endif; ?>
</body>

</html>
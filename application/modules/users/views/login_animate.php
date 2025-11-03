<!--<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>// echo $idt->nm_perusahaan;</title>  -->
<style>
  /* ====== Global & Background ====== */
  html,
  body {
    height: 100%;
  }

  body {
    font-family: "Open Sans", Arial, sans-serif;
    min-height: 100vh;
    margin: 0;
    background: url("<?= base_url(); ?>assets/img/wallpaper_harapan.jpg") 50% / cover fixed no-repeat;
  }

  /* ====== Overlay (gelap + blur kaca) ====== */
  .wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    min-height: 100vh;
    width: 100%;
    padding: 24px;
    background: rgba(4, 40, 68, .55);
    /* gelap lembut */
    backdrop-filter: blur(6px) saturate(110%);
    /* efek kaca */
    -webkit-backdrop-filter: blur(6px) saturate(110%);
  }

  /* ====== Card Login ====== */
  .login {
    position: relative;
    width: 90%;
    max-width: 380px;
    background: #fff;
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, .06);
    box-shadow: 0 12px 34px rgba(3, 24, 43, .25);
    padding: 22px 22px 24px;
  }

  /* Logo atas (opsional) */
  .login>span img {
    display: block;
    margin: 0px auto 30px;
  }

  /* ====== Field (input + icon) ====== */
  .login .field {
    position: relative;
    margin-bottom: 12px;
  }

  .login .field input {
    width: 100%;
    padding: 12px 12px 12px 38px;
    /* ruang ikon kiri */
    border: 1px solid #d7e1ea;
    border-radius: 10px;
    background: #fff;
    color: #1f2a36;
    transition: all .18s ease;
  }

  .login .field input::placeholder {
    color: #9aa8b6;
  }

  .login .field i.fa {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: #3498db;
    font-size: 14px;
    pointer-events: none;
    opacity: .95;
  }

  /* Fokus */
  .login .field input:focus {
    outline: 0;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, .16);
  }

  .login .field input:focus+i.fa {
    color: #2e86c1;
  }

  /* ====== Tombol ====== */
  .login .btn {
    display: block;
    width: 100%;
    margin-top: 30px;
    margin-bottom: 15px;
    padding: 12px 14px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(180deg, #49a9e7, #2e86c1);
    color: #fff;
    font-weight: 600;
    letter-spacing: .2px;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(52, 152, 219, .35);
    transition: transform .08s ease, box-shadow .2s ease, opacity .2s ease;
  }

  .login .btn:hover {
    box-shadow: 0 8px 20px rgba(52, 152, 219, .45);
    transform: translateY(-1px);
  }

  .login .btn:active {
    transform: translateY(1px);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, .18);
  }

  /* ====== Spinner state (opsional, jika kamu toggle .loading / .ok via JS) ====== */
  @keyframes spinner {
    from {
      transform: rotate(0)
    }

    to {
      transform: rotate(360deg)
    }
  }

  .login .btn .spinner {
    display: none;
  }

  .login.loading .btn {
    position: relative;
  }

  .login.loading .btn .spinner {
    display: block;
    position: absolute;
    left: 50%;
    top: 50%;
    width: 32px;
    height: 32px;
    margin: -16px 0 0 -16px;
    border: 3px solid #fff;
    border-top-color: rgba(255, 255, 255, .35);
    border-radius: 50%;
    animation: spinner .6s linear infinite;
  }

  .login.ok .btn {
    background: #8bc34a;
  }

  /* ====== Footer ====== */
  footer {
    margin-top: 18px;
    text-align: center;
    color: #d7dee6;
    font-size: 12px;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, .28);
  }

  /* ====== Responsive kecil ====== */
  @media (max-width: 420px) {
    .login {
      padding: 18px 16px 20px;
      border-radius: 14px;
    }

    .login .btn {
      border-radius: 12px;
    }
  }
</style>

</head>

<body>
  <div class="wrapper">
    <?= form_open($this->uri->uri_string(), ['id' => 'frm_login', 'name' => 'frm_login', 'class' => 'login', 'autocomplete' => 'off']) ?>

    <span>
      <img src="<?= base_url('assets/images/harapan_logo.png'); ?>" width="25%" alt="Logo">
    </span>

    <!-- Username -->
    <div class="field">
      <input type="hidden" name="login" value="1">
      <input type="text" name="username" placeholder="Username" value="<?= set_value('username') ?>" required autofocus>
      <i class="fa fa-user"></i>
    </div>

    <!-- Password -->
    <div class="field">
      <input type="password" name="password" placeholder="Password" required>
      <i class="fa fa-lock"></i>
    </div>

    <!-- reCAPTCHA v3 token -->
    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

    <button type="submit" class="btn" name="login"><i class="fa fa-sign-in"></i>&emsp;Sign In</button>

    <?= form_close() ?>

    <footer>
      <p style="color:#fff">Copyright &copy; <?= $idt->nm_perusahaan; ?> <?= date('Y'); ?></p>
    </footer>
  </div>

  <!-- reCAPTCHA v3 -->
  <script src="https://www.google.com/recaptcha/api.js?render=<?= isset($recaptcha_site_key) ? $recaptcha_site_key : (defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '') ?>"></script>
  <script>
    (() => {
      const form = document.getElementById('frm_login');
      const btn = form.querySelector('button[type="submit"]');
      const siteKey = "<?= $recaptcha_site_key ?? (defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '') ?>";

      if (!siteKey) {
        console.error('SITE KEY kosong');
      }

      form.addEventListener('submit', (e) => {
        e.preventDefault();
        btn.disabled = true;

        grecaptcha.ready(() => {
          grecaptcha.execute(siteKey, {
              action: 'login'
            })
            .then((token) => {
              document.getElementById('g-recaptcha-response').value = token;
              form.submit();
            })
            .catch((err) => {
              btn.disabled = false;
              alert('Gagal memuat reCAPTCHA. Coba lagi.');
              console.error(err);
            });
        });
      });
    })();
  </script>

  <script src="<?= base_url('assets/login/js/index.js'); ?>"></script>
</body>

</html>
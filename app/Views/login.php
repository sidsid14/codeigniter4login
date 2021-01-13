<link rel="stylesheet" href="/assets/css/login-style.css?1.1" />

 <div class="container">
      <div class="forms-container">
        <div class="signin-signup">
          <form action="/" class="sign-in-form" method="post">
            <?php if (isset($validation)): ?>
            <div class="col-12">
              <div class="alert alert-danger" role="alert">
                <?= $validation->listErrors() ?>
              </div>
            </div>
            <?php endif; ?>
            <h2 class="title">Sign in</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" placeholder="Email" class="no-autofill-bkg"  name="email" id="email" value="<?= set_value('email') ?>">
         
              <!-- <input type="text" placeholder="Username" /> -->
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" placeholder="Password" class="no-autofill-bkg"  name="password" id="password" value="" />
            </div>
            <input type="submit" value="Login" class="btn solid" />
          </form>
        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <img style="margin:0 auto" src="http://info.viosrdtest.in/Docsgo-Logo.png" class="image" alt="" />
          <div class="content">
            <h3>Project Data Reporting Tool</h3>
            <p>
           A central place to view, generate all important project reports from the various data sources.</p>
          </div>
        </div>
      </div>
    </div>
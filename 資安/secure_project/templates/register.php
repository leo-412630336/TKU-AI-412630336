<h1>註冊帳號</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="register.php">
    <div class="form-group">
        <label for="username">使用者名稱</label>
        <input type="text" id="username" name="username" required>
    </div>

    <div class="form-group">
        <label for="password">密碼</label>
        <input type="password" id="password" name="password" required>
        <div id="strength-meter" class="strength-meter"></div>
        <small style="color:#666;">請使用8位以上字元，包含數字與符號以增強安全性。</small>
    </div>

    <!-- Mock CAPTCHA -->
    <div class="form-group">
        <label for="captcha">安全驗證：5 + 7 等於多少？</label>
        <input type="text" id="captcha" name="captcha" required placeholder="答案" style="width: 100px;">
    </div>

    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <button type="submit">建立帳號</button>
</form>

<p style="text-align:center; margin-top:1rem;">
    已經有帳號了嗎？ <a href="login.php">點此登入</a>
</p>
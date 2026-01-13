<h1>登入</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<form method="POST" action="login.php">
    <div class="form-group">
        <label for="username">使用者名稱</label>
        <input type="text" id="username" name="username" required>
    </div>

    <div class="form-group">
        <label for="password">密碼</label>
        <input type="password" id="password" name="password" required>
    </div>

    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <button type="submit">登入</button>
</form>

<p style="text-align:center; margin-top:1rem;">
    還沒有帳號嗎？ <a href="register.php">點此註冊</a>
</p>
<h1>Register</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="register.php">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <div id="strength-meter" class="strength-meter"></div>
        <small style="color:#666;">Use 8+ chars, numbers & symbols for strong password.</small>
    </div>

    <!-- Mock CAPTCHA -->
    <div class="form-group">
        <label for="captcha">Security Check: What is 5 + 7?</label>
        <input type="text" id="captcha" name="captcha" required placeholder="Answer" style="width: 100px;">
    </div>

    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <button type="submit">Create Account</button>
</form>

<p style="text-align:center; margin-top:1rem;">
    Already have an account? <a href="login.php">Login here</a>
</p>
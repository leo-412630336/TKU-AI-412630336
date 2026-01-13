<h1>歡迎, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>

<div class="alert alert-success">
    您已透過安全的驗證系統 (MongoDB) 成功登入。
</div>

<p>
    此頁面受保護。系統會驗證您的 Session 並確保您已經通過身份驗證。
</p>

<h3>已啟用的安全功能：</h3>
<ul>
    <li>Session 管理 (HttpOnly Cookies)</li>
    <li>輸出編碼 (XSS 防護)</li>
    <li>透過 MongoDB Driver 進行資料庫查詢 (NoSQL Injection 防護)</li>
</ul>

<hr>

<h3>測試 NoSQL Injection 防護</h3>
<p>試著搜尋使用者。標準的 SQL payload 如 <code>' OR 1=1</code> 對 MongoDB 無效。</p>
<p>您可以嘗試 NoSQL payloads 例如使用工具發送 `?search[$ne]=null`，但我們的後端會透過型別轉換與檢測來防禦。</p>

<form method="GET" action="home.php" style="margin-bottom: 2rem;">
    <div class="form-group">
        <input type="text" name="search" placeholder="搜尋使用者..." value="<?= htmlspecialchars($search ?? '') ?>"
            style="width: 70%; display: inline-block;">
        <button type="submit" style="width: 25%; display: inline-block;">搜尋</button>
    </div>
</form>

<?php if (isset($search) && $search !== ''): ?>
    <h4>"<?= htmlspecialchars($search) ?>" 的搜尋結果</h4>
    <?php if (empty($searchResults)): ?>
        <p>找不到使用者。</p>
    <?php else: ?>
        <ul>
            <?php foreach ($searchResults as $user): ?>
                <li><?= htmlspecialchars($user['username']) ?> (加入時間: <?= htmlspecialchars($user['created_at']) ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
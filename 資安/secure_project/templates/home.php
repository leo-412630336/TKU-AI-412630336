<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>

<div class="alert alert-success">
    You have successfully logged in via a secure authentication system (MongoDB).
</div>

<p>
    This page is protected. It validates your session and ensures you are authenticated.
</p>

<h3>Security Features Active:</h3>
<ul>
    <li>Session Management (HttpOnly Cookies)</li>
    <li>Output Encoding (XSS Protection)</li>
    <li>Database Queries via MongoDB Driver (NoSQL Injection Protection)</li>
</ul>

<hr>

<h3>Test NoSQL Injection Protection</h3>
<p>Try searching for a user. Standard SQL payloads like <code>' OR 1=1</code> won't work on MongoDB.</p>
<p>You can try NoSQL payloads e.g. using a tool to send `?search[$ne]=null`, but our backend prevents this by type
    casting.</p>

<form method="GET" action="home.php" style="margin-bottom: 2rem;">
    <div class="form-group">
        <input type="text" name="search" placeholder="Search username..." value="<?= htmlspecialchars($search ?? '') ?>"
            style="width: 70%; display: inline-block;">
        <button type="submit" style="width: 25%; display: inline-block;">Search</button>
    </div>
</form>

<?php if (isset($search) && $search !== ''): ?>
    <h4>Search Results for "<?= htmlspecialchars($search) ?>"</h4>
    <?php if (empty($searchResults)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($searchResults as $user): ?>
                <li><?= htmlspecialchars($user['username']) ?> (Joined: <?= htmlspecialchars($user['created_at']) ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
<form class="card narrow" method="post" action="/admin/login">
    <h1>Admin sign-in</h1>
    <?php if ($view['error'] !== null): ?>
        <p class="error" role="alert"><?= $e($view['error']) ?></p>
    <?php endif; ?>
    <input type="hidden" name="csrf_token" value="<?= $e($view['csrfToken']) ?>">
    <div class="field">
        <label for="username">Username</label>
        <input id="username" name="username" autocomplete="username" maxlength="64" required>
    </div>
    <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
    </div>
    <button type="submit">Continue</button>
</form>

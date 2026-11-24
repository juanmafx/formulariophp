<form class="card narrow" method="post" action="/admin/verify">
    <h1>Two-factor verification</h1>
    <?php if ($view['error'] !== null): ?>
        <p class="error" role="alert"><?= $e($view['error']) ?></p>
    <?php endif; ?>
    <input type="hidden" name="csrf_token" value="<?= $e($view['csrfToken']) ?>">
    <div class="field">
        <label for="code">Authenticator code</label>
        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
    </div>
    <button type="submit">Verify</button>
</form>

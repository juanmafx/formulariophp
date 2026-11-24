<?php
$fields = [
    ['name', 'Name', 'text', 'name', 100],
    ['email', 'Email', 'email', 'email', 254],
    ['phone', 'Phone', 'tel', 'tel', 33],
    ['subject', 'Subject', 'text', 'off', 150],
];
?>
<h1>Contact form</h1>
<form class="card form-grid" method="post" action="/contact" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $e($view['csrfToken']) ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= $e($view['maxBytes']) ?>">
    <?php foreach ($fields as [$id, $label, $type, $autocomplete, $maxLength]): ?>
        <div class="field">
            <label for="<?= $e($id) ?>"><?= $e($label) ?></label>
            <input id="<?= $e($id) ?>" name="<?= $e($id) ?>" type="<?= $e($type) ?>" autocomplete="<?= $e($autocomplete) ?>" maxlength="<?= $e($maxLength) ?>" value="<?= $e($view['values'][$id] ?? '') ?>" required>
            <?php if (isset($view['errors'][$id])): ?>
                <p class="error" role="alert"><?= $e($view['errors'][$id]) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <div class="field field-wide">
        <label for="attachment">Attachment (optional: PDF, JPEG, PNG, or text, up to 5 MB)</label>
        <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.txt">
        <?php if (isset($view['errors']['attachment'])): ?>
            <p class="error" role="alert"><?= $e($view['errors']['attachment']) ?></p>
        <?php endif; ?>
    </div>
    <div class="field field-wide">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="6" maxlength="5000" required><?= $e($view['values']['message'] ?? '') ?></textarea>
        <?php if (isset($view['errors']['message'])): ?>
            <p class="error" role="alert"><?= $e($view['errors']['message']) ?></p>
        <?php endif; ?>
    </div>
    <div class="field-wide">
        <button type="submit">Send</button>
    </div>
</form>

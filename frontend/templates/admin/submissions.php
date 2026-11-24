<header class="toolbar">
    <h1>Submissions (<?= $e($view['total']) ?>)</h1>
    <form method="post" action="/admin/logout">
        <input type="hidden" name="csrf_token" value="<?= $e($view['csrfToken']) ?>">
        <button type="submit">Sign out</button>
    </form>
</header>
<?php if ($view['submissions'] === []): ?>
    <p class="card">No submissions yet.</p>
<?php endif; ?>
<?php foreach ($view['submissions'] as $submission): ?>
    <article class="card">
        <h2><?= $e($submission->contact->subject) ?></h2>
        <dl>
            <dt>Received</dt><dd><?= $e(gmdate('Y-m-d H:i', $submission->createdAt)) ?> UTC</dd>
            <dt>Name</dt><dd><?= $e($submission->contact->name) ?></dd>
            <dt>Email</dt><dd><?= $e($submission->contact->email) ?></dd>
            <dt>Phone</dt><dd><?= $e($submission->contact->phone) ?></dd>
            <dt>Delivery</dt><dd><?= $e($submission->deliveryStatus) ?></dd>
            <?php if ($submission->attachmentName !== null): ?>
                <dt>Attachment</dt><dd><?= $e($submission->attachmentName) ?> (file not stored; sent by email only)</dd>
            <?php endif; ?>
        </dl>
        <p class="message"><?= $e($submission->contact->message) ?></p>
    </article>
<?php endforeach; ?>
<nav class="toolbar" aria-label="Pagination">
    <?php if ($view['page'] > 1): ?>
        <a href="/admin?page=<?= $e($view['page'] - 1) ?>">Previous</a>
    <?php endif; ?>
    <span>Page <?= $e($view['page']) ?> of <?= $e($view['pageCount']) ?></span>
    <?php if ($view['page'] < $view['pageCount']): ?>
        <a href="/admin?page=<?= $e($view['page'] + 1) ?>">Next</a>
    <?php endif; ?>
</nav>

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE COLLATE NOCASE CHECK (length(username) BETWEEN 3 AND 64),
    password_hash TEXT NOT NULL CHECK (password_hash LIKE '$argon2id$%'),
    totp_secret_encrypted TEXT NOT NULL CHECK (totp_secret_encrypted LIKE 'v1.%'),
    last_totp_step INTEGER CHECK (last_totp_step IS NULL OR last_totp_step >= 0),
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS contact_submissions (
    id TEXT PRIMARY KEY CHECK (length(id) = 32),
    name_encrypted TEXT NOT NULL CHECK (name_encrypted LIKE 'v1.%'),
    email_encrypted TEXT NOT NULL CHECK (email_encrypted LIKE 'v1.%'),
    phone_encrypted TEXT NOT NULL CHECK (phone_encrypted LIKE 'v1.%'),
    subject_encrypted TEXT NOT NULL CHECK (subject_encrypted LIKE 'v1.%'),
    message_encrypted TEXT NOT NULL CHECK (message_encrypted LIKE 'v1.%'),
    attachment_name_encrypted TEXT CHECK (attachment_name_encrypted IS NULL OR attachment_name_encrypted LIKE 'v1.%'),
    delivery_status TEXT NOT NULL CHECK (delivery_status IN ('pending', 'sent', 'failed')),
    created_at INTEGER NOT NULL
) WITHOUT ROWID;

CREATE INDEX IF NOT EXISTS idx_contact_submissions_created_at
    ON contact_submissions (created_at DESC, id DESC);

CREATE TABLE IF NOT EXISTS rate_limits (
    bucket_key TEXT PRIMARY KEY CHECK (length(bucket_key) = 64),
    hits INTEGER NOT NULL CHECK (hits >= 1),
    expires_at INTEGER NOT NULL
) WITHOUT ROWID;

CREATE INDEX IF NOT EXISTS idx_rate_limits_expires_at ON rate_limits (expires_at);

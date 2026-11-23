-- 001: Admin accounts and login throttling.
-- Replaces the single admin login from the config file. Create users with:  php bin/admin-user.php add <username>

CREATE TABLE IF NOT EXISTS admin_users (
	id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
	username            VARCHAR(50)  NOT NULL,
	display_name        VARCHAR(100) NOT NULL DEFAULT '',
	email               VARCHAR(254) NOT NULL DEFAULT '',
	password_hash       VARCHAR(255) NOT NULL,
	is_active           TINYINT(1)   NOT NULL DEFAULT 1,
	created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	password_changed_at DATETIME     NULL,
	last_login_at       DATETIME     NULL,
	PRIMARY KEY (id),
	UNIQUE KEY uq_admin_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
	id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	ip           VARCHAR(45)  NOT NULL,
	username     VARCHAR(50)  NOT NULL DEFAULT '',
	attempted_at DATETIME     NOT NULL,
	succeeded    TINYINT(1)   NOT NULL DEFAULT 0,
	PRIMARY KEY (id),
	KEY ix_login_attempts_user (username, attempted_at),
	KEY ix_login_attempts_ip (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

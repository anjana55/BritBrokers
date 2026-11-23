-- 002: Convert the site's tables from MyISAM/latin1 to InnoDB/utf8mb4.
--
-- InnoDB gives transactions, crash safety and foreign keys (migration 003).
--
-- IMPORTANT, text encoding: the tables are declared latin1 but actually hold UTF-8 text
-- (the PHP 5 code wrote UTF-8 bytes over a latin1 connection). A normal CONVERT TO would treat
-- each byte as a latin1 character and garble it ("’" would become "â€™"). So the columns that
-- contain non-ASCII text are first changed to raw bytes (VARBINARY), then relabelled as utf8mb4,
-- which keeps the bytes exactly as they are. All other text columns are plain ASCII (checked),
-- so CONVERT TO is safe for them.
--
-- After this migration the site must connect with DB_CHARSET="utf8mb4" (the default from this
-- release on).
-- The blog (WordPress, bbb_* tables) is not touched.

-- Record the order the lists are shown in today (MyISAM returns rows in storage order and the
-- code never sorted them). InnoDB returns rows in key order, so without this the search form's
-- dropdowns and checkboxes would change order. The lists are sorted by sort_order from now on,
-- which also makes it possible to reorder them later.
ALTER TABLE property_types ADD COLUMN sort_order INT NOT NULL DEFAULT 0;
SET @n := 0;
UPDATE property_types SET sort_order = (@n := @n + 1);
ALTER TABLE feature_types ADD COLUMN sort_order INT NOT NULL DEFAULT 0;
SET @n := 0;
UPDATE feature_types SET sort_order = (@n := @n + 1);
ALTER TABLE districts ADD COLUMN sort_order INT NOT NULL DEFAULT 0;
SET @n := 0;
UPDATE districts SET sort_order = (@n := @n + 1);
ALTER TABLE cities ADD COLUMN sort_order INT NOT NULL DEFAULT 0;
SET @n := 0;
UPDATE cities SET sort_order = (@n := @n + 1);
ALTER TABLE business_types ADD COLUMN sort_order INT NOT NULL DEFAULT 0;
SET @n := 0;
UPDATE business_types SET sort_order = (@n := @n + 1);
ALTER TABLE price_ranges ADD COLUMN sort_order INT NOT NULL DEFAULT 0;
SET @n := 0;
UPDATE price_ranges SET sort_order = (@n := @n + 1);

-- properties: byte-preserving conversion of the free-text columns
ALTER TABLE properties
	DROP INDEX street,
	MODIFY street      VARBINARY(255)  NOT NULL,
	MODIFY keywords    VARBINARY(255)  NOT NULL,
	MODIFY description VARBINARY(2000) NOT NULL;

ALTER TABLE properties ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE properties
	MODIFY street      VARCHAR(255)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	MODIFY keywords    VARCHAR(255)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	MODIFY description VARCHAR(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	ADD FULLTEXT KEY ft_properties_search (street, keywords, description);

-- The other tables hold ASCII text only
ALTER TABLE images         ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE features       ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE feature_types  ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE cities         ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE districts      ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE property_types ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE business_types ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE price_ranges   ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE imagesv1       ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Exchange rates: DOUBLE instead of single-precision FLOAT, and a primary key.
-- Converted via text so the stored rate stays as shown (181.43, not 181.42999267578125).
ALTER TABLE rates
	MODIFY LKR VARCHAR(32) NOT NULL,
	MODIFY GBP VARCHAR(32) NOT NULL;

ALTER TABLE rates
	ENGINE=InnoDB,
	CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
	MODIFY LKR DOUBLE NOT NULL,
	MODIFY GBP DOUBLE NOT NULL,
	ADD COLUMN id TINYINT UNSIGNED NOT NULL DEFAULT 1 FIRST,
	ADD PRIMARY KEY (id);

-- 003: Clean up, then add primary keys, indexes and foreign keys.
-- Needs 002 (foreign keys require InnoDB).

-- 98 image records belong to properties that no longer exist. Move them aside (not deleted),
-- so the foreign key below can be added. Their files, if any, are left on disk.
CREATE TABLE IF NOT EXISTS images_orphaned LIKE images;
INSERT INTO images_orphaned
	SELECT i.* FROM images i LEFT JOIN properties p ON p.property_id = i.property_id
	WHERE p.property_id IS NULL;
DELETE i FROM images i LEFT JOIN properties p ON p.property_id = i.property_id
	WHERE p.property_id IS NULL;

-- 8 duplicate feature rows (the same feature listed twice for a property): keep one of each
CREATE TABLE features_dedup AS
	SELECT property_id, feature_id, MAX(feature_spec) AS feature_spec
	FROM features GROUP BY property_id, feature_id;
DELETE FROM features;
INSERT INTO features (property_id, feature_id, feature_spec)
	SELECT property_id, feature_id, feature_spec FROM features_dedup;
DROP TABLE features_dedup;

-- District and city are numeric IDs stored as text; store them as numbers so joins can use indexes
ALTER TABLE properties
	MODIFY district INT(11) NOT NULL,
	MODIFY city     INT(11) NOT NULL,
	ADD KEY ix_properties_search (business_type, status),
	ADD KEY ix_properties_district (district),
	ADD KEY ix_properties_city (city),
	ADD KEY ix_properties_type (property_type),
	ADD KEY ix_properties_featured (featured),
	ADD KEY ix_properties_submitted (time_submitted);

ALTER TABLE images
	ADD KEY ix_images_property (property_id, img_type);

ALTER TABLE features
	ADD PRIMARY KEY (property_id, feature_id),
	ADD KEY ix_features_feature (feature_id);

ALTER TABLE cities
	ADD KEY ix_cities_district (district_id);

ALTER TABLE price_ranges
	ADD KEY ix_price_ranges (min_max, type);

-- Foreign keys. Deleting a property removes its image and feature records with it
-- (the site deletes the image files first).
ALTER TABLE images
	ADD CONSTRAINT fk_images_property FOREIGN KEY (property_id) REFERENCES properties (property_id) ON DELETE CASCADE;

ALTER TABLE features
	ADD CONSTRAINT fk_features_property FOREIGN KEY (property_id) REFERENCES properties (property_id) ON DELETE CASCADE,
	ADD CONSTRAINT fk_features_type     FOREIGN KEY (feature_id)  REFERENCES feature_types (feature_id);

ALTER TABLE properties
	ADD CONSTRAINT fk_properties_district FOREIGN KEY (district)      REFERENCES districts (district_id),
	ADD CONSTRAINT fk_properties_city     FOREIGN KEY (city)          REFERENCES cities (city_id),
	ADD CONSTRAINT fk_properties_type     FOREIGN KEY (property_type) REFERENCES property_types (pt_id),
	ADD CONSTRAINT fk_properties_business FOREIGN KEY (business_type) REFERENCES business_types (bt_id);

ALTER TABLE cities
	ADD CONSTRAINT fk_cities_district FOREIGN KEY (district_id) REFERENCES districts (district_id);

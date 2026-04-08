ALTER TABLE `shows`
    ADD COLUMN `pricing_profile_id` BIGINT UNSIGNED NULL AFTER `movie_version_id`;

ALTER TABLE `pricing_rules`
    ADD COLUMN `rule_name` VARCHAR(255) NULL AFTER `pricing_profile_id`,
    ADD COLUMN `rule_type` VARCHAR(16) NOT NULL DEFAULT 'BASE' AFTER `rule_name`,
    ADD COLUMN `valid_from` DATE NULL AFTER `rule_type`,
    ADD COLUMN `valid_to` DATE NULL AFTER `valid_from`,
    ADD COLUMN `price_mode` VARCHAR(20) NOT NULL DEFAULT 'FIXED' AFTER `price_amount`,
    ADD COLUMN `adjustment_value` BIGINT NULL AFTER `price_mode`;

UPDATE `shows`
SET `pricing_profile_id` = (
    SELECT id FROM `pricing_profiles`
    ORDER BY id ASC
    LIMIT 1
)
WHERE `pricing_profile_id` IS NULL;

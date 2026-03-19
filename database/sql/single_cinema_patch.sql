DELETE FROM cinema_chains WHERE id NOT IN (SELECT chain_id FROM cinemas);
UPDATE cinemas SET chain_id = (SELECT id FROM cinema_chains ORDER BY id LIMIT 1);

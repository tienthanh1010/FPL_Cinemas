-- Patch toi gian cho mo hinh 1 rap duy nhat
DELETE FROM cinema_chains WHERE id NOT IN (SELECT chain_id FROM cinemas);
UPDATE cinemas SET chain_id = (SELECT id FROM cinema_chains ORDER BY id LIMIT 1);

-- Khuyen nghi (tu chay neu can):
-- DELETE FROM cinemas WHERE id NOT IN (SELECT MIN(id) FROM cinemas);
-- DELETE FROM auditoriums WHERE cinema_id NOT IN (SELECT id FROM cinemas);

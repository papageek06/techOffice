-- ============================================================================
-- Script SQL pour lier les pièces aux modèles d'imprimantes
-- Table : piece_modele
-- Date de création : 2026-01-27
-- ============================================================================
-- 
-- IMPORTANT : 
-- 1. Ce script utilise des sous-requêtes pour trouver les IDs des pièces et modèles
-- 2. Les pièces sont identifiées par leur référence (colonne 'reference')
-- 3. Les modèles sont identifiés par leur référence (colonne 'reference_modele')
-- 4. Le script utilise ON DUPLICATE KEY UPDATE pour éviter les doublons
-- 5. Ajuster les correspondances selon vos pièces réelles en stock
--
-- ============================================================================

-- ============================================================================
-- CORRESPONDANCES RICOH - MPC Series (Modèles couleur)
-- ============================================================================

-- MP C2003 / MP C2004 / MP C2503 / MP C2504
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841925' LIMIT 1) as piece_id,
    (SELECT id FROM modele WHERE reference_modele = 'MP C2003' LIMIT 1) as modele_id,
    'TONER_K' as role,
    'Toner noir origine Ricoh' as notes
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841925')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C2003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841928' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C2003' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841928')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C2003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841927' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C2003' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841927')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C2003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841926' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C2003' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841926')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C2003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C2004 (même pièces que MP C2003)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C2004' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C2003' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C2004')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C2503 / MP C2504 (même pièces que MP C2003)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C2503' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C2003' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C2503')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C2504' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C2003' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C2504')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C3003 / MP C3004 / MP C3503 / MP C3504
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841817' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C3003' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841817')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C3003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841820' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C3003' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841820')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C3003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841819' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C3003' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841819')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C3003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841818' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C3003' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841818')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C3003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C3004 / MP C3503 / MP C3504 (même pièces que MP C3003)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C3004' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C3003' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C3004')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C3503' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C3003' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C3503')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C3504' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C3003' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C3504')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C4503 / MP C4504 / MP C5503 / MP C5504 / MP C6003 / MP C6004
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841817' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841817')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C4503')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841820' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841820')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C4503')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841819' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841819')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C4503')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841818' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841818')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C4503')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C4504 / MP C5503 / MP C5504 / MP C6003 / MP C6004 (même pièces que MP C4503)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C4504' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C4504')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C5503' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C5503')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C5504' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C5504')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C6003' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C6003')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C6004' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C4503' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C6004')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C305 / MP C306 / MP C307 / MP C405 / MP C406 / MP C407
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842095' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C306' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842095')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C306')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842096' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C306' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842096')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C306')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842097' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C306' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842097')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C306')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842098' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C306' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842098')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C306')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C307 / MP C407 (même pièces que MP C306)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C307' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C306' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C307')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'MP C407' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'MP C306' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C407')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- MP C305
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842079' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C305' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842079')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C305')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842082' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C305' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842082')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C305')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842081' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C305' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842081')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C305')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842080' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'MP C305' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842080')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'MP C305')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- ============================================================================
-- CORRESPONDANCES RICOH - IMC Series (Modèles couleur)
-- ============================================================================

-- IM C2000 / IM C2500
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842311' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C2000' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842311')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C2000')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842314' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C2000' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842314')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C2000')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842313' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C2000' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842313')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C2000')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842312' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C2000' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842312')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C2000')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C2500 (même pièces que IM C2000)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM C2500' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM C2000' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C2500')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C3000 / IM C3500
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842601' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C300' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842601')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C300' LIMIT 1)
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842602' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C300' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842602')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C300' LIMIT 1)
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842603' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C300' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842603')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C300' LIMIT 1)
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842604' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C300' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842604')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C300' LIMIT 1)
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C3000 / IM C3500 (utilisent les mêmes pièces que IM C300)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM C3000' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM C300' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C3000')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM C3500' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM C300' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C3500')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C4500 / IM C5500 / IM C6000
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842284' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C4500' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842284')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C4500')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842286' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C4500' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842286')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C4500')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842285' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C4500' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842285')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C4500')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C5500 / IM C6000 (même pièces que IM C4500)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM C5500' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM C4500' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C5500')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM C6000' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM C4500' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C6000')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C3010 / IM C3510
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842506' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C3010' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842506')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C3010')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842509' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C3010' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842509')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C3010')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842508' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C3010' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842508')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C3010')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842507' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C3010' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842507')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C3010')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C3510 (même pièces que IM C3010)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM C3510' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM C3010' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C3510')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C4510 / IM C5510 / IM C6010
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842530' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C4510' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842530')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C4510')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842533' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C4510' LIMIT 1),
    'TONER_C',
    'Toner cyan origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842533')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C4510')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842532' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C4510' LIMIT 1),
    'TONER_M',
    'Toner magenta origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842532')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C4510')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842531' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM C4510' LIMIT 1),
    'TONER_Y',
    'Toner jaune origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842531')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C4510')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM C5510 / IM C6010 (même pièces que IM C4510)
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM C5510' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM C4510' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C5510')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM C6010' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM C4510' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM C6010')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- ============================================================================
-- CORRESPONDANCES RICOH - IM Series (Modèles noir et blanc)
-- ============================================================================

-- IM 350
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '418133' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM 350' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '418133')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM 350')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM 2702
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '842135' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM 2702' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '842135')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM 2702')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- IM 7000 / IM 9000
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '841992' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'IM 7000' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '841992')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM 7000')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'IM 9000' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'IM 7000' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'IM 9000')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- ============================================================================
-- CORRESPONDANCES RICOH - SP Series
-- ============================================================================

-- SP 4510
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '407340' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'SP 4510' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '407340')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'SP 4510')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- SP 3600DN
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '407340' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'SP 3600DN' LIMIT 1),
    'TONER_K',
    'Toner noir origine Ricoh'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '407340')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'SP 3600DN')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- ============================================================================
-- CORRESPONDANCES LEXMARK
-- ============================================================================

-- C2240
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7181' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'C2240' LIMIT 1),
    'TONER_K',
    'Toner noir Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7181')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'C2240')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7178' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'C2240' LIMIT 1),
    'TONER_C',
    'Toner cyan Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7178')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'C2240')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7179' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'C2240' LIMIT 1),
    'TONER_M',
    'Toner magenta Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7179')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'C2240')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7180' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'C2240' LIMIT 1),
    'TONER_Y',
    'Toner jaune Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7180')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'C2240')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- XC4240
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7185' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'XC4240' LIMIT 1),
    'TONER_K',
    'Toner noir Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7185')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'XC4240')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7182' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'XC4240' LIMIT 1),
    'TONER_C',
    'Toner cyan Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7182')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'XC4240')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7183' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'XC4240' LIMIT 1),
    'TONER_M',
    'Toner magenta Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7183')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'XC4240')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7184' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'XC4240' LIMIT 1),
    'TONER_Y',
    'Toner jaune Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7184')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'XC4240')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- M3250
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B6890' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'M3250' LIMIT 1),
    'TONER_K',
    'Toner noir Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B6890')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'M3250')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- M1246 / XM1246
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B6889' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'M1246' LIMIT 1),
    'TONER_K',
    'Toner noir Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B6889')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'M1246')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT piece_id, (SELECT id FROM modele WHERE reference_modele = 'XM1246' LIMIT 1), role, notes
FROM piece_modele
WHERE modele_id = (SELECT id FROM modele WHERE reference_modele = 'M1246' LIMIT 1)
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'XM1246')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- XM1242
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B6888' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'XM1242' LIMIT 1),
    'TONER_K',
    'Toner noir Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B6888')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'XM1242')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- XM3142
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '24B7535' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'XM3142' LIMIT 1),
    'TONER_K',
    'Toner noir Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '24B7535')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'XM3142')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- M5255
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = '25B3079' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'M5255' LIMIT 1),
    'TONER_K',
    'Toner noir Lexmark'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = '25B3079')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'M5255')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- ============================================================================
-- CORRESPONDANCES HP
-- ============================================================================

-- LaserJet M506
INSERT INTO piece_modele (piece_id, modele_id, role, notes)
SELECT 
    (SELECT id FROM piece WHERE reference = 'CF287JC' LIMIT 1),
    (SELECT id FROM modele WHERE reference_modele = 'LaserJet M506' LIMIT 1),
    'TONER_K',
    'Toner noir HP'
WHERE EXISTS (SELECT 1 FROM piece WHERE reference = 'CF287JC')
  AND EXISTS (SELECT 1 FROM modele WHERE reference_modele = 'LaserJet M506')
ON DUPLICATE KEY UPDATE notes = VALUES(notes);

-- ============================================================================
-- NOTES IMPORTANTES
-- ============================================================================
-- 
-- 1. Ce script utilise ON DUPLICATE KEY UPDATE pour éviter les doublons
--    basés sur la contrainte unique (piece_id, modele_id, role)
--
-- 2. Les correspondances sont basées sur les références de pièces courantes
--    Vous devez ajuster les références selon vos pièces réelles en stock
--
-- 3. Pour ajouter de nouvelles correspondances, copiez un bloc INSERT
--    et modifiez les références de pièce et le modèle
--
-- 4. Les bacs de récupération, tambours et autres pièces peuvent être
--    ajoutés de la même manière avec les rôles appropriés :
--    - BAC_RECUP pour les bacs de récupération
--    - DRUM pour les tambours
--    - FUSER pour les unités de fusion
--    - AUTRE pour les autres pièces
--
-- 5. Vérifiez que toutes les pièces référencées existent dans la table 'piece'
--    avant d'exécuter ce script
--
-- ============================================================================

-- Grade 1-12 curriculum mapping for development/demo data.
-- This is a practical configurable mapping for the School Management System,
-- not a claim that these hours are fixed official national curriculum hours.
-- Safe to run more than once because grade_id + subject_id is unique.

BEGIN;

WITH curriculum(grade_code, subject_code, credit_hours, is_required) AS (
    VALUES
        -- Grade 1
        ('GRADE_1', 'KHMER',          7, TRUE),
        ('GRADE_1', 'MATH',           5, TRUE),
        ('GRADE_1', 'SCIENCE',        3, TRUE),
        ('GRADE_1', 'SOCIAL_STUDIES', 3, TRUE),
        ('GRADE_1', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_1', 'PE',             2, TRUE),
        ('GRADE_1', 'ART',            1, TRUE),
        ('GRADE_1', 'MUSIC',          1, TRUE),
        ('GRADE_1', 'HEALTH',         1, TRUE),
        ('GRADE_1', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 2
        ('GRADE_2', 'KHMER',          7, TRUE),
        ('GRADE_2', 'MATH',           5, TRUE),
        ('GRADE_2', 'SCIENCE',        3, TRUE),
        ('GRADE_2', 'SOCIAL_STUDIES', 3, TRUE),
        ('GRADE_2', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_2', 'PE',             2, TRUE),
        ('GRADE_2', 'ART',            1, TRUE),
        ('GRADE_2', 'MUSIC',          1, TRUE),
        ('GRADE_2', 'HEALTH',         1, TRUE),
        ('GRADE_2', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 3
        ('GRADE_3', 'KHMER',          7, TRUE),
        ('GRADE_3', 'MATH',           5, TRUE),
        ('GRADE_3', 'SCIENCE',        3, TRUE),
        ('GRADE_3', 'SOCIAL_STUDIES', 3, TRUE),
        ('GRADE_3', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_3', 'PE',             2, TRUE),
        ('GRADE_3', 'ART',            1, TRUE),
        ('GRADE_3', 'MUSIC',          1, TRUE),
        ('GRADE_3', 'HEALTH',         1, TRUE),
        ('GRADE_3', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 4
        ('GRADE_4', 'KHMER',          6, TRUE),
        ('GRADE_4', 'MATH',           5, TRUE),
        ('GRADE_4', 'ENGLISH',        4, TRUE),
        ('GRADE_4', 'SCIENCE',        3, TRUE),
        ('GRADE_4', 'SOCIAL_STUDIES', 3, TRUE),
        ('GRADE_4', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_4', 'ICT',            2, TRUE),
        ('GRADE_4', 'PE',             2, TRUE),
        ('GRADE_4', 'ART',            1, TRUE),
        ('GRADE_4', 'HEALTH',         1, TRUE),
        ('GRADE_4', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 5
        ('GRADE_5', 'KHMER',          6, TRUE),
        ('GRADE_5', 'MATH',           5, TRUE),
        ('GRADE_5', 'ENGLISH',        4, TRUE),
        ('GRADE_5', 'SCIENCE',        3, TRUE),
        ('GRADE_5', 'SOCIAL_STUDIES', 3, TRUE),
        ('GRADE_5', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_5', 'ICT',            2, TRUE),
        ('GRADE_5', 'PE',             2, TRUE),
        ('GRADE_5', 'ART',            1, TRUE),
        ('GRADE_5', 'HEALTH',         1, TRUE),
        ('GRADE_5', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 6
        ('GRADE_6', 'KHMER',          6, TRUE),
        ('GRADE_6', 'MATH',           5, TRUE),
        ('GRADE_6', 'ENGLISH',        4, TRUE),
        ('GRADE_6', 'SCIENCE',        3, TRUE),
        ('GRADE_6', 'SOCIAL_STUDIES', 3, TRUE),
        ('GRADE_6', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_6', 'ICT',            2, TRUE),
        ('GRADE_6', 'PE',             2, TRUE),
        ('GRADE_6', 'ART',            1, TRUE),
        ('GRADE_6', 'HEALTH',         1, TRUE),
        ('GRADE_6', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 7
        ('GRADE_7', 'KHMER',          5, TRUE),
        ('GRADE_7', 'MATH',           5, TRUE),
        ('GRADE_7', 'ENGLISH',        4, TRUE),
        ('GRADE_7', 'PHYSICS',        3, TRUE),
        ('GRADE_7', 'CHEMISTRY',      3, TRUE),
        ('GRADE_7', 'BIOLOGY',        3, TRUE),
        ('GRADE_7', 'HISTORY',        3, TRUE),
        ('GRADE_7', 'GEOGRAPHY',      3, TRUE),
        ('GRADE_7', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_7', 'ICT',            2, TRUE),
        ('GRADE_7', 'PE',             2, TRUE),
        ('GRADE_7', 'HEALTH',         1, TRUE),
        ('GRADE_7', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 8
        ('GRADE_8', 'KHMER',          5, TRUE),
        ('GRADE_8', 'MATH',           5, TRUE),
        ('GRADE_8', 'ENGLISH',        4, TRUE),
        ('GRADE_8', 'PHYSICS',        3, TRUE),
        ('GRADE_8', 'CHEMISTRY',      3, TRUE),
        ('GRADE_8', 'BIOLOGY',        3, TRUE),
        ('GRADE_8', 'HISTORY',        3, TRUE),
        ('GRADE_8', 'GEOGRAPHY',      3, TRUE),
        ('GRADE_8', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_8', 'ICT',            2, TRUE),
        ('GRADE_8', 'PE',             2, TRUE),
        ('GRADE_8', 'HEALTH',         1, TRUE),
        ('GRADE_8', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 9
        ('GRADE_9', 'KHMER',          5, TRUE),
        ('GRADE_9', 'MATH',           5, TRUE),
        ('GRADE_9', 'ENGLISH',        4, TRUE),
        ('GRADE_9', 'PHYSICS',        3, TRUE),
        ('GRADE_9', 'CHEMISTRY',      3, TRUE),
        ('GRADE_9', 'BIOLOGY',        3, TRUE),
        ('GRADE_9', 'HISTORY',        3, TRUE),
        ('GRADE_9', 'GEOGRAPHY',      3, TRUE),
        ('GRADE_9', 'MORAL_CIVICS',   2, TRUE),
        ('GRADE_9', 'ICT',            2, TRUE),
        ('GRADE_9', 'PE',             2, TRUE),
        ('GRADE_9', 'HEALTH',         1, TRUE),
        ('GRADE_9', 'LIFE_SKILLS',    1, TRUE),

        -- Grade 10
        ('GRADE_10', 'KHMER',         5, TRUE),
        ('GRADE_10', 'MATH',          6, TRUE),
        ('GRADE_10', 'ENGLISH',       4, TRUE),
        ('GRADE_10', 'PHYSICS',       4, TRUE),
        ('GRADE_10', 'CHEMISTRY',     4, TRUE),
        ('GRADE_10', 'BIOLOGY',       4, TRUE),
        ('GRADE_10', 'HISTORY',       3, TRUE),
        ('GRADE_10', 'GEOGRAPHY',     3, TRUE),
        ('GRADE_10', 'MORAL_CIVICS',  2, TRUE),
        ('GRADE_10', 'ICT',           2, TRUE),
        ('GRADE_10', 'PE',            2, TRUE),
        ('GRADE_10', 'ECONOMICS',     3, TRUE),

        -- Grade 11
        ('GRADE_11', 'KHMER',         5, TRUE),
        ('GRADE_11', 'MATH',          6, TRUE),
        ('GRADE_11', 'ENGLISH',       4, TRUE),
        ('GRADE_11', 'PHYSICS',       4, TRUE),
        ('GRADE_11', 'CHEMISTRY',     4, TRUE),
        ('GRADE_11', 'BIOLOGY',       4, TRUE),
        ('GRADE_11', 'HISTORY',       3, TRUE),
        ('GRADE_11', 'GEOGRAPHY',     3, TRUE),
        ('GRADE_11', 'MORAL_CIVICS',  2, TRUE),
        ('GRADE_11', 'ICT',           2, TRUE),
        ('GRADE_11', 'PE',            2, TRUE),
        ('GRADE_11', 'ECONOMICS',     3, TRUE),

        -- Grade 12
        ('GRADE_12', 'KHMER',         5, TRUE),
        ('GRADE_12', 'MATH',          6, TRUE),
        ('GRADE_12', 'ENGLISH',       4, TRUE),
        ('GRADE_12', 'PHYSICS',       4, TRUE),
        ('GRADE_12', 'CHEMISTRY',     4, TRUE),
        ('GRADE_12', 'BIOLOGY',       4, TRUE),
        ('GRADE_12', 'HISTORY',       3, TRUE),
        ('GRADE_12', 'GEOGRAPHY',     3, TRUE),
        ('GRADE_12', 'MORAL_CIVICS',  2, TRUE),
        ('GRADE_12', 'ICT',           2, TRUE),
        ('GRADE_12', 'PE',            2, TRUE),
        ('GRADE_12', 'ECONOMICS',     3, TRUE)
)
INSERT INTO grade_subjects (
    grade_id,
    subject_id,
    credit_hours,
    is_required,
    created_at,
    updated_at
)
SELECT
    g.id,
    s.id,
    c.credit_hours,
    c.is_required,
    NOW(),
    NOW()
FROM curriculum c
JOIN grades g
    ON g.code = c.grade_code
JOIN subjects s
    ON s.code = c.subject_code
ON CONFLICT (grade_id, subject_id)
DO UPDATE SET
    credit_hours = EXCLUDED.credit_hours,
    is_required = EXCLUDED.is_required,
    updated_at = NOW();

COMMIT;

-- Verification:
-- SELECT
--     g.code AS grade_code,
--     g.name_en AS grade_name,
--     s.code AS subject_code,
--     s.name_en AS subject_name,
--     gs.credit_hours,
--     gs.is_required
-- FROM grade_subjects gs
-- JOIN grades g ON g.id = gs.grade_id
-- JOIN subjects s ON s.id = gs.subject_id
-- ORDER BY g.order_no, s.name_en;

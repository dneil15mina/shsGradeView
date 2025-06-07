-- SQL Commands to update grade_levels table
-- Run these commands in your MySQL client:

-- 1. Add the level_order column
ALTER TABLE grade_levels ADD COLUMN level_order INT NOT NULL DEFAULT 0 AFTER level_name;

-- 2. Update existing grade levels with proper order
UPDATE grade_levels SET level_order = 1 WHERE level_name = 'Grade 11';
UPDATE grade_levels SET level_order = 2 WHERE level_name = 'Grade 12';

-- 3. Verify the changes
SELECT * FROM grade_levels;

-- 4. Additional recommended indexes for performance
CREATE INDEX idx_grade_level_order ON grade_levels(level_order);
CREATE INDEX idx_sections_level ON sections(level_id);

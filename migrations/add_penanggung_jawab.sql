-- Add penanggung_jawab column to tambah_notulen table
-- This column stores the user ID of the person in charge of the meeting

ALTER TABLE `tambah_notulen` 
ADD COLUMN `penanggung_jawab` INT(11) NULL DEFAULT NULL 
AFTER `status`;

-- Add foreign key constraint (optional but recommended)
ALTER TABLE `tambah_notulen`
ADD CONSTRAINT `fk_notulen_penanggung_jawab` 
FOREIGN KEY (`penanggung_jawab`) REFERENCES `users` (`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

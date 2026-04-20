SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `screening_results` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,

  `full_name` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `skin_type` varchar(100) NOT NULL,
  `main_concern` text NOT NULL,
  
  `image_path` varchar(255) NOT NULL,
  
  -- Update Enum agar sinkron dengan hasil deteksi Teachable Machine
  `ml_severity_level` enum('Mild','Moderate','Severe','PAPULE','PUSTULE','BLACKHEAD') NOT NULL,
  
  `ml_papule_count` int(11) DEFAULT 0,
  `ml_pustule_count` int(11) DEFAULT 0,
  `ml_blackhead_count` int(11) DEFAULT 0,
  
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `screening_results`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `screening_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `screening_results`
  ADD CONSTRAINT `fk_screening_patient` FOREIGN KEY (`patient_id`) REFERENCES `backbone_medweb`.`users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
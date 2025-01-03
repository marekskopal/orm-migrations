CREATE TABLE `table_a` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `created_at` datetime NOT NULL,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NULL,
    `type` enum('a','b','c') NOT NULL DEFAULT 'a',
    `price` decimal(11,2) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `table_b` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `table_a_id` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `table_a_id` (`table_a_id`),
    CONSTRAINT `table_b_table_a_id_table_a_id_fk` FOREIGN KEY (`table_a_id`) REFERENCES `table_a` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


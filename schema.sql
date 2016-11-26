CREATE TABLE events (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, file_id INT UNSIGNED DEFAULT NULL, type SMALLINT NOT NULL, event_time DATETIME NOT NULL, INDEX IDX_5387574AA76ED395 (user_id), INDEX IDX_5387574A93CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE files (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, category_id INT UNSIGNED DEFAULT NULL, bvh_id INT UNSIGNED DEFAULT NULL, title VARCHAR(64) DEFAULT NULL, filename VARCHAR(64) NOT NULL, original_filename VARCHAR(64) NOT NULL, realpath VARCHAR(255) NOT NULL, size INT NOT NULL, mime VARCHAR(64) NOT NULL, hash VARCHAR(35) NOT NULL, created_time DATETIME NOT NULL, modified_time DATETIME NOT NULL, roles LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', UNIQUE INDEX UNIQ_63540593C0BE965 (filename), INDEX IDX_6354059A76ED395 (user_id), INDEX IDX_635405912469DE2 (category_id), INDEX IDX_6354059E5FED953 (bvh_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE provinsi (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_9AE8A715E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE users (id INT UNSIGNED AUTO_INCREMENT NOT NULL, email VARCHAR(64) NOT NULL, password VARCHAR(64) NOT NULL, name VARCHAR(32) NOT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)', created_time DATETIME NOT NULL, modified_time DATETIME NOT NULL, discriminator SMALLINT NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE events ADD CONSTRAINT FK_5387574AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id);
ALTER TABLE events ADD CONSTRAINT FK_5387574A93CB796C FOREIGN KEY (file_id) REFERENCES files (id) ON DELETE SET NULL;
ALTER TABLE files ADD CONSTRAINT FK_6354059A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);
ALTER TABLE files ADD CONSTRAINT FK_635405912469DE2 FOREIGN KEY (category_id) REFERENCES provinsi (id);
ALTER TABLE files ADD CONSTRAINT FK_6354059E5FED953 FOREIGN KEY (bvh_id) REFERENCES files (id);

INSERT INTO `provinsi` (`id`, `name`) VALUES
	(1, 'Aceh'),
	(17, 'Bali'),
	(14, 'Banten'),
	(7, 'Bengkulu'),
	(11, 'DKI Jakarta'),
	(29, 'Gorontalo'),
	(5, 'Jambi'),
	(12, 'Jawa Barat'),
	(13, 'Jawa Tengah'),
	(15, 'Jawa Timur'),
	(20, 'Kalimantan Barat'),
	(22, 'Kalimantan Selatan'),
	(21, 'Kalimantan Tengah'),
	(23, 'Kalimantan Timur'),
	(24, 'Kalimantan Utara'),
	(9, 'Kep. Bangka Belitung'),
	(10, 'Kep. Riau'),
	(8, 'Lampung'),
	(31, 'Maluku'),
	(32, 'Maluku Utara'),
	(18, 'Nusa Tenggara Barat'),
	(19, 'Nusa Tenggara Timur'),
	(33, 'Papua'),
	(34, 'Papua barat'),
	(4, 'Riau'),
	(30, 'Sulawesi Barat'),
	(27, 'Sulawesi Selatan'),
	(26, 'Sulawesi Tengah'),
	(28, 'Sulawesi Tenggara'),
	(25, 'Sulawesi Utara'),
	(3, 'Sumatera Barat'),
	(6, 'Sumatera Selatan'),
	(2, 'Sumatera Utara'),
	(16, 'Yogyakarta');
	
INSERT INTO `users` (`id`, `email`, `password`, `name`, `roles`, `created_time`, `modified_time`, `discriminator`) VALUES
	(1, 'admin@localhost', '$2y$13$pBwws92Hy9j83QHmCtIgvuIkVZEwN5EBijf/ZzYWBXkv/2VjHwP7C', 'Administrator', 'ROLE_ADMIN', '2016-11-26 13:53:04', '2016-11-26 13:53:04', 2);
CREATE TABLE `articles` (
    `id` INTEGER AUTO_INCREMENT,
    `author_id` INTEGER,
    `title` VARCHAR(255),
    `body` TEXT,
    `published` VARCHAR(1) DEFAULT 'N',
    PRIMARY KEY (`id`)
)

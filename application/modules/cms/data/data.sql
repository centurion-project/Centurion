SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `cms_flatpage_template` (`id`, `name`, `view_script`, `class`)
VALUES
    (1, 'Basic', '_generic/basic.phtml', NULL),
    (2, 'Basic no title', '_generic/basic-notitle.phtml', NULL);


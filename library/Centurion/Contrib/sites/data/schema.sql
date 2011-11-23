SET foreign_key_checks = 0;

DROP TABLE IF EXISTS `centurion_site`;
create table centurion_site
(
   id           int(11) unsigned not null auto_increment,
   domain       varchar(100) not null,
   name         varchar(50) not null,
   primary key (id)
)
ENGINE=InnoDB;